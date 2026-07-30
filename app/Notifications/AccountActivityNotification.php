<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use App\Support\WhatsAppMessageTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public array $backoff = [10, 30, 60];

    public function __construct(
        public string $title,
        public string $message,
        public string $category,
        public string $url,
        public array $details = [],
        public bool $sendWhatsApp = true,
        public ?string $whatsAppTemplateKey = null,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (($notifiable->email_notifications_enabled ?? true) && filled($notifiable->email ?? null)) {
            $channels[] = 'mail';
        }

        if ($this->sendWhatsApp
            && ($notifiable->whatsapp_notifications_enabled ?? true)
            && config('services.whatsapp.enabled')
            && filled($notifiable->phone ?? null)) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    /**
     * Store portal alerts immediately while external delivery stays queued.
     *
     * @return array<string, string>
     */
    public function viaConnections(): array
    {
        return [
            'database' => 'sync',
            'mail' => config('queue.default'),
            WhatsAppChannel::class => config('queue.default'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $accent = match ($this->category) {
            'payment' => '#047857',
            'booking' => '#1d4ed8',
            'withdrawal' => '#b45309',
            'security' => '#be123c',
            default => '#172554',
        };

        return (new MailMessage)
            ->subject($this->title)
            ->view('emails.account-activity', [
                'name' => $notifiable->name ?? 'Customer',
                'title' => $this->title,
                'notificationMessage' => $this->message,
                'category' => ucfirst($this->category),
                'details' => $this->details,
                'actionUrl' => $this->url,
                'accent' => $accent,
            ]);
    }

    public function toWhatsApp(object $notifiable): string
    {
        if ($this->whatsAppTemplateKey) {
            return WhatsAppMessageTemplates::render($this->whatsAppTemplateKey, $this->templateValues($notifiable));
        }

        $details = collect($this->details)
            ->map(fn (mixed $value, string $label): string => $label.': '.$value)
            ->implode("\n");

        return collect([
            '*'.$this->title.'*',
            $this->message,
            $details ?: null,
            $this->url,
        ])->filter()->implode("\n\n");
    }

    public function toWhatsAppTemplateParameters(object $notifiable): array
    {
        $details = collect($this->details)
            ->map(fn (mixed $value, string $label): string => $label.': '.$value)
            ->implode(', ');

        return [
            $notifiable->name ?? 'Customer',
            $this->title,
            $this->whatsAppTemplateKey
                ? WhatsAppMessageTemplates::render($this->whatsAppTemplateKey, $this->templateValues($notifiable))
                : $this->message,
            $details ?: 'Open your customer portal for details.',
            $this->url,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message, 'category' => $this->category, 'url' => $this->url, 'details' => $this->details];
    }

    private function templateValues(object $notifiable): array
    {
        return [
            'customer' => $notifiable->name ?? 'Customer',
            'booking' => $this->details['Booking'] ?? '',
            'project' => $this->details['Project'] ?? '',
            'amount' => $this->details['Amount'] ?? '',
            'receipt' => $this->details['Receipt'] ?? '',
            'due_date' => $this->details['Due date'] ?? '',
            'days_overdue' => $this->details['Days overdue'] ?? '',
            'url' => $this->url,
        ];
    }
}
