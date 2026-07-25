<?php

namespace App\Http\Controllers;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppSettingsController extends Controller
{
    public function index(): View
    {
        $enabled = (bool) config('services.whatsapp.enabled');
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        $hasToken = filled(config('services.whatsapp.access_token'));
        $configured = $enabled && $phoneNumberId !== '' && $hasToken;

        return view('whatsapp.index', compact('enabled', 'phoneNumberId', 'hasToken', 'configured'));
    }

    public function test(Request $request, WhatsAppChannel $channel): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $admin = $request->user();
        $admin->phone = $data['phone'];
        $template = (string) config('services.whatsapp.test_template');
        if ($template === '') {
            return back()->with('error', 'Configure WHATSAPP_TEST_TEMPLATE before sending a diagnostic message.');
        }
        $sent = $channel->sendTemplate(
            $admin,
            $template,
            (string) config('services.whatsapp.test_template_language', 'en_US')
        );

        return back()->with(
            $sent ? 'success' : 'error',
            $sent ? 'Test template accepted by Meta. Delivery may take a few moments.' : 'WhatsApp message was not accepted. Check the enabled setting, credentials, recipient allowlist, and application log.'
        );
    }
}
