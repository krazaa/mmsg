<?php

namespace Tests\Feature;

use App\Mail\BookingApprovedMail;
use App\Mail\PaymentVerifiedMail;
use App\Mail\PlanActivatedMail;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerBookingRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_requests_plot_and_admin_activates_reserved_booking(): void
    {
        Storage::fake('local');
        Mail::fake();
        $customerUser = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $referrer = User::factory()->create(['role' => 'customer', 'referral_code' => 'REF-SPONSOR', 'status' => true]);
        $customerUser->update(['role' => 'customer', 'name' => 'Plot Buyer', 'cnic' => '22222-2222222-2', 'phone' => '0300', 'referral_code' => 'REF-BOOK', 'referral_agent_id' => $referrer->id]);
        $customer = Customer::findOrFail($customerUser->id);
        $project = Project::create(['name' => 'New Project', 'slug' => 'new-project', 'location' => 'Abbottabad', 'gross_area_marla' => 1000, 'saleable_area_marla' => 1000, 'status' => true]);
        $package = PlotPackage::create(['project_id' => $project->id, 'name' => '10 Marla', 'size_marla' => 10, 'booking_amount' => 200000, 'months' => 12, 'monthly_amount' => 50000, 'month_12_balloon' => 0, 'month_24_balloon' => 0, 'month_36_balloon' => 0, 'status' => true]);
        CommissionRule::create(['package_id' => $package->id, 'level' => 1, 'percentage' => 5, 'status' => true]);
        $fullProject = Project::create(['name' => 'Sold Out Project', 'slug' => 'sold-out-project', 'location' => 'Abbottabad', 'gross_area_marla' => 100, 'saleable_area_marla' => 100, 'sold_area_marla' => 100, 'status' => true]);
        PlotPackage::create(['project_id' => $fullProject->id, 'name' => 'Sold Out Plan', 'size_marla' => 5, 'booking_amount' => 100000, 'months' => 12, 'monthly_amount' => 10000, 'month_12_balloon' => 0, 'month_24_balloon' => 0, 'month_36_balloon' => 0, 'status' => true]);

        $this->actingAs($customerUser)->get(route('customer.bookings.create'))
            ->assertOk()->assertSee('Book a plot')->assertSee('Request 10 Marla')
            ->assertSee('You can make the first payment after your booking is approved by the office.')
            ->assertSee('Your current amount due')->assertSee('0.00')
            ->assertDontSee('Sold Out Project')->assertDontSee('0.00 marla available');
        $this->actingAs($customerUser)->post(route('customer.bookings.store'), ['package_id' => $package->id])
            ->assertRedirect(route('dashboard'))->assertSessionHas('success');

        $booking = Booking::firstOrFail();
        $this->assertEquals('pending', $booking->status);
        $this->assertEquals(10, (float) $project->refresh()->reserved_area_marla);
        $this->assertEquals(0, (float) $project->sold_area_marla);
        $this->assertCount(0, $booking->installments);
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame('Booking request submitted', $customerUser->notifications()->first()->data['title']);
        $this->assertSame('New booking requires approval', $admin->notifications()->first()->data['title']);
        $this->assertSame(route('bookings.manage', $booking), $admin->notifications()->first()->data['url']);
        $this->actingAs($customerUser)->get(route('customer.bookings.create'))
            ->assertOk()
            ->assertSee('is awaiting office approval')
            ->assertSee('Approval pending');
        $this->actingAs($customerUser)->post(route('customer.bookings.store'), ['package_id' => $package->id])
            ->assertSessionHasErrors('package_id');
        $this->assertDatabaseCount('bookings', 1);
        $this->assertEquals(10, (float) $project->refresh()->reserved_area_marla);
        $this->actingAs($admin)->get(route('management.notifications.index'))
            ->assertOk()
            ->assertSee('New booking requires approval')
            ->assertSee($booking->booking_number)
            ->assertSee('Plot Buyer');
        $this->actingAs($customerUser)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Account notifications')
            ->assertSee('Booking request submitted');

        $this->actingAs($admin)->put(route('bookings.update', $booking), [
            'name' => $customer->name,
            'cnic' => $customer->cnic,
            'phone' => $customer->phone,
            'booking_date' => $booking->booking_date->toDateString(),
            'status' => 'approved',
        ])->assertRedirect(route('bookings.show', $booking));

        $this->assertEquals('approved', $booking->refresh()->status);
        $this->assertEquals(10, (float) $project->refresh()->reserved_area_marla);
        $this->assertEquals(0, (float) $project->sold_area_marla);
        $this->assertCount(12, $booking->refresh()->installments);
        Mail::assertSent(BookingApprovedMail::class, fn (BookingApprovedMail $mail) => $mail->hasTo($customer->email));

        $this->actingAs($customerUser)->get(route('dashboard'))
            ->assertOk()->assertSee('Pay your first payment')->assertSee('200,000.00');
        $this->actingAs($customerUser)->post(route('customer.payments.store'), [
            'booking_id' => $booking->id,
            'payment_type' => 'booking',
            'payment_method' => 'online_transfer',
            'transaction_reference' => 'FIRST-123',
            'proof' => UploadedFile::fake()->image('first-payment.png'),
        ])->assertRedirect()->assertSessionHas('success');
        $firstPayment = Payment::whereNull('installment_schedule_id')->firstOrFail();
        $this->assertEquals('pending', $firstPayment->status);
        $this->assertEquals(200000, (float) $firstPayment->amount);
        $this->assertStringStartsWith('BKG-1ST-PAY-', $firstPayment->receipt_number);
        Storage::disk('local')->assertExists($firstPayment->proof_path);
        $this->assertTrue($admin->notifications()->get()->pluck('data.title')->contains('Customer payment needs verification'));
        $this->actingAs($admin)->get(route('management.notifications.index'))
            ->assertOk()
            ->assertSee('Customer payment needs verification')
            ->assertSee('Plot Buyer')
            ->assertSee($firstPayment->receipt_number);

        $this->actingAs($admin)->put(route('payments.update', $firstPayment), [
            'payment_method' => 'online_transfer',
            'transaction_reference' => 'FIRST-123',
            'status' => 'verified',
        ])->assertRedirect(route('payments.index'));
        $this->assertEquals('verified', $firstPayment->refresh()->status);
        $this->assertEquals('active', $booking->refresh()->status);
        $this->assertEquals($referrer->id, $booking->agent_id);
        $this->assertEquals(10000, (float) Commission::where('payment_id', $firstPayment->id)->value('amount'));
        $this->assertEquals(0, (float) $project->refresh()->reserved_area_marla);
        $this->assertEquals(10, (float) $project->sold_area_marla);
        Mail::assertSent(PaymentVerifiedMail::class, fn (PaymentVerifiedMail $mail) => $mail->hasTo($customer->email));
        Mail::assertSent(PlanActivatedMail::class, fn (PlanActivatedMail $mail) => $mail->hasTo($customer->email));

        $titles = $customerUser->notifications()->get()->pluck('data.title');
        $this->assertTrue($titles->contains('Booking request submitted'));
        $this->assertTrue($titles->contains('Booking approved'));
        $this->assertTrue($titles->contains('Payment submitted for verification'));
        $this->assertTrue($titles->contains('Payment verified'));
        $this->assertTrue($titles->contains('Property plan activated'));
        $this->actingAs($customerUser)->get(route('customer.notifications.index'))
            ->assertOk()
            ->assertSee('Booking approved')
            ->assertSee('Payment verified');
        $this->actingAs($customerUser)->post(route('customer.notifications.read-all'))->assertRedirect();
        $this->assertSame(0, $customerUser->unreadNotifications()->count());
    }
}
