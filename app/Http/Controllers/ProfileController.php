<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\SiteSetting;
use App\Models\WithdrawalSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $request->user()->loadMissing(['customer.referralAgent', 'passkeys']);

        return view('profile.edit', [
            'user' => $request->user(),
            'showReferralCode' => SiteSetting::showReferralCodesOnCustomerPortal(),
            'pinRecoveryEnabled' => WithdrawalSetting::pinRecoveryEnabled(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateWithdrawalPin(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'withdrawal_pin' => ['required', 'string', 'regex:/^\d{4,6}$/', 'confirmed'],
        ], [
            'withdrawal_pin.regex' => 'The withdrawal PIN must contain 4 to 6 digits.',
        ]);

        $request->user()->update([
            'withdrawal_pin' => $data['withdrawal_pin'],
            'withdrawal_pin_failed_attempts' => 0,
            'withdrawal_pin_locked_until' => null,
        ]);

        return Redirect::route('profile.edit')->with('success', 'Withdrawal PIN updated securely.');
    }

    public function updateNotificationPreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email_notifications_enabled' => ['required', 'boolean'],
            'whatsapp_notifications_enabled' => ['required', 'boolean'],
        ]);

        $request->user()->update($data);

        return Redirect::route('profile.edit')->with('success', 'Notification preferences updated.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
