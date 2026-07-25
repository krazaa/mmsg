<?php

namespace App\Console\Commands;

use App\Mail\UpcomingInstallmentMail;
use App\Models\InstallmentSchedules;
use App\Notifications\AccountActivityNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendUpcomingInstallmentReminders extends Command
{
    protected $signature = 'installments:send-upcoming-reminders';

    protected $description = 'Send daily reminders from 10 days before an unpaid installment through its due date';

    public function handle(): int
    {
        $sent = 0;
        $today = today();

        InstallmentSchedules::with(['booking.customer', 'booking.project', 'booking.package'])
            ->whereIn('status', ['pending', 'partial'])
            ->whereRaw('DATE(due_date) BETWEEN ? AND ?', [
                $today->toDateString(),
                $today->copy()->addDays(10)->toDateString(),
            ])
            ->where(function ($query) use ($today) {
                $query->whereNull('reminder_sent_at')
                    ->orWhereRaw('DATE(reminder_sent_at) < ?', [$today->toDateString()]);
            })
            ->whereHas('booking', fn ($query) => $query->where('status', 'active')->whereHas('customer', fn ($customer) => $customer->where('status', true)))
            ->orderBy('id')
            ->each(function (InstallmentSchedules $installment) use (&$sent, $today) {
                $daysRemaining = (int) $today->diffInDays($installment->due_date, false);
                $dueLabel = match ($daysRemaining) {
                    0 => 'Installment due today',
                    1 => 'Installment due tomorrow',
                    default => "Installment due in {$daysRemaining} days",
                };

                Mail::to($installment->booking->customer->email)->send(new UpcomingInstallmentMail($installment));
                $installment->booking->customer->notify(new AccountActivityNotification($dueLabel, 'An upcoming installment is due soon. Open your portal to review the amount and due date.', 'reminder', route('dashboard').'#payments', ['Booking' => $installment->booking->booking_number, 'Due date' => $installment->due_date->format('d M Y'), 'Amount' => 'Rs '.number_format($installment->total_due - $installment->paid_amount, 2)]));
                $installment->update(['reminder_sent_at' => now()]);
                $sent++;
            });

        $this->info("$sent upcoming installment reminder(s) sent.");

        return self::SUCCESS;
    }
}
