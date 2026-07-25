<?php

namespace App\Models;

use App\Models\Concerns\AuditsModelChanges;
use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use AuditsModelChanges, TracksUserstamps;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['payment_date' => 'datetime', 'amount' => 'decimal:2', 'verified_at' => 'datetime'];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function installment()
    {
        return $this->belongsTo(InstallmentSchedules::class, 'installment_schedule_id');
    }
}
