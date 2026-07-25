<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\WithdrawalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerWithdrawalController extends Controller
{
    public function managementIndex(Request $request): View
    {
        $status = in_array($request->string('status')->toString(), ['all', 'pending', 'approved', 'rejected'], true)
            ? $request->string('status')->toString()
            : 'pending';
        $search = $request->string('search')->trim()->toString();
        $query = WithdrawalRequest::with('customer');
        $totalCount = (clone $query)->count();
        $pendingCount = (clone $query)->where('status', 'pending')->count();
        $pendingAmount = (float) (clone $query)->where('status', 'pending')->sum('amount');
        $approvedAmount = (float) (clone $query)->where('status', 'approved')->sum('amount');
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

        return view('withdrawal-requests.index', compact(
            'withdrawals', 'status', 'search', 'totalCount', 'pendingCount', 'pendingAmount', 'approvedAmount'
        ));
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
        $withdrawn = (float) WithdrawalRequest::where('customer_id', $request->user()->id)->where('status', 'approved')->sum('amount');
        $available = max(0, $payable - $pending);
        $withdrawals = WithdrawalRequest::where('customer_id', $request->user()->id)->latest()->paginate(15);

        return view('customer-withdrawals.index', compact('payable', 'lifetime', 'pending', 'withdrawn', 'available', 'withdrawals'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', Rule::in(['bank_transfer', 'easypaisa', 'jazzcash', 'crypto'])],
            'account_title' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $withdrawal = DB::transaction(function () use ($request, $data) {
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
            if (abs((float) $data['amount'] - $available) > 0.009) {
                throw ValidationException::withMessages(['amount' => 'Withdrawal requests must use the full available commission of Rs '.number_format($available, 2).'.']);
            }

            return WithdrawalRequest::create($data + [
                'request_number' => 'WDR-'.now()->format('ymdHis').'-'.random_int(100, 999),
                'customer_id' => $request->user()->id,
                'status' => 'pending',
            ]);
        });

        return redirect()->route('customer.withdrawals.index')->with('success', 'Withdrawal request '.$withdrawal->request_number.' submitted successfully.');
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
}
