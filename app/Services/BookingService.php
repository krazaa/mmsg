<?php

namespace App\Services;

use App\Contracts\BookingCreator;
use App\Contracts\BookingPaymentRecorder;
use App\Contracts\CommissionDistributor;
use App\Contracts\InstallmentScheduleGenerator;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\InstallmentSchedules;
use App\Models\Payment;
use App\Models\PlotPackage;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService implements BookingCreator, BookingPaymentRecorder
{
    public function __construct(
        private readonly InstallmentScheduleGenerator $schedules,
        private readonly CommissionDistributor $commissions,
    ) {}

    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $package = PlotPackage::query()->where('status', true)->findOrFail($data['package_id']);
            $project = Project::query()->lockForUpdate()->findOrFail($package->project_id);

            if (! $project->status) {
                throw ValidationException::withMessages(['package_id' => 'This project is inactive and cannot accept bookings.']);
            }

            if ($project->available_area_marla < (float) $package->size_marla) {
                throw ValidationException::withMessages(['package_id' => 'This package is unavailable. The project does not have enough saleable land remaining.']);
            }

            if (! empty($data['customer_id'])) {
                $customer = Customer::query()->where('status', true)->findOrFail($data['customer_id']);
            } else {
                $customer = Customer::create([
                    'password' => str()->random(40), 'email_verified_at' => now(),
                    'name' => $data['name'], 'father_name' => $data['father_name'] ?? null,
                    'referral_agent_id' => $data['agent_id'] ?? null,
                    'cnic' => $data['cnic'], 'phone' => $data['phone'],
                    'email' => $data['email'] ?? null, 'address' => $data['address'] ?? null,
                ]);
            }

            $agentId = ! empty($data['customer_id'])
                ? $customer->referral_agent_id
                : ($data['agent_id'] ?? $customer->referral_agent_id);

            $booking = Booking::create([
                'booking_number' => 'BKG-'.now()->format('ymd').'-'.strtoupper(substr((string) str()->uuid(), 0, 6)),
                'project_id' => $project->id, 'package_id' => $package->id,
                'customer_id' => $customer->id, 'agent_id' => $agentId,
                'booking_date' => $data['booking_date'], 'total_price' => $package->total_price,
                'booking_amount' => $package->booking_amount,
                'financed_amount' => $package->total_price - (float) $package->booking_amount,
                'status' => 'active',
            ]);

            $this->schedules->generate($booking);

            $project->increment('sold_area_marla', (float) $package->size_marla);
            $payment = Payment::create([
                'receipt_number' => 'BKG-PAY-'.now()->format('ymdHis').random_int(100, 999),
                'booking_id' => $booking->id, 'customer_id' => $customer->id,
                'amount' => $package->booking_amount, 'payment_method' => $data['payment_method'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'payment_date' => now(), 'status' => 'verified', 'verified_by' => auth()->id(), 'verified_at' => now(),
            ]);
            $this->commissions->distribute($payment, $booking);

            return $booking->load('customer', 'package', 'installments', 'payments');
        }, 5);
    }

    public function recordPayment(Booking $booking, array $data): Payment
    {
        return DB::transaction(function () use ($booking, $data) {
            $installment = InstallmentSchedules::query()->where('booking_id', $booking->id)
                ->where('installment_number', $data['installment_number'])->lockForUpdate()->firstOrFail();
            $remaining = (float) $installment->total_due - (float) $installment->paid_amount;
            if ((float) $data['amount'] > $remaining) {
                throw ValidationException::withMessages(['amount' => 'Payment exceeds the remaining installment balance of Rs '.number_format($remaining).'.']);
            }
            $payment = Payment::create([
                'receipt_number' => 'RC-'.now()->format('ymdHis').'-'.random_int(100, 999),
                'booking_id' => $booking->id, 'customer_id' => $booking->customer_id,
                'installment_schedule_id' => $installment->id,
                'amount' => $data['amount'], 'payment_method' => $data['payment_method'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'payment_date' => now(), 'status' => 'verified', 'verified_by' => auth()->id(), 'verified_at' => now(),
            ]);
            $installment->increment('paid_amount', $data['amount']);
            $installment->refresh()->update(['status' => (float) $installment->paid_amount >= (float) $installment->total_due ? 'paid' : 'partial']);
            $this->commissions->distribute($payment, $booking);

            return $payment;
        }, 5);
    }
}
