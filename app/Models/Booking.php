<?php

namespace App\Models;

use App\Models\Concerns\AuditsModelChanges;
use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use AuditsModelChanges, TracksUserstamps;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['booking_date' => 'date', 'total_price' => 'decimal:2', 'booking_amount' => 'decimal:2', 'financed_amount' => 'decimal:2'];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function package()
    {
        return $this->belongsTo(PlotPackage::class, 'package_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function installments()
    {
        return $this->hasMany(InstallmentSchedules::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function allotment()
    {
        return $this->hasOne(PlotAllotment::class);
    }
}
