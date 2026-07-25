<?php

namespace App\Mail;

use App\Models\InstallmentSchedules;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UpcomingInstallmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public InstallmentSchedules $installment) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->dueLabel().' — '.$this->installment->booking->booking_number);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.upcoming-installment', with: ['dueLabel' => $this->dueLabel()]);
    }

    private function dueLabel(): string
    {
        $daysRemaining = (int) today()->diffInDays($this->installment->due_date, false);

        return match ($daysRemaining) {
            0 => 'Installment due today',
            1 => 'Installment due tomorrow',
            default => "Installment due in {$daysRemaining} days",
        };
    }
}
