<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerAnnouncementController extends Controller
{
    public function edit(): View
    {
        return view('customer-announcement.edit', [
            'announcement' => SiteSetting::customerPortalAnnouncement(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'title' => ['nullable', 'required_if:enabled,1', 'string', 'max:100'],
            'message' => ['nullable', 'required_if:enabled,1', 'string', 'max:1000'],
        ]);

        foreach ([
            'customer_announcement_enabled' => (string) (int) $request->boolean('enabled'),
            'customer_announcement_title' => $data['title'] ?: 'Important announcement',
            'customer_announcement_message' => $data['message'] ?? '',
            'customer_announcement_version' => (string) now()->getTimestampMs(),
        ] as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Customer announcement updated.');
    }
}
