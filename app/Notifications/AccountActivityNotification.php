<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $category,
        public string $url,
        public array $details = [],
        public bool $sendWhatsApp = true,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->sendWhatsApp && config('services.whatsapp.enabled') && filled($notifiable->phone ?? null)) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    public function toWhatsApp(object $notifiable): string
    {
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
            $this->message,
            $details ?: 'Open your customer portal for details.',
            $this->url,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message, 'category' => $this->category, 'url' => $this->url, 'details' => $this->details];
    }
}
