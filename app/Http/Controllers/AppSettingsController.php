<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\WithdrawalSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppSettingsController extends Controller
{
    public function edit(): View
    {
        return view('app-settings.edit', [
            'fee' => WithdrawalSetting::fee(),
            'pinRecoveryEnabled' => WithdrawalSetting::pinRecoveryEnabled(),
            'showReferralCode' => SiteSetting::showReferralCodesOnCustomerPortal(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fee_enabled' => ['required', 'boolean'],
            'fee_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'fee_value' => ['required', 'numeric', 'min:0'],
            'pin_recovery_enabled' => ['required', 'boolean'],
            'customer_portal_show_referral_code' => ['required', 'boolean'],
        ]);

        if ($data['fee_type'] === 'percentage' && (float) $data['fee_value'] > 100) {
            throw ValidationException::withMessages([
                'fee_value' => 'The percentage withdrawal fee cannot exceed 100%.',
            ]);
        }

        WithdrawalSetting::query()->update([
            'fee_enabled' => (bool) $data['fee_enabled'],
            'fee_type' => $data['fee_type'],
            'fee_value' => $data['fee_value'],
            'pin_recovery_enabled' => (bool) $data['pin_recovery_enabled'],
        ]);

        SiteSetting::updateOrCreate(
            ['key' => 'customer_portal_show_referral_code'],
            ['value' => (string) (int) $data['customer_portal_show_referral_code']],
        );

        return back()->with('success', 'App settings updated.');
    }
}
