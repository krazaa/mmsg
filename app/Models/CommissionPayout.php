<?php

namespace App\Models;

use App\Models\Concerns\AuditsModelChanges;
use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class CommissionPayout extends Model
{
    use AuditsModelChanges, TracksUserstamps;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'fee_amount' => 'decimal:2', 'net_amount' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
