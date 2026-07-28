<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\InstallmentSchedules;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\AccountActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerPaymentController extends Controller
{
    public function receipt(Request $request, Payment $payment)
    {
        abort_unless($request->user()->role === 'customer' && $request->user()->customer, 403);
        abort_unless($payment->customer_id === $request->user()->customer->id, 404);

        $payment->load(['booking.project', 'booking.package', 'installment', 'customer']);

        return view('customer-payments.receipt', compact('payment'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'customer' && $request->user()->customer, 403);
        $data = $request->validate([
            'booking_id' => ['required', 'integer'],
            'payment_type' => ['required', Rule::in(['booking', 'installment'])],
            'installment_id' => ['nullable', 'required_if:payment_type,installment', 'integer'],
            'amount' => ['nullable', 'required_if:payment_type,installment', 'numeric', 'gt:0'],
            'payment_method' => ['required', Rule::exists('payment_methods', 'code')->where(fn ($query) => $query->where('customer_portal', true)->where('status', true))],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,jfif,png,webp,gif,bmp,heic,heif,pdf', 'max:300'],
        ]);

        $customer = $request->user()->customer;
        $booking = Booking::where('customer_id', $customer->id)
            ->where('status', $data['payment_type'] === 'booking' ? 'approved' : 'active')->findOrFail($data['booking_id']);
        if ($data['payment_type'] === 'installment' && ! Payment::where('booking_id', $booking->id)->whereNull('installment_schedule_id')->where('status', 'verified')->exists()) {
            throw ValidationException::withMessages(['payment_type' => 'The first payment must be verified before paying installments.']);
        }
        $installment = $data['payment_type'] === 'installment'
            ? InstallmentSchedules::where('booking_id', $booking->id)->whereDate('due_date', '<=', today()->endOfMonth())->findOrFail($data['installment_id'])
            : null;
        $path = $request->file('proof')->store('payment-proofs/'.$customer->id, 'local');

        try {
            $payment = DB::transaction(function () use ($data, $customer, $booking, $installment, $path, $request) {
                $locked = $installment ? InstallmentSchedules::lockForUpdate()->findOrFail($installment->id) : null;
                if ($locked) {
                    $pending = Payment::where('installment_schedule_id', $locked->id)->where('status', 'pending')->sum('amount');
                    $available = max(0, (float) $locked->total_due - (float) $locked->paid_amount - (float) $pending);
                    if (abs((float) $data['amount'] - $available) > 0.009) {
                        throw ValidationException::withMessages(['amount' => 'The installment must be paid in full. Required amount: Rs '.number_format($available, 2).'.']);
                    }
                    $amount = (float) $data['amount'];
                } else {
                    if (Payment::where('booking_id', $booking->id)->whereNull('installment_schedule_id')->whereIn('status', ['pending', 'verified'])->exists()) {
                        throw ValidationException::withMessages(['payment_type' => 'The first payment has already been submitted.']);
                    }
                    $amount = (float) $booking->booking_amount;
                }

                return Payment::create([
                    'receipt_number' => $locked
                        ? 'INSTL-'.now()->format('ymdHis').'-'.random_int(100, 999)
                        : 'BKG-PAY-'.now()->format('ymdHis').random_int(100, 999),
                    'booking_id' => $booking->id,
                    'customer_id' => $customer->id,
                    'installment_schedule_id' => $locked?->id,
                    'amount' => $amount,
                    'payment_method' => $data['payment_method'],
                    'transaction_reference' => $data['transaction_reference'] ?? null,
                    'proof_path' => $path,
                    'proof_original_name' => $request->file('proof')->getClientOriginalName(),
                    'payment_date' => now(),
                    'status' => 'pending',
                ]);
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        $customer->user->notify(new AccountActivityNotification('Payment submitted for verification', 'Your payment proof was received and is now under office review.', 'payment', route('dashboard').'#payments', ['Receipt' => $payment->receipt_number, 'Amount' => 'Rs '.number_format($payment->amount, 2)]));
        User::whereIn('role', ['super_admin', 'admin'])->where('status', true)->each(fn (User $admin) => $admin->notify(new AccountActivityNotification(
            'Customer payment needs verification',
            $customer->name.' submitted payment proof for office review.',
            'payment',
            route('payments.edit', $payment),
            ['Customer' => $customer->name, 'Receipt' => $payment->receipt_number, 'Amount' => 'Rs '.number_format($payment->amount, 2), 'Booking' => $booking->booking_number],
            false,
        )));

        return back()->with('success', 'Payment proof submitted. The payment will appear as verified after office review.');
    }
}
