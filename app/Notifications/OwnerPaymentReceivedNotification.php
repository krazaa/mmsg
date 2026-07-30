<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Notifications\Channels\WhatsAppChannel;
use App\Support\WhatsAppMessageTemplates;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OwnerPaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(public int $paymentId) {}

    public function via(object $notifiable): array
    {
        return config('services.whatsapp.enabled') ? [WhatsAppChannel::class] : [];
    }

    public function viaConnections(): array
    {
        return [WhatsAppChannel::class => config('queue.default')];
    }

    public function toWhatsApp(object $notifiable): string
    {
        $payment = $this->payment();

        return WhatsAppMessageTemplates::render(
            $payment->installment_schedule_id ? 'owner_installment_received' : 'owner_first_payment_received',
            $this->templateValues($payment)
        );
    }

    public function toWhatsAppTemplateParameters(object $notifiable): array
    {
        $payment = $this->payment();
        $type = $payment->installment_schedule_id ? 'Installment payment received' : 'First payment received';
        $message = WhatsAppMessageTemplates::render(
            $payment->installment_schedule_id ? 'owner_installment_received' : 'owner_first_payment_received',
            $this->templateValues($payment)
        );

        return [
            'Owner',
            $type,
            $message,
            'Booking: '.$payment->booking->booking_number.', Amount: Rs '.number_format($payment->amount, 2).', Receipt: '.$payment->receipt_number,
            route('payments.edit', $payment),
        ];
    }

    private function payment(): Payment
    {
        return Payment::with(['customer', 'booking.project'])->findOrFail($this->paymentId);
    }

    private function templateValues(Payment $payment): array
    {
        return [
            'customer' => $payment->customer->name,
            'booking' => $payment->booking->booking_number,
            'project' => $payment->booking->project->name,
            'amount' => 'Rs '.number_format($payment->amount, 2),
            'receipt' => $payment->receipt_number,
            'url' => route('payments.edit', $payment),
        ];
    }
}
