<?php

namespace App\Services;

use App\Contracts\InstallmentScheduleGenerator;
use App\Models\Booking;
use App\Models\InstallmentSchedules;
use Illuminate\Support\Carbon;

class InstallmentScheduleService implements InstallmentScheduleGenerator
{
    public function generate(Booking $booking): void
    {
        $package = $booking->package;
        $balloonPayments = collect($package->balloonPayments())->pluck('amount', 'month');

        for ($month = 1; $month <= $package->months; $month++) {
            $balloon = (float) $balloonPayments->get($month, 0);
            InstallmentSchedules::create([
                'booking_id' => $booking->id,
                'installment_number' => $month,
                'due_date' => Carbon::parse($booking->booking_date)->addMonthsNoOverflow($month - 1),
                'regular_amount' => $package->monthly_amount,
                'balloon_amount' => $balloon,
                'total_due' => (float) $package->monthly_amount + $balloon,
            ]);
        }
    }
}
