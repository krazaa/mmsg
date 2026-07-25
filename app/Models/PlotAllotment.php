<?php

namespace App\Models;

use App\Models\Concerns\AuditsModelChanges;
use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class PlotAllotment extends Model
{
    use AuditsModelChanges, TracksUserstamps;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['allotment_date' => 'date'];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }

    public function allottedBy()
    {
        return $this->belongsTo(User::class, 'allotted_by');
    }
}
