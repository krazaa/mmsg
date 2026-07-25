<?php

namespace App\Contracts;

use App\Models\Booking;

interface BookingCreator
{
    public function create(array $data): Booking;
}
