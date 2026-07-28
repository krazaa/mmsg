<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalPinResetNotification extends Notification
{
    use Queueable;

    public function __construct(public string $temporaryPin) {}

    public function via(object $notifiable): array
    {
        $channels = filled($notifiable->email ?? null) ? ['mail'] : [];

        if (config('services.whatsapp.enabled') && filled($notifiable->phone ?? null)) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your temporary withdrawal PIN')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new temporary withdrawal PIN was requested for your customer account.')
            ->line('Temporary PIN: '.$this->temporaryPin)
            ->line('Use this PIN for your next withdrawal, then change it from Profile & Security.')
            ->line('If you did not request this PIN, change your account password and contact the office immediately.')
            ->action('Open Profile & Security', route('profile.edit'));
    }

    public function toWhatsApp(object $notifiable): string
    {
        return collect([
            '*Temporary withdrawal PIN*',
            'Hello '.$notifiable->name.', your new temporary withdrawal PIN is *'.$this->temporaryPin.'*.',
            'Use it for your next withdrawal, then change it from Profile & Security.',
            'If you did not request this, contact the office immediately.',
            route('profile.edit'),
        ])->implode("\n\n");
    }

    public function toWhatsAppTemplateParameters(object $notifiable): array
    {
        return [
            $notifiable->name ?? 'Customer',
            'Temporary withdrawal PIN',
            'Your new temporary withdrawal PIN is '.$this->temporaryPin.'.',
            'Change it from Profile & Security after use.',
            route('profile.edit'),
        ];
    }
}
