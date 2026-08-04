<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\WithdrawalSetting;
use App\Notifications\WithdrawalPinResetNotification;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CustomerWithdrawalController extends Controller
{
    private const PIN_MAX_ATTEMPTS = 4;

    private const PIN_LOCK_HOURS = 24;

    public function managementIndex(Request $request): View
    {
        $status = in_array($request->string('status')->toString(), ['all', 'pending', 'approved', 'rejected'], true)
            ? $request->string('status')->toString()
            : 'pending';
        $search = $request->string('search')->trim()->toString();
        $query = WithdrawalRequest::with('customer');
        $totalCount = (clone $query)->count();
        $pendingCount = (clone $query)->where('status', 'pending')->count();
        $pendingAmount = (float) (clone $query)->where('status', 'pending')
            ->selectRaw('COALESCE(SUM(COALESCE(net_amount, amount)), 0) as total')->value('total');
        $approvedAmount = (float) (clone $query)->where('status', 'approved')
            ->selectRaw('COALESCE(SUM(COALESCE(net_amount, amount)), 0) as total')->value('total');
        $withdrawals = $query
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($search, function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(function ($query) use ($term) {
                    $query->where('request_number', 'like', $term)
                        ->orWhere('account_title', 'like', $term)
                        ->orWhere('account_number', 'like', $term)
                        ->orWhereHas('customer', fn ($customer) => $customer
                            ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term)
                            ->orWhere('phone', 'like', $term)
                            ->orWhere('file_no', 'like', $term));
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();
        $lockedCustomers = User::query()
            ->where('role', 'customer')
            ->whereNotNull('withdrawal_pin_locked_until')
            ->where('withdrawal_pin_locked_until', '>', now())
            ->orderBy('withdrawal_pin_locked_until')
            ->get(['id', 'name', 'email', 'phone', 'file_no', 'referral_code', 'withdrawal_pin_failed_attempts', 'withdrawal_pin_locked_until']);

        return view('withdrawal-requests.index', compact(
            'withdrawals', 'status', 'search', 'totalCount', 'pendingCount', 'pendingAmount', 'approvedAmount',
            'lockedCustomers'
        ));
    }

    public function unlockPin(User $customer): RedirectResponse
    {
        abort_unless($customer->role === 'customer', 404);

        $customer->forceFill([
            'withdrawal_pin_failed_attempts' => 0,
            'withdrawal_pin_locked_until' => null,
        ])->save();

        return back()->with('success', $customer->name.' can submit withdrawal requests again.');
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'customer', 403);

        $payable = (float) Commission::where('beneficiary_id', $request->user()->id)
            ->where('status', 'earned')
            ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as payable')
            ->value('payable');
        $lifetime = (float) Commission::where('beneficiary_id', $request->user()->id)->sum('amount');
        $pending = (float) WithdrawalRequest::where('customer_id', $request->user()->id)->where('status', 'pending')->sum('amount');
        $withdrawn = (float) WithdrawalRequest::where('customer_id', $request->user()->id)
            ->where('status', 'approved')
            ->selectRaw('COALESCE(SUM(COALESCE(net_amount, amount)), 0) as total')
            ->value('total');
        $available = max(0, $payable - $pending);
        $withdrawals = WithdrawalRequest::where('customer_id', $request->user()->id)->latest()->paginate(15);
        $payoutMethods = $request->user()->payoutMethods()->orderByDesc('is_default')->latest()->get();
        $settings = WithdrawalSetting::settings();
        $frequency = $this->customerFrequency($request, $settings);
        $policy = WithdrawalSetting::policy($frequency);
        $withdrawalDayAllowed = $this->withdrawalDayAllowed($frequency, $policy);
        $withdrawalDayLabel = $this->withdrawalDayLabel($frequency, $policy);
        $periodStart = $this->periodStart($frequency);
        $requestsThisPeriod = WithdrawalRequest::where('customer_id', $request->user()->id)
            ->where('status', '!=', 'rejected')
            ->where('created_at', '>=', $periodStart)
            ->count();
        $remainingRequests = max(0, $policy['request_limit'] - $requestsThisPeriod);
        $maximumRequestAmount = $policy['maximum_amount'] > 0
            ? min($available, $policy['maximum_amount'])
            : $available;
        $fee = $settings['fee'];
        $pinLockedUntil = $request->user()->withdrawal_pin_locked_until?->isFuture()
            ? $request->user()->withdrawal_pin_locked_until
            : null;

        return view('customer-withdrawals.index', compact(
            'payable', 'lifetime', 'pending', 'withdrawn', 'available', 'withdrawals',
            'settings', 'frequency', 'policy', 'remainingRequests', 'maximumRequestAmount', 'payoutMethods', 'fee',
            'pinLockedUntil', 'withdrawalDayAllowed', 'withdrawalDayLabel'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'withdrawal_pin' => ['required', 'string', 'regex:/^\d{4,6}$/'],
            'payout_method_id' => ['nullable', 'integer'],
            'payment_method' => ['required_without:payout_method_id', Rule::in(['bank_transfer', 'raast', 'easypaisa', 'jazzcash', 'crypto'])],
            'account_title' => ['required_without:payout_method_id', 'string', 'max:100'],
            'account_number' => ['required_without:payout_method_id', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'withdrawal_pin.regex' => 'Enter your 4 to 6 digit withdrawal PIN.',
        ]);
        if (! filled($request->user()->withdrawal_pin)) {
            throw ValidationException::withMessages([
                'withdrawal_pin' => 'Set your withdrawal PIN in Profile & Security before requesting a withdrawal.',
            ]);
        }

        $customer = $request->user();
        if ($customer->withdrawal_pin_locked_until?->isFuture()) {
            throw ValidationException::withMessages([
                'withdrawal_pin' => 'Withdrawals are locked after four incorrect PIN attempts. Try again after '.$customer->withdrawal_pin_locked_until->format('d M Y, h:i A').'.',
            ]);
        }
        if ($customer->withdrawal_pin_locked_until || $customer->withdrawal_pin_failed_attempts >= self::PIN_MAX_ATTEMPTS) {
            $customer->forceFill([
                'withdrawal_pin_failed_attempts' => 0,
                'withdrawal_pin_locked_until' => null,
            ])->save();
        }
        if (! Hash::check($data['withdrawal_pin'], $customer->withdrawal_pin)) {
            $attempts = (int) $customer->withdrawal_pin_failed_attempts + 1;
            $lockedUntil = $attempts >= self::PIN_MAX_ATTEMPTS ? now()->addHours(self::PIN_LOCK_HOURS) : null;
            $customer->forceFill([
                'withdrawal_pin_failed_attempts' => $attempts,
                'withdrawal_pin_locked_until' => $lockedUntil,
            ])->save();

            if ($lockedUntil) {
                throw ValidationException::withMessages([
                    'withdrawal_pin' => 'Too many incorrect PIN attempts. Withdrawals are locked for 24 hours until '.$lockedUntil->format('d M Y, h:i A').'.',
                ]);
            }

            $remainingAttempts = self::PIN_MAX_ATTEMPTS - $attempts;
            throw ValidationException::withMessages([
                'withdrawal_pin' => 'The withdrawal PIN is incorrect. '.$remainingAttempts.' attempt'.($remainingAttempts === 1 ? '' : 's').' remaining before a 24-hour lock.',
            ]);
        }
        if ($customer->withdrawal_pin_failed_attempts > 0) {
            $customer->forceFill([
                'withdrawal_pin_failed_attempts' => 0,
                'withdrawal_pin_locked_until' => null,
            ])->save();
        }
        unset($data['withdrawal_pin']);
        $fee = WithdrawalSetting::fee();
        $feeAmount = WithdrawalSetting::calculateFee((float) $data['amount'], $fee);
        if ($feeAmount + 0.009 >= (float) $data['amount']) {
            throw ValidationException::withMessages([
                'amount' => 'The withdrawal amount must be greater than the applicable fee of Rs '.number_format($feeAmount, 2).'.',
            ]);
        }
        $data['fee_amount'] = $feeAmount;
        $data['net_amount'] = round((float) $data['amount'] - $feeAmount, 2);
        if (! empty($data['payout_method_id'])) {
            $method = $request->user()->payoutMethods()->findOrFail($data['payout_method_id']);
            $data['payment_method'] = $method->payment_method;
            $data['account_title'] = $method->account_title;
            $data['account_number'] = $method->account_number;
            $data['notes'] = trim(collect([$method->bank_name, $method->network, $data['notes'] ?? null])->filter()->join(' · '));
        }
        unset($data['payout_method_id']);
        $settings = WithdrawalSetting::settings();
        $frequency = $this->customerFrequency($request, $settings);
        $policy = WithdrawalSetting::policy($frequency);
        if (! $this->withdrawalDayAllowed($frequency, $policy)) {
            throw ValidationException::withMessages([
                'amount' => 'Withdrawal submissions are closed today. '.$this->withdrawalDayRuleText($frequency, $policy).'.',
            ]);
        }

        $withdrawal = DB::transaction(function () use ($request, $data, $policy, $frequency) {
            $requestsThisPeriod = WithdrawalRequest::where('customer_id', $request->user()->id)
                ->where('status', '!=', 'rejected')
                ->where('created_at', '>=', $this->periodStart($frequency))
                ->lockForUpdate()
                ->count();
            if ($requestsThisPeriod >= $policy['request_limit']) {
                throw ValidationException::withMessages([
                    'amount' => 'You have reached the '.$frequency.' withdrawal request limit.',
                ]);
            }
            if ((float) $data['amount'] < $policy['minimum_amount']) {
                throw ValidationException::withMessages([
                    'amount' => 'The minimum withdrawal amount is Rs '.number_format($policy['minimum_amount'], 2).'.',
                ]);
            }
            if ($policy['maximum_amount'] > 0 && (float) $data['amount'] > $policy['maximum_amount']) {
                throw ValidationException::withMessages([
                    'amount' => 'The maximum withdrawal amount is Rs '.number_format($policy['maximum_amount'], 2).'.',
                ]);
            }

            $payable = (float) Commission::where('beneficiary_id', $request->user()->id)
                ->where('status', 'earned')->lockForUpdate()
                ->selectRaw('COALESCE(SUM(amount - paid_amount), 0) as payable')
                ->value('payable');
            $pending = (float) WithdrawalRequest::where('customer_id', $request->user()->id)
                ->where('status', 'pending')->lockForUpdate()->sum('amount');
            $available = max(0, $payable - $pending);

            if ((float) $data['amount'] > $available) {
                throw ValidationException::withMessages(['amount' => 'The requested amount cannot exceed your available commission of Rs '.number_format($available, 2).'.']);
            }

            return WithdrawalRequest::create($data + [
                'request_number' => 'WDR-'.now()->format('ymdHis').'-'.random_int(100, 999),
                'customer_id' => $request->user()->id,
                'status' => 'pending',
            ]);
        });

        return redirect()->route('customer.withdrawals.index')->with('success', 'Withdrawal request '.$withdrawal->request_number.' submitted successfully.');
    }

    public function recoverPin(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);
        if (! WithdrawalSetting::pinRecoveryEnabled()) {
            return back()->with('error', 'Temporary PIN recovery is disabled by the office. Please contact the office for assistance.');
        }

        $customer = $request->user();
        $key = 'withdrawal-pin-recovery:'.$customer->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 1)) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn($key) / 60));

            return back()->with('error', 'A temporary PIN was already requested. Try again in '.$minutes.' minute'.($minutes === 1 ? '' : 's').'.');
        }

        RateLimiter::hit($key, 600);
        $temporaryPin = (string) random_int(100000, 999999);
        $original = [
            'withdrawal_pin' => $customer->getRawOriginal('withdrawal_pin'),
            'withdrawal_pin_failed_attempts' => $customer->withdrawal_pin_failed_attempts,
            'withdrawal_pin_locked_until' => $customer->withdrawal_pin_locked_until,
        ];

        $customer->forceFill([
            'withdrawal_pin' => $temporaryPin,
            'withdrawal_pin_failed_attempts' => 0,
            'withdrawal_pin_locked_until' => null,
        ])->save();

        try {
            $customer->notify(new WithdrawalPinResetNotification($temporaryPin));
        } catch (Throwable $exception) {
            $customer->forceFill($original)->save();
            RateLimiter::clear($key);
            Log::error('Withdrawal PIN recovery notification failed.', [
                'customer_id' => $customer->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'The temporary PIN could not be delivered. Please try again or contact the office.');
        }

        $channels = collect([
            filled($customer->email) ? 'email' : null,
            config('services.whatsapp.enabled') && filled($customer->phone) ? 'WhatsApp' : null,
        ])->filter()->implode(' and ');

        return back()->with('success', 'A temporary withdrawal PIN was sent by '.($channels ?: 'your available contact channel').'.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'policies' => ['required', 'array'],
            'policies.*.request_limit' => ['required', 'integer', 'min:1', 'max:100'],
            'policies.*.withdrawal_day' => ['nullable', 'integer', 'min:1', 'max:7'],
            'policies.*.withdrawal_day_mode' => ['nullable', Rule::in(['selected_day', 'before_selected_day'])],
            'policies.*.minimum_amount' => ['required', 'numeric', 'min:1'],
            'policies.*.maximum_amount' => ['required', 'numeric', 'min:0'],
        ]);
        foreach (['daily', 'weekly', 'monthly'] as $frequency) {
            $policy = $data['policies'][$frequency] ?? null;
            if (! $policy) {
                throw ValidationException::withMessages(["policies.{$frequency}" => ucfirst($frequency).' settings are required.']);
            }
            $data['policies'][$frequency]['withdrawal_day_mode'] = $policy['withdrawal_day_mode'] ?? 'selected_day';
            if ((float) $policy['maximum_amount'] > 0 && (float) $policy['maximum_amount'] < (float) $policy['minimum_amount']) {
                throw ValidationException::withMessages([
                    "policies.{$frequency}.maximum_amount" => 'The maximum amount must be zero (unlimited) or at least the minimum amount.',
                ]);
            }
        }

        DB::transaction(function () use ($data): void {
            foreach (['daily', 'weekly', 'monthly'] as $frequency) {
                WithdrawalSetting::updateOrCreate(
                    ['frequency' => $frequency],
                    $data['policies'][$frequency] + [
                        'is_default' => $frequency === $data['frequency'],
                    ],
                );
            }

            User::query()
                ->where('role', 'customer')
                ->update(['withdrawal_frequency' => $data['frequency']]);
        });

        return back()->with('success', ucfirst($data['frequency']).' withdrawal policy applied to all customers.');
    }

    public function editSettings(): View
    {
        return view('withdrawal-settings.edit', [
            'settings' => WithdrawalSetting::settings(),
        ]);
    }

    public function review(Request $request, WithdrawalRequest $withdrawalRequest): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['paid', 'rejected'])],
            'transaction_reference' => ['nullable', 'required_if:decision,paid', 'string', 'max:100'],
            'review_notes' => ['nullable', 'required_if:decision,rejected', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $withdrawalRequest, $data): void {
            $withdrawal = WithdrawalRequest::whereKey($withdrawalRequest->id)->lockForUpdate()->firstOrFail();
            if ($withdrawal->status !== 'pending') {
                throw ValidationException::withMessages(['withdrawal' => 'This withdrawal request has already been reviewed.']);
            }

            if ($data['decision'] === 'rejected') {
                $withdrawal->update([
                    'status' => 'rejected',
                    'review_notes' => $data['review_notes'],
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);

                return;
            }

            $commissions = Commission::where('beneficiary_id', $withdrawal->customer_id)
                ->where('status', 'earned')
                ->whereColumn('paid_amount', '<', 'amount')
                ->orderBy('created_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $available = (float) $commissions->sum(
                fn (Commission $commission) => (float) $commission->amount - (float) $commission->paid_amount
            );
            if ($available + 0.009 < (float) $withdrawal->amount) {
                throw ValidationException::withMessages(['withdrawal' => 'The customer no longer has enough payable commission for this request.']);
            }

            $payout = CommissionPayout::create([
                'payout_number' => 'PAY-'.now()->format('ymdHis').'-'.random_int(100, 999),
                'agent_id' => $withdrawal->customer_id,
                'amount' => $withdrawal->amount,
                'fee_amount' => $withdrawal->fee_amount,
                'net_amount' => $withdrawal->net_amount ?? $withdrawal->amount,
                'payment_method' => $withdrawal->payment_method,
                'transaction_reference' => $data['transaction_reference'],
                'notes' => trim('Withdrawal '.$withdrawal->request_number.'. '.($data['review_notes'] ?? '')),
                'paid_by' => $request->user()->id,
                'paid_at' => now(),
            ]);
            $remaining = (float) $withdrawal->amount;
            foreach ($commissions as $commission) {
                if ($remaining <= 0.009) {
                    break;
                }

                $outstanding = (float) $commission->amount - (float) $commission->paid_amount;
                $allocated = min($outstanding, $remaining);
                $newPaidAmount = (float) $commission->paid_amount + $allocated;
                $fullyPaid = $newPaidAmount + 0.009 >= (float) $commission->amount;
                $commission->update([
                    'paid_amount' => $fullyPaid ? $commission->amount : $newPaidAmount,
                    'status' => $fullyPaid ? 'paid' : 'earned',
                    'commission_payout_id' => $fullyPaid ? $payout->id : null,
                ]);
                $remaining -= $allocated;
            }
            $withdrawal->update([
                'status' => 'approved',
                'review_notes' => $data['review_notes'] ?? null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', $data['decision'] === 'paid' ? 'Withdrawal paid and commission payout recorded.' : 'Withdrawal request rejected.');
    }

    private function periodStart(string $frequency): CarbonInterface
    {
        return match ($frequency) {
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => now()->startOfDay(),
        };
    }

    private function withdrawalDayAllowed(string $frequency, array $policy): bool
    {
        $day = $policy['withdrawal_day'] ?? null;
        if ($day === null) {
            return true;
        }

        return ($policy['withdrawal_day_mode'] ?? 'selected_day') === 'before_selected_day'
            ? now()->isoWeekday() < (int) $day
            : now()->isoWeekday() === (int) $day;
    }

    private function withdrawalDayLabel(string $frequency, array $policy): string
    {
        $day = $policy['withdrawal_day'] ?? null;
        if ($day === null) {
            return 'on any day';
        }

        return 'every '.now()->startOfWeek()->addDays((int) $day - 1)->format('l');
    }

    private function withdrawalDayRuleText(string $frequency, array $policy): string
    {
        if (($policy['withdrawal_day'] ?? null) === null) {
            return 'Requests are accepted on any day';
        }

        $day = str_replace('every ', '', $this->withdrawalDayLabel($frequency, $policy));

        return ($policy['withdrawal_day_mode'] ?? 'selected_day') === 'before_selected_day'
            ? 'Requests are accepted before '.$day
            : 'Requests are accepted only on '.$day;
    }

    private function customerFrequency(Request $request, array $settings): string
    {
        $frequency = $request->user()->withdrawal_frequency;

        return in_array($frequency, ['daily', 'weekly', 'monthly'], true)
            ? $frequency
            : $settings['frequency'];
    }
}
