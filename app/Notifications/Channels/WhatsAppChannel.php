<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppChannel
{
    public function send(object $notifiable, Notification $notification): bool
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return false;
        }

        $recipient = $this->recipientPhone($notifiable, $notification);
        if ($recipient === '') {
            return false;
        }

        $template = (string) config('services.whatsapp.notification_template');
        if ($template !== '' && method_exists($notification, 'toWhatsAppTemplateParameters')) {
            return $this->sendTemplate(
                $notifiable,
                $template,
                (string) config('services.whatsapp.notification_template_language', 'en'),
                $notification->toWhatsAppTemplateParameters($notifiable)
            );
        }

        return $this->sendPayload($notifiable, [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $notification->toWhatsApp($notifiable),
            ],
        ], $notification::class);
    }

    public function sendTemplate(object $notifiable, string $template, string $language = 'en_US', array $parameters = []): bool
    {
        $recipient = $this->recipientPhone($notifiable);
        if ($recipient === '') {
            return false;
        }

        $templatePayload = [
            'name' => $template,
            'language' => ['code' => $language],
        ];
        if ($parameters !== []) {
            $templatePayload['components'] = [[
                'type' => 'body',
                'parameters' => array_map(
                    fn (mixed $value): array => ['type' => 'text', 'text' => (string) $value],
                    $parameters
                ),
            ]];
        }

        return $this->sendPayload($notifiable, [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $recipient,
            'type' => 'template',
            'template' => $templatePayload,
        ], 'template:'.$template);
    }

    private function sendPayload(object $notifiable, array $payload, string $notification): bool
    {
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        $token = (string) config('services.whatsapp.access_token');
        if ($phoneNumberId === '' || $token === '') {
            return false;
        }

        try {
            Http::withToken($token)->acceptJson()->timeout(10)->retry(2, 250)
                ->post(rtrim((string) config('services.whatsapp.api_url'), '/').'/'.$phoneNumberId.'/messages', $payload)
                ->throw();

            return true;
        } catch (Throwable $exception) {
            Log::warning('WhatsApp notification could not be delivered.', [
                'notifiable_id' => $notifiable->getKey(),
                'notification' => $notification,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';
        if ($phone === '') {
            return '';
        }

        if (str_starts_with($phone, '00')) {
            return substr($phone, 2);
        }

        if (str_starts_with($phone, '0')) {
            return (string) config('services.whatsapp.default_country_code', '92').substr($phone, 1);
        }

        return $phone;
    }

    private function recipientPhone(object $notifiable, ?Notification $notification = null): string
    {
        $phone = (string) ($notifiable->phone ?? '');
        if ($phone === '' && method_exists($notifiable, 'routeNotificationFor')) {
            $phone = (string) $notifiable->routeNotificationFor(self::class, $notification);
        }

        return $this->normalizePhone($phone);
    }
}
