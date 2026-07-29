<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalSetting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'request_limit' => 'integer',
            'is_default' => 'boolean',
            'minimum_amount' => 'decimal:2',
            'maximum_amount' => 'decimal:2',
            'fee_enabled' => 'boolean',
            'fee_value' => 'decimal:2',
            'pin_recovery_enabled' => 'boolean',
        ];
    }

    public static function policies(): array
    {
        $saved = static::query()->get()->keyBy('frequency');

        return collect(['daily', 'weekly', 'monthly'])->mapWithKeys(function (string $frequency) use ($saved) {
            $setting = $saved->get($frequency);

            return [$frequency => [
                'request_limit' => (int) ($setting?->request_limit ?? 1),
                'minimum_amount' => (float) ($setting?->minimum_amount ?? 1),
                'maximum_amount' => (float) ($setting?->maximum_amount ?? 0),
            ]];
        })->all();
    }

    public static function settings(): array
    {
        return [
            'frequency' => static::query()->where('is_default', true)->value('frequency') ?: 'weekly',
            'policies' => static::policies(),
            'fee' => static::fee(),
            'pin_recovery_enabled' => static::pinRecoveryEnabled(),
        ];
    }

    public static function pinRecoveryEnabled(): bool
    {
        return (bool) (static::query()->orderBy('id')->value('pin_recovery_enabled') ?? true);
    }

    public static function fee(): array
    {
        $setting = static::query()->orderBy('id')->first();

        return [
            'enabled' => (bool) ($setting?->fee_enabled ?? false),
            'type' => in_array($setting?->fee_type, ['fixed', 'percentage'], true) ? $setting->fee_type : 'fixed',
            'value' => (float) ($setting?->fee_value ?? 0),
        ];
    }

    public static function calculateFee(float $amount, ?array $fee = null): float
    {
        $fee ??= static::fee();
        if (! $fee['enabled'] || $fee['value'] <= 0) {
            return 0;
        }

        return round($fee['type'] === 'percentage' ? $amount * $fee['value'] / 100 : $fee['value'], 2);
    }

    public static function policy(string $frequency): array
    {
        $policies = static::policies();

        return $policies[$frequency] ?? $policies['daily'];
    }
}
