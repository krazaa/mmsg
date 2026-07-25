<?php

namespace App\Models;

use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use TracksUserstamps;

    protected $guarded = [];

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }
}
