<?php

namespace App\Contracts;

use App\Models\Booking;
use App\Models\Payment;

interface CommissionDistributor
{
    public function distribute(Payment $payment, Booking $booking): void;
}
