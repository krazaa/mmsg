<?php

namespace Tests\Feature;

use App\Mail\UpcomingInstallmentMail;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\InstallmentSchedules;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UpcomingInstallmentReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_is_sent_once_exactly_five_days_before_an_active_plan_installment(): void
    {
        Mail::fake();
        $customerUser = User::factory()->create(['role' => 'customer', 'status' => true]);
        $customer = Customer::findOrFail($customerUser->id);
        $project = Project::create(['name' => 'Reminder Project', 'slug' => 'reminder-project', 'location' => 'Abbottabad', 'gross_area_marla' => 1000, 'saleable_area_marla' => 1000]);
        $package = PlotPackage::create(['project_id' => $project->id, 'name' => 'Reminder Plan', 'size_marla' => 5, 'booking_amount' => 100000, 'months' => 12, 'monthly_amount' => 25000, 'month_12_balloon' => 0, 'month_24_balloon' => 0, 'month_36_balloon' => 0]);
        $booking = Booking::create(['booking_number' => 'BOOK-REMINDER', 'project_id' => $project->id, 'package_id' => $package->id, 'customer_id' => $customer->id, 'booking_date' => today(), 'total_price' => 400000, 'booking_amount' => 100000, 'financed_amount' => 300000, 'status' => 'active']);
        $due = InstallmentSchedules::create(['booking_id' => $booking->id, 'installment_number' => 1, 'due_date' => today()->addDays(5), 'regular_amount' => 25000, 'balloon_amount' => 0, 'total_due' => 25000, 'paid_amount' => 5000, 'status' => 'partial']);
        InstallmentSchedules::create(['booking_id' => $booking->id, 'installment_number' => 2, 'due_date' => today()->addDays(6), 'regular_amount' => 25000, 'balloon_amount' => 0, 'total_due' => 25000, 'status' => 'pending']);

        $this->artisan('installments:send-upcoming-reminders')
            ->expectsOutput('1 upcoming installment reminder(s) sent.')
            ->expectsOutput('0 overdue installment reminder(s) sent.')
            ->assertSuccessful();
        Mail::assertSent(UpcomingInstallmentMail::class, fn (UpcomingInstallmentMail $mail) => $mail->hasTo($customer->email)
            && $mail->installment->is($due));
        $this->assertNotNull($due->fresh()->reminder_sent_at);
        $this->assertSame('Installment due in 5 days', $customerUser->notifications()->first()->data['title']);

        $this->artisan('installments:send-upcoming-reminders')
            ->expectsOutput('0 upcoming installment reminder(s) sent.')
            ->assertSuccessful();
        Mail::assertSentCount(1);
    }

    public function test_overdue_reminder_is_sent_no_more_than_once_each_week(): void
    {
        Mail::fake();
        $customerUser = User::factory()->create(['role' => 'customer', 'status' => true]);
        $customer = Customer::findOrFail($customerUser->id);
        $project = Project::create(['name' => 'Overdue Project', 'slug' => 'overdue-project', 'location' => 'Abbottabad', 'gross_area_marla' => 1000, 'saleable_area_marla' => 1000]);
        $package = PlotPackage::create(['project_id' => $project->id, 'name' => 'Overdue Plan', 'size_marla' => 5, 'booking_amount' => 100000, 'months' => 12, 'monthly_amount' => 25000, 'month_12_balloon' => 0, 'month_24_balloon' => 0, 'month_36_balloon' => 0]);
        $booking = Booking::create(['booking_number' => 'BOOK-OVERDUE', 'project_id' => $project->id, 'package_id' => $package->id, 'customer_id' => $customer->id, 'booking_date' => today(), 'total_price' => 400000, 'booking_amount' => 100000, 'financed_amount' => 300000, 'status' => 'active']);
        $overdue = InstallmentSchedules::create(['booking_id' => $booking->id, 'installment_number' => 1, 'due_date' => today()->subDays(2), 'regular_amount' => 25000, 'balloon_amount' => 0, 'total_due' => 25000, 'status' => 'pending']);

        $this->artisan('installments:send-upcoming-reminders')
            ->expectsOutput('1 overdue installment reminder(s) sent.')
            ->assertSuccessful();
        $this->assertNotNull($overdue->fresh()->overdue_reminder_sent_at);
        $this->assertSame('Installment overdue', $customerUser->notifications()->first()->data['title']);

        $this->artisan('installments:send-upcoming-reminders')
            ->expectsOutput('0 overdue installment reminder(s) sent.')
            ->assertSuccessful();
        $this->assertDatabaseCount('notifications', 1);

        $overdue->update(['overdue_reminder_sent_at' => now()->subWeek()]);
        $this->artisan('installments:send-upcoming-reminders')
            ->expectsOutput('1 overdue installment reminder(s) sent.')
            ->assertSuccessful();
        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_reminder_is_not_sent_for_inactive_plans(): void
    {
        Mail::fake();
        $customerUser = User::factory()->create(['role' => 'customer', 'status' => true]);
        $project = Project::create(['name' => 'Pending Project', 'slug' => 'pending-project', 'location' => 'Abbottabad', 'gross_area_marla' => 1000, 'saleable_area_marla' => 1000]);
        $package = PlotPackage::create(['project_id' => $project->id, 'name' => 'Pending Plan', 'size_marla' => 5, 'booking_amount' => 100000, 'months' => 12, 'monthly_amount' => 25000, 'month_12_balloon' => 0, 'month_24_balloon' => 0, 'month_36_balloon' => 0]);
        $booking = Booking::create(['booking_number' => 'BOOK-PENDING', 'project_id' => $project->id, 'package_id' => $package->id, 'customer_id' => $customerUser->id, 'booking_date' => today(), 'total_price' => 400000, 'booking_amount' => 100000, 'financed_amount' => 300000, 'status' => 'approved']);
        InstallmentSchedules::create(['booking_id' => $booking->id, 'installment_number' => 1, 'due_date' => today()->addDays(10), 'regular_amount' => 25000, 'balloon_amount' => 0, 'total_due' => 25000, 'status' => 'pending']);

        $this->artisan('installments:send-upcoming-reminders')->assertSuccessful();
        Mail::assertNothingSent();
    }
}
