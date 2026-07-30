<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Notifications\Channels\WhatsAppChannel;
use App\Support\WhatsAppMessageTemplates;
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
        $ownerNumbers = implode("\n", SiteSetting::ownerWhatsAppNumbers());
        $templates = WhatsAppMessageTemplates::all();

        return view('whatsapp.index', compact('enabled', 'phoneNumberId', 'hasToken', 'configured', 'ownerNumbers', 'templates'));
    }

    public function updateOwners(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'owner_numbers' => ['nullable', 'string', 'max:2000'],
        ]);

        $numbers = collect(preg_split('/[\s,;]+/', (string) ($data['owner_numbers'] ?? '')))
            ->map(fn (?string $number) => preg_replace('/[^\d+]/', '', trim((string) $number)))
            ->filter()
            ->unique()
            ->values();

        if ($numbers->contains(fn (string $number) => ! preg_match('/^\+?\d{10,15}$/', $number))) {
            return back()->withErrors(['owner_numbers' => 'Enter valid WhatsApp numbers with 10 to 15 digits.'])->withInput();
        }

        SiteSetting::updateOrCreate(
            ['key' => 'whatsapp_owner_numbers'],
            ['value' => $numbers->implode("\n")],
        );

        return back()->with('success', 'Owner WhatsApp numbers updated.');
    }

    public function updateTemplates(Request $request): RedirectResponse
    {
        $rules = collect(WhatsAppMessageTemplates::defaults())
            ->mapWithKeys(fn (string $default, string $key) => ["templates.$key" => ['required', 'string', 'max:2000']])
            ->all();
        $data = $request->validate(['templates' => ['required', 'array'], ...$rules]);

        foreach (array_keys(WhatsAppMessageTemplates::defaults()) as $key) {
            SiteSetting::updateOrCreate(
                ['key' => 'whatsapp_template_'.$key],
                ['value' => trim($data['templates'][$key])],
            );
        }

        return back()->with('success', 'WhatsApp notification templates updated.');
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
