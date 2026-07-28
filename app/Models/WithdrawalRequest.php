<?php

namespace App\Models;

use App\Models\Concerns\AuditsModelChanges;
use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use AuditsModelChanges, TracksUserstamps;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
