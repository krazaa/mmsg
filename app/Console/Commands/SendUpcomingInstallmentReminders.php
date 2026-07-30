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

    protected $description = 'Send installment reminders five days before the due date and weekly while overdue';

    public function handle(): int
    {
        $upcomingSent = 0;
        $overdueSent = 0;
        $today = today();

        InstallmentSchedules::with(['booking.customer', 'booking.project', 'booking.package'])
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('due_date', $today->copy()->addDays(5))
            ->whereNull('reminder_sent_at')
            ->whereHas('booking', fn ($query) => $query->where('status', 'active')->whereHas('customer', fn ($customer) => $customer->where('status', true)))
            ->orderBy('id')
            ->each(function (InstallmentSchedules $installment) use (&$upcomingSent) {
                Mail::to($installment->booking->customer->email)->send(new UpcomingInstallmentMail($installment));
                $installment->booking->customer->notify(new AccountActivityNotification('Installment due in 5 days', 'Your installment is due in five days. Open your portal to review the amount and due date.', 'reminder', route('dashboard').'#payments', ['Booking' => $installment->booking->booking_number, 'Due date' => $installment->due_date->format('d M Y'), 'Amount' => 'Rs '.number_format($installment->total_due - $installment->paid_amount, 2)], true, 'customer_upcoming_installment'));
                $installment->update(['reminder_sent_at' => now()]);
                $upcomingSent++;
            });

        InstallmentSchedules::with(['booking.customer', 'booking.project', 'booking.package'])
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('due_date', '<', $today)
            ->where(function ($query) {
                $query->whereNull('overdue_reminder_sent_at')
                    ->orWhere('overdue_reminder_sent_at', '<=', now()->subWeek());
            })
            ->whereHas('booking', fn ($query) => $query->where('status', 'active')->whereHas('customer', fn ($customer) => $customer->where('status', true)))
            ->orderBy('id')
            ->each(function (InstallmentSchedules $installment) use (&$overdueSent, $today) {
                $daysOverdue = (int) $installment->due_date->diffInDays($today);
                $installment->booking->customer->notify(new AccountActivityNotification('Installment overdue', 'Your installment is overdue. Please submit payment or contact the office for assistance.', 'reminder', route('dashboard').'#payments', ['Booking' => $installment->booking->booking_number, 'Due date' => $installment->due_date->format('d M Y'), 'Days overdue' => $daysOverdue, 'Amount' => 'Rs '.number_format($installment->total_due - $installment->paid_amount, 2)], true, 'customer_overdue_installment'));
                $installment->update(['overdue_reminder_sent_at' => now()]);
                $overdueSent++;
            });

        $this->info("$upcomingSent upcoming installment reminder(s) sent.");
        $this->info("$overdueSent overdue installment reminder(s) sent.");

        return self::SUCCESS;
    }
}
