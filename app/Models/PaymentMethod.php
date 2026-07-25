<?php

namespace App\Models;

use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use TracksUserstamps;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'customer_portal' => 'boolean',
            'status' => 'boolean',
        ];
    }
}
