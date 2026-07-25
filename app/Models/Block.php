<?php

namespace App\Models;

use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use TracksUserstamps;

    protected $guarded = [];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function plots()
    {
        return $this->hasMany(Plot::class);
    }
}
