<?php

namespace App\Models;

use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class EmailCampaignRecipient extends Model
{
    use TracksUserstamps;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['dispatched_at' => 'datetime', 'sent_at' => 'datetime', 'failed_at' => 'datetime'];
    }

    public function campaign()
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
