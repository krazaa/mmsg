<?php

namespace App\Mail;

use App\Models\EmailCampaignRecipient;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class BulkCampaignMail extends Mailable
{
    public function __construct(public EmailCampaignRecipient $recipient) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->recipient->campaign->subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.bulk-campaign');
    }

    public function attachments(): array
    {
        $campaign = $this->recipient->campaign;

        return $campaign->attachment_path
            ? [Attachment::fromStorageDisk('local', $campaign->attachment_path)->as($campaign->attachment_name)]
            : [];
    }
}
