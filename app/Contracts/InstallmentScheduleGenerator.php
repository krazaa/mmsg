<?php

namespace App\Contracts;

use App\Models\Booking;

interface InstallmentScheduleGenerator
{
    public function generate(Booking $booking): void;
}
