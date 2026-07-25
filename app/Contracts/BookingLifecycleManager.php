<?php

namespace App\Contracts;

use App\Models\Booking;

interface BookingLifecycleManager
{
    public function update(Booking $booking, array $data): bool;
}
