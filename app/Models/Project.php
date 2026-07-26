<?php

namespace App\Models;

use App\Models\Concerns\AuditsModelChanges;
use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    use AuditsModelChanges, TracksUserstamps;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['gross_area_marla' => 'decimal:2', 'saleable_area_marla' => 'decimal:2', 'sold_area_marla' => 'decimal:2', 'reserved_area_marla' => 'decimal:2', 'status' => 'boolean'];
    }

    public function packages()
    {
        return $this->hasMany(PlotPackage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function blocks()
    {
        return $this->hasMany(Block::class);
    }

    public function plots()
    {
        return $this->hasMany(Plot::class);
    }

    public function getAvailableAreaMarlaAttribute(): float
    {
        return max(0, (float) $this->saleable_area_marla - (float) $this->sold_area_marla - (float) $this->reserved_area_marla);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->image_path);
    }

    public function getBlueprintUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->blueprint_path);
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'projects/')) {
            return Storage::disk('public')->url($path);
        }

        if (is_file(public_path($path))) {
            return asset($path);
        }

        return null;
    }
}
