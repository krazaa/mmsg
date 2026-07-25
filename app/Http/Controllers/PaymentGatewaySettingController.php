<?php

namespace App\Http\Controllers;

use App\Models\PaymentGatewaySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentGatewaySettingController extends Controller
{
    public function index(): View
    {
        $gateways = collect(['jazzcash', 'easypaisa', 'binance'])->mapWithKeys(fn ($provider) => [
            $provider => PaymentGatewaySetting::firstOrCreate(['provider' => $provider], ['mode' => 'sandbox', 'enabled' => false]),
        ]);

        return view('payment-gateways.index', compact('gateways'));
    }

    public function update(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['jazzcash', 'easypaisa', 'binance'], true), 404);

        $data = $request->validate([
            'mode' => ['required', Rule::in(['sandbox', 'live'])],
            'api_url' => ['required', 'url', 'max:500'],
            'return_url' => ['required', 'url', 'max:500'],
            'merchant_id' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'integrity_salt' => ['nullable', 'string', 'max:255'],
            'store_id' => ['nullable', 'string', 'max:255'],
            'hash_key' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'secret_key' => ['nullable', 'string', 'max:255'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        $setting = PaymentGatewaySetting::firstOrCreate(
            ['provider' => $provider],
            ['mode' => 'sandbox', 'enabled' => false],
        );
        $credentials = $setting->credentials ?? [];
        $credentialFields = match ($provider) {
            'jazzcash' => ['merchant_id', 'password', 'integrity_salt'],
            'easypaisa' => ['store_id', 'hash_key'],
            'binance' => ['merchant_id', 'api_key', 'secret_key'],
        };
        foreach ($credentialFields as $field) {
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

        $requestedEnabled = $request->boolean('enabled');
        $setting->enabled = $requestedEnabled && $setting->isComplete();
        $setting->save();

        if ($requestedEnabled && ! $setting->enabled) {
            return back()->withErrors(['gateway' => 'Settings were saved, but the gateway remains disabled until every required credential is complete.']);
        }

        return back()->with('success', ucfirst($provider).' gateway settings saved securely.');
    }
}
