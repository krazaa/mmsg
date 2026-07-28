<?php

namespace App\Services;

use App\Models\PaymentGatewaySetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentGatewayService
{
    private const PROVIDERS = [
        'jazzcash' => ['merchant_id', 'password', 'integrity_salt'],
        'easypaisa' => ['store_id', 'hash_key'],
        'binance' => ['merchant_id', 'api_key', 'secret_key'],
    ];

    public function settings(): Collection
    {
        return collect(array_keys(self::PROVIDERS))->mapWithKeys(fn (string $provider) => [
            $provider => $this->setting($provider),
        ]);
    }

    public function save(string $provider, array $data, bool $requestedEnabled): PaymentGatewaySetting
    {
        $this->assertSupported($provider);

        return DB::transaction(function () use ($provider, $data, $requestedEnabled): PaymentGatewaySetting {
            $setting = $this->setting($provider);
            $credentials = $setting->credentials ?? [];

            foreach (self::PROVIDERS[$provider] as $field) {
                if (filled($data[$field] ?? null)) {
                    $credentials[$field] = $data[$field];
                }
            }

            $setting->fill([
                'mode' => $data['mode'],
                'api_url' => $data['api_url'],
                'return_url' => $data['return_url'],
                'credentials' => $credentials,
                'enabled' => false,
            ]);

            $setting->enabled = $requestedEnabled && $setting->isComplete();
            $setting->save();

            return $setting;
        });
    }

    public function supports(string $provider): bool
    {
        return array_key_exists($provider, self::PROVIDERS);
    }

    public function assertSupported(string $provider): void
    {
        if (! $this->supports($provider)) {
            throw new NotFoundHttpException('Payment gateway provider not found.');
        }
    }

    private function setting(string $provider): PaymentGatewaySetting
    {
        $this->assertSupported($provider);

        return PaymentGatewaySetting::firstOrCreate(
            ['provider' => $provider],
            ['mode' => 'sandbox', 'enabled' => false],
        );
    }
}
