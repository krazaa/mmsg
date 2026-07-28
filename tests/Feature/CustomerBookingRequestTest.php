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
            ->assertOk()->assertSee('Book a plot')
            ->assertSee('Selected installment plan')
            ->assertSee('Continue with Installments')
            ->assertDontSee('Choose a payment plan below')
            ->assertSee('You can make the first payment after your booking is approved by the office.')
            ->assertSee('Your current amount due')->assertSee('0.00')
            ->assertDontSee('Sold Out Project')->assertDontSee('0.00 marla available');
        $this->actingAs($customerUser)->post(route('customer.bookings.store'), ['package_id' => $package->id, 'payment_plan' => 'installment'])
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
        $this->actingAs($customerUser)->post(route('customer.bookings.store'), ['package_id' => $package->id, 'payment_plan' => 'installment'])
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
        $bookingEmailHtml = (new BookingApprovedMail($booking->fresh()->load(['customer', 'project', 'package'])))->render();
        $this->assertStringContainsString('Your booking is approved', $bookingEmailHtml);
        $this->assertStringContainsString('MMS Group', $bookingEmailHtml);

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
        $this->assertStringStartsWith('BKG-PAY-', $firstPayment->receipt_number);
        Storage::disk('local')->assertExists($firstPayment->proof_path);
        $this->assertTrue($admin->notifications()->get()->pluck('data.title')->contains('Customer payment needs verification'));
        $this->actingAs($admin)->get(route('management.notifications.index'))
            ->assertOk()
            ->assertSee('Customer payment needs verification')
            ->assertSee('Plot Buyer')
            ->assertSee($firstPayment->receipt_number);
        $this->actingAs($admin)->get(route('payments.edit', $firstPayment))
            ->assertOk()
            ->assertSee('Customer file number')
            ->assertSee('name="file_no"', false);

        $this->actingAs($admin)->put(route('payments.update', $firstPayment), [
            'payment_method' => 'online_transfer',
            'transaction_reference' => 'FIRST-123',
            'status' => 'verified',
        ])->assertSessionHasErrors('file_no');

        User::factory()->create(['file_no' => 'AT-USED-001']);
        $this->actingAs($admin)->put(route('payments.update', $firstPayment), [
            'payment_method' => 'online_transfer',
            'transaction_reference' => 'FIRST-123',
            'status' => 'verified',
            'file_no' => ' at-used-001 ',
        ])->assertSessionHasErrors('file_no');
        $this->assertSame('pending', $firstPayment->refresh()->status);

        $customerUser->update(['file_no' => 'AT-EXISTING-001']);
        $this->actingAs($admin)->put(route('payments.update', $firstPayment), [
            'payment_method' => 'online_transfer',
            'transaction_reference' => 'FIRST-123',
            'status' => 'verified',
            'file_no' => 'AT-EXISTING-001',
        ])->assertSessionHasErrors('file_no');
        $this->assertSame('pending', $firstPayment->refresh()->status);

        $this->actingAs($admin)->put(route('payments.update', $firstPayment), [
            'payment_method' => 'online_transfer',
            'transaction_reference' => 'FIRST-123',
            'status' => 'verified',
            'file_no' => 'AT-MANUAL-001',
        ])->assertRedirect(route('payments.index'));
        $this->assertEquals('verified', $firstPayment->refresh()->status);
        $this->assertEquals('active', $booking->refresh()->status);
        $this->assertSame('AT-MANUAL-001', $customerUser->refresh()->file_no);
        $this->assertEquals($referrer->id, $booking->agent_id);
        $this->assertEquals(10000, (float) Commission::where('payment_id', $firstPayment->id)->value('amount'));
        $this->assertEquals(0, (float) $project->refresh()->reserved_area_marla);
        $this->assertEquals(10, (float) $project->sold_area_marla);
        Mail::assertSent(PaymentVerifiedMail::class, fn (PaymentVerifiedMail $mail) => $mail->hasTo($customer->email));
        Mail::assertSent(PlanActivatedMail::class, fn (PlanActivatedMail $mail) => $mail->hasTo($customer->email));
        $planEmailHtml = (new PlanActivatedMail($booking->fresh()->load(['customer', 'project', 'package'])))->render();
        $this->assertStringContainsString('Your payment plan is now active', $planEmailHtml);
        $this->assertStringContainsString('MMS Group', $planEmailHtml);

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

    public function test_customer_can_choose_cash_rate_without_installment_schedule(): void
    {
        Mail::fake();
        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone' => '03001234567',
            'cnic' => '33333-3333333-3',
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $project = Project::create([
            'name' => 'Cash Project',
            'slug' => 'cash-project',
            'location' => 'Abbottabad',
            'gross_area_marla' => 100,
            'saleable_area_marla' => 100,
            'status' => true,
        ]);
        $package = PlotPackage::create([
            'project_id' => $project->id,
            'name' => 'Cash 5 Marla',
            'size_marla' => 5,
            'cash_price' => 900000,
            'booking_amount' => 100000,
            'months' => 12,
            'monthly_amount' => 75000,
            'status' => true,
        ]);

        $this->actingAs($customer)->get(route('customer.bookings.create'))
            ->assertOk()
            ->assertSee('Selected cash plan')
            ->assertSee('Selected installment plan')
            ->assertSee('Choose payment plan')
            ->assertSee('Installments are selected by default')
            ->assertSee('Continue with Cash')
            ->assertSee('Continue with Installments')
            ->assertSee('Confirm cash selection')
            ->assertSee('Confirm installment selection')
            ->assertSee('Submit cash booking request?')
            ->assertSee('Submit installment booking request?')
            ->assertSee('Full cash payment')
            ->assertSee('One full payment after office approval.')
            ->assertSee('No cash payment is charged now')
            ->assertSee('Your full cash payment becomes available only after approval.')
            ->assertSee('No installments');

        $this->actingAs($customer)->post(route('customer.bookings.store'), [
            'package_id' => $package->id,
            'payment_plan' => 'cash',
        ])->assertRedirect(route('dashboard'));

        $booking = Booking::firstOrFail();
        $this->assertSame('cash', $booking->payment_plan);
        $this->assertSame(900000.0, (float) $booking->total_price);
        $this->assertSame(900000.0, (float) $booking->booking_amount);
        $this->assertSame(0.0, (float) $booking->financed_amount);

        $this->actingAs($admin)->put(route('bookings.update', $booking), [
            'name' => $customer->name,
            'cnic' => $customer->cnic,
            'phone' => $customer->phone,
            'booking_date' => $booking->booking_date->toDateString(),
            'status' => 'approved',
        ])->assertRedirect(route('bookings.show', $booking));

        $this->assertCount(0, $booking->refresh()->installments);
    }

    public function test_cash_only_package_is_preselected_and_rejects_installments(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);
        $project = Project::create([
            'name' => 'Cash Only Project',
            'slug' => 'cash-only-project',
            'gross_area_marla' => 100,
            'saleable_area_marla' => 100,
            'status' => true,
        ]);
        $package = PlotPackage::create([
            'project_id' => $project->id,
            'name' => 'Cash Only Package',
            'size_marla' => 5,
            'cash_price' => 800000,
            'payment_plan_options' => 'cash',
            'booking_amount' => 100000,
            'months' => 12,
            'monthly_amount' => 75000,
            'status' => true,
        ]);

        $this->actingAs($customer)->get(route('customer.bookings.create'))
            ->assertOk()
            ->assertSee('Cash Only')
            ->assertSee('Installments not available')
            ->assertDontSee('Choose a payment plan below');

        $this->actingAs($customer)->post(route('customer.bookings.store'), [
            'package_id' => $package->id,
            'payment_plan' => 'installment',
        ])->assertSessionHasErrors('payment_plan');

        $this->actingAs($customer)->post(route('customer.bookings.store'), [
            'package_id' => $package->id,
            'payment_plan' => 'cash',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame('cash', Booking::firstOrFail()->payment_plan);
    }
}
