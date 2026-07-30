<?php

namespace App\Support;

use App\Models\SiteSetting;

class WhatsAppMessageTemplates
{
    public static function defaults(): array
    {
        return [
            'customer_booking_approved' => "*Plot approved*\nHello {customer}, your booking {booking} for {project} has been approved. You can now submit your first payment.\n{url}",
            'customer_first_payment_verified' => "*First payment verified*\nHello {customer}, your first payment of {amount} has been verified.\nReceipt: {receipt}\n{url}",
            'customer_installment_verified' => "*Installment payment verified*\nHello {customer}, your installment payment of {amount} has been verified.\nReceipt: {receipt}\n{url}",
            'customer_upcoming_installment' => "*Installment due in 5 days*\nHello {customer}, your installment for booking {booking} is due on {due_date}.\nAmount: {amount}\n{url}",
            'customer_overdue_installment' => "*Installment overdue*\nHello {customer}, your installment for booking {booking} is overdue by {days_overdue} day(s).\nAmount: {amount}\n{url}",
            'owner_first_payment_received' => "*First payment received*\n{customer} submitted first-payment proof.\nBooking: {booking}\nProject: {project}\nAmount: {amount}\nReceipt: {receipt}\n{url}",
            'owner_installment_received' => "*Installment payment received*\n{customer} submitted installment-payment proof.\nBooking: {booking}\nProject: {project}\nAmount: {amount}\nReceipt: {receipt}\n{url}",
        ];
    }

    public static function all(): array
    {
        return collect(static::defaults())
            ->mapWithKeys(fn (string $default, string $key) => [$key => SiteSetting::valueFor('whatsapp_template_'.$key, $default)])
            ->all();
    }

    public static function render(string $key, array $values): string
    {
        $template = (string) (static::all()[$key] ?? '');
        $replacements = collect($values)
            ->mapWithKeys(fn (mixed $value, string $name) => ['{'.$name.'}' => (string) $value])
            ->all();

        return strtr($template, $replacements);
    }
}
