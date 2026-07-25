<?php

namespace App\Models;

use App\Models\Concerns\AuditsModelChanges;
use App\Models\Concerns\TracksUserstamps;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySetting extends Model
{
    use AuditsModelChanges, TracksUserstamps;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'credentials' => 'encrypted:array',
            'last_tested_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->useLogName(class_basename($this))
            ->logAll()
            ->logExcept(['credentials'])
            ->dontSubmitEmptyLogs();
    }

    public function isComplete(): bool
    {
        $credentials = $this->credentials ?? [];
        $required = match ($this->provider) {
            'jazzcash' => ['merchant_id', 'password', 'integrity_salt'],
            'easypaisa' => ['store_id', 'hash_key'],
            'binance' => ['merchant_id', 'api_key', 'secret_key'],
            default => [],
        };

        return $required !== []
            && collect($required)->every(fn ($field) => filled($credentials[$field] ?? null))
            && filled($this->api_url)
            && filled($this->return_url);
    }
}
