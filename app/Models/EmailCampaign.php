<?php

namespace App\Models;

use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    use TracksUserstamps;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['filters' => 'array', 'completed_at' => 'datetime'];
    }

    public function recipients()
    {
        return $this->hasMany(EmailCampaignRecipient::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
