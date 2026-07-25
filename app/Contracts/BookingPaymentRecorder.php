<?php

namespace App\Contracts;

use App\Models\Booking;
use App\Models\Payment;

interface BookingPaymentRecorder
{
    public function recordPayment(Booking $booking, array $data): Payment;
}
