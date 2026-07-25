<?php

namespace App\Models;

use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    use TracksUserstamps;

    protected $guarded = [];

    public function package()
    {
        return $this->belongsTo(PlotPackage::class, 'package_id');
    }
}
