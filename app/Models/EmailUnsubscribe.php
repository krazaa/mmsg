<?php

namespace App\Models;

use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class EmailUnsubscribe extends Model
{
    use TracksUserstamps;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['unsubscribed_at' => 'datetime'];
    }
}
