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

        $rules = CommissionRule::query()->where('package_id', $booking->package_id)
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
                    'amount' => (float) $payment->amount * (float) $rule->percentage / 100,
                    'status' => 'earned',
                ]
            );
            $beneficiary = Referral::query()->where('user_id', $beneficiary)->value('sponsor_id');
        }
    }
}
