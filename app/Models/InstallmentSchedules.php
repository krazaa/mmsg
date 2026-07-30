<?php

namespace App\Models;

use App\Models\Concerns\AuditsModelChanges;
use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class InstallmentSchedules extends Model
{
    use AuditsModelChanges, TracksUserstamps;

    protected $table = 'installment_schedules';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'regular_amount' => 'decimal:2', 'balloon_amount' => 'decimal:2', 'total_due' => 'decimal:2', 'paid_amount' => 'decimal:2', 'reminder_sent_at' => 'datetime', 'overdue_reminder_sent_at' => 'datetime'];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
