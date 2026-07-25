<?php

namespace App\Jobs;

use App\Mail\BulkCampaignMail;
use App\Models\EmailCampaignRecipient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEmailCampaignRecipient implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public int $recipientId) {}

    public function handle(): void
    {
        $recipient = EmailCampaignRecipient::with('campaign')->find($this->recipientId);
        if (! $recipient || $recipient->status === 'sent') {
            return;
        }

        $recipient->update(['status' => 'sending', 'failure_reason' => null]);
        Mail::to($recipient->email, $recipient->name)->send(new BulkCampaignMail($recipient));
        $recipient->update(['status' => 'sent', 'sent_at' => now(), 'failed_at' => null]);
        $this->finishCampaign($recipient);
    }

    public function failed(?Throwable $exception): void
    {
        $recipient = EmailCampaignRecipient::find($this->recipientId);
        if (! $recipient) {
            return;
        }

        $recipient->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => str($exception?->getMessage() ?? 'Unknown delivery error')->limit(1000),
        ]);
        $this->finishCampaign($recipient);
    }

    private function finishCampaign(EmailCampaignRecipient $recipient): void
    {
        $campaign = $recipient->campaign;
        if (! $campaign->recipients()->whereIn('status', ['queued', 'dispatched', 'sending'])->exists()) {
            $campaign->update(['status' => 'completed', 'completed_at' => now()]);
        } elseif ($campaign->status === 'queued') {
            $campaign->update(['status' => 'sending']);
        }
    }
}
