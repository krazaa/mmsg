<?php

namespace App\Models;

use App\Enums\PlotStatus;
use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plot extends Model
{
    use TracksUserstamps;

    protected $fillable = [
        'project_id',
        'block_id',
        'plot_number',
        'size_marla',
        'category',
        'base_price',
        'premium_amount',
        'total_price',
        'status',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'size_marla' => 'decimal:2',
            'base_price' => 'decimal:2',
            'premium_amount' => 'decimal:2',
            'total_price' => 'decimal:2',
            'status' => PlotStatus::class,
            'version' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function allotment()
    {
        return $this->hasOne(PlotAllotment::class);
    }
}
