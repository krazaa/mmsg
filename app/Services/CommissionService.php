<?php

namespace App\Services;

use App\Contracts\CommissionDistributor;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Payment;
use App\Models\Referral;

class CommissionService implements CommissionDistributor
{
    public function distribute(Payment $payment, Booking $booking): void
    {
        $beneficiary = $booking->agent_id ?: $booking->customer?->referral_agent_id;
        if ($beneficiary && ! $booking->agent_id) {
            $booking->update(['agent_id' => $beneficiary]);
        }

        $commissionType = $booking->payment_plan === 'cash'
            ? 'cash'
            : ($payment->installment_schedule_id === null ? 'first_payment' : 'installment');

        $rules = CommissionRule::query()->where('package_id', $booking->package_id)
            ->where('payment_plan', $commissionType)
            ->where('status', true)->orderBy('level')->limit(3)->get();

        foreach ($rules as $rule) {
            if (! $beneficiary) {
                break;
            }

            Commission::firstOrCreate(
                ['payment_id' => $payment->id, 'level' => $rule->level],
                [
                    'booking_id' => $booking->id,
                    'beneficiary_id' => $beneficiary,
                    'percentage' => $rule->percentage,
                    'calculation_type' => $rule->calculation_type,
                    'fixed_amount' => $rule->fixed_amount,
                    'amount' => $rule->calculation_type === 'fixed'
                        ? (float) $rule->fixed_amount
                        : (float) $payment->amount * (float) $rule->percentage / 100,
                    'status' => 'earned',
                ]
            );
            $beneficiary = Referral::query()->where('user_id', $beneficiary)->value('sponsor_id');
        }
    }
}
