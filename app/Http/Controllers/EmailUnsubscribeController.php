<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaignRecipient;
use App\Models\EmailUnsubscribe;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailUnsubscribeController extends Controller
{
    public function show(string $token): View
    {
        $recipient = EmailCampaignRecipient::where('unsubscribe_token', $token)->firstOrFail();

        return view('email-campaigns.unsubscribe', compact('recipient'));
    }

    public function store(string $token): RedirectResponse
    {
        $recipient = EmailCampaignRecipient::where('unsubscribe_token', $token)->firstOrFail();
        EmailUnsubscribe::updateOrCreate(
            ['email' => strtolower($recipient->email)],
            ['unsubscribed_at' => now(), 'source' => 'campaign']
        );

        return back()->with('success', 'You have been unsubscribed from promotional emails.');
    }
}
