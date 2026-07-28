<?php

namespace App\Http\Controllers;

use App\Contracts\BookingPaymentRecorder;
use App\Contracts\CommissionDistributor;
use App\Mail\PaymentVerifiedMail;
use App\Mail\PlanActivatedMail;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Notifications\AccountActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::orderBy('name')->get();
        $payments = Payment::with(['booking.project', 'customer', 'installment'])
            ->whereHas('booking')
            ->when($request->integer('project'), fn ($q, $id) => $q->whereHas('booking', fn ($b) => $b->where('project_id', $id)))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = '%'.$request->string('search')->trim().'%';
                $q->where(fn ($i) => $i->where('receipt_number', 'like', $s)->orWhere('transaction_reference', 'like', $s)->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $s)));
            })
            ->latest('payment_date')->paginate(25)->withQueryString();

        return view('payments.index', compact('projects', 'payments'));
    }

    public function edit(Payment $payment)
    {
        $payment->load('booking', 'customer', 'installment');
        $proofMime = $payment->proof_path && Storage::disk('local')->exists($payment->proof_path)
            ? Storage::disk('local')->mimeType($payment->proof_path) : null;

        return view('payments.edit', compact('payment', 'proofMime'));
    }

    public function proof(Request $request, Payment $payment)
    {
        abort_unless($payment->proof_path && Storage::disk('local')->exists($payment->proof_path), 404);

        if ($request->boolean('download')) {
            return Storage::disk('local')->download($payment->proof_path, $payment->proof_original_name ?? 'payment-proof');
        }

        return Storage::disk('local')->response($payment->proof_path, $payment->proof_original_name ?? 'payment-proof');
    }

    public function update(Request $request, Payment $payment, CommissionDistributor $commissions)
    {
        if ($request->has('file_no')) {
            $request->merge(['file_no' => Str::upper(trim($request->string('file_no')->toString()))]);
        }

        $requiresFileNumber = $payment->status === 'pending'
            && $request->string('status')->toString() === 'verified'
            && $payment->installment_schedule_id === null;

        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'cheque', 'card', 'easypaisa', 'jazzcash', 'online_transfer', 'direct_deposit', 'crypto'])],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['pending', 'verified', 'reversed'])],
            'file_no' => [
                Rule::requiredIf($requiresFileNumber),
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'file_no'),
            ],
            'verification_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $fileNumber = $data['file_no'] ?? null;
        unset($data['file_no']);
        if ($payment->status === 'reversed' && $data['status'] === 'verified') {
            throw ValidationException::withMessages(['status' => 'A reversed receipt cannot be re-verified. Record a new payment instead.']);
        }
        if ($payment->status === 'verified' && $data['status'] === 'reversed' && Commission::where('payment_id', $payment->id)->where('status', 'paid')->exists()) {
            throw ValidationException::withMessages(['status' => 'This payment has paid-out commission and cannot be reversed until the payout is resolved.']);
        }
        $becameVerified = false;
        $becameActive = false;
        $becameReversed = false;
        DB::transaction(function () use ($payment, $data, $fileNumber, $commissions, &$becameVerified, &$becameActive, &$becameReversed) {
            $locked = Payment::lockForUpdate()->findOrFail($payment->id);
            if ($locked->status === 'pending' && $data['status'] === 'verified') {
                $becameVerified = true;
                $booking = Booking::with('customer')->lockForUpdate()->findOrFail($locked->booking_id);
                if (! $booking->agent_id && $booking->customer?->referral_agent_id) {
                    $booking->update(['agent_id' => $booking->customer->referral_agent_id]);
                }
                $installment = $locked->installment()->lockForUpdate()->first();
                if ($installment) {
                    $remaining = (float) $installment->total_due - (float) $installment->paid_amount;
                    if ((float) $locked->amount > $remaining) {
                        throw ValidationException::withMessages(['status' => 'Payment exceeds the current remaining installment balance of Rs '.number_format($remaining, 2).'.']);
                    }
                    $paid = (float) $installment->paid_amount + (float) $locked->amount;
                    $installment->update(['paid_amount' => $paid, 'status' => $paid >= (float) $installment->total_due ? 'paid' : 'partial']);
                }
                if (! $installment) {
                    if (User::whereRaw('UPPER(TRIM(file_no)) = ?', [$fileNumber])->lockForUpdate()->exists()) {
                        throw ValidationException::withMessages([
                            'file_no' => 'This customer file number already exists. Enter a new unused file number.',
                        ]);
                    }
                    $booking->customer->update(['file_no' => $fileNumber]);
                }
                if (! $installment && $booking->status === 'approved') {
                    $project = Project::lockForUpdate()->findOrFail($booking->project_id);
                    $project->decrement('reserved_area_marla', (float) $booking->package->size_marla);
                    $project->increment('sold_area_marla', (float) $booking->package->size_marla);
                    $booking->update(['status' => 'active']);
                    $becameActive = true;
                }
                $commissions->distribute($locked, $booking->refresh()->load('customer'));
                $data['verified_at'] = now();
            }
            if ($locked->status === 'verified' && $data['status'] === 'reversed') {
                $becameReversed = true;
                $installment = $locked->installment()->lockForUpdate()->first();
                if ($installment) {
                    $paid = max(0, (float) $installment->paid_amount - (float) $locked->amount);
                    $status = $paid <= 0 ? 'pending' : ($paid >= (float) $installment->total_due ? 'paid' : 'partial');
                    $installment->update(['paid_amount' => $paid, 'status' => $status]);
                }Commission::where('payment_id', $locked->id)->update(['status' => 'reversed', 'updated_by' => auth()->id()]);
            }$locked->update($data + ['verified_by' => auth()->id()]);
        });

        if ($becameVerified) {
            $verifiedPayment = $payment->fresh()->load(['customer', 'booking.project', 'booking.package', 'installment']);
            Mail::to($verifiedPayment->customer->email)->send(new PaymentVerifiedMail($verifiedPayment));
            $verifiedPayment->customer->notify(new AccountActivityNotification('Payment verified', 'Your payment has been verified and credited to your account.', 'payment', route('dashboard').'#payments', ['Receipt' => $verifiedPayment->receipt_number, 'Amount' => 'Rs '.number_format($verifiedPayment->amount, 2)]));
            if ($becameActive) {
                Mail::to($verifiedPayment->customer->email)->send(new PlanActivatedMail($verifiedPayment->booking));
                $verifiedPayment->customer->notify(new AccountActivityNotification('Property plan activated', 'Your first payment was verified and the property plan is now active.', 'booking', route('dashboard').'#payments', ['Booking' => $verifiedPayment->booking->booking_number, 'Project' => $verifiedPayment->booking->project->name]));
            }
        }

        if ($becameReversed) {
            $reversedPayment = $payment->fresh()->load('customer');
            $reversedPayment->customer->notify(new AccountActivityNotification('Payment reversed', 'A previously verified payment was reversed. Please contact the office if you need assistance.', 'payment', route('dashboard').'#payments', ['Receipt' => $reversedPayment->receipt_number, 'Amount' => 'Rs '.number_format($reversedPayment->amount, 2)]));
        }

        return redirect()->route('payments.index')->with('success', 'Payment updated.');
    }

    public function store(Request $request, Booking $booking, BookingPaymentRecorder $payments)
    {
        $data = $request->validate([
            'installment_number' => ['required', 'integer', 'between:1,36'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'in:cash,bank_transfer,cheque,card,easypaisa,jazzcash'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
        ]);
        $payment = $payments->recordPayment($booking, $data);
        $booking->customer->notify(new AccountActivityNotification('Payment recorded and verified', 'The office recorded a verified payment on your account.', 'payment', route('dashboard').'#payments', ['Receipt' => $payment->receipt_number, 'Amount' => 'Rs '.number_format($payment->amount, 2)]));

        return back()->with('success', 'Payment recorded and up to three commission levels posted.');
    }
}
