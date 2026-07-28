<?php

namespace App\Http\Controllers;

use App\Services\PaymentGatewayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentGatewaySettingController extends Controller
{
    public function __construct(private readonly PaymentGatewayService $gateways) {}

    public function index(): View
    {
        $gateways = $this->gateways->settings();

        return view('payment-gateways.index', compact('gateways'));
    }

    public function update(Request $request, string $provider): RedirectResponse
    {
        $this->gateways->assertSupported($provider);

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

        $requestedEnabled = $request->boolean('enabled');
        $setting = $this->gateways->save($provider, $data, $requestedEnabled);

        if ($requestedEnabled && ! $setting->enabled) {
            return back()->withErrors(['gateway' => 'Settings were saved, but the gateway remains disabled until every required credential is complete.']);
        }

        return back()->with('success', ucfirst($provider).' gateway settings saved securely.');
    }
}
