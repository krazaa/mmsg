<?php

namespace Tests\Feature;

use App\Mail\PaymentVerifiedMail;
use App\Mail\PlanActivatedMail;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Customer;
use App\Models\InstallmentSchedules;
use App\Models\Payment;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerPaymentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_uploads_proof_and_admin_verifies_payment(): void
    {
        Storage::fake('local');
        Mail::fake();
        config()->set('services.whatsapp', [
            'enabled' => true,
            'api_url' => 'https://graph.facebook.com/v23.0',
            'phone_number_id' => '123456789',
            'access_token' => 'test-token',
            'default_country_code' => '92',
        ]);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test']]])]);
        SiteSetting::create(['key' => 'whatsapp_owner_numbers', 'value' => '+923001112233']);
        [$customerUser, $admin, $booking, $installment] = $this->records();

        $this->actingAs($customerUser)->post(route('customer.payments.store'), [
            'booking_id' => $booking->id,
            'payment_type' => 'installment',
            'installment_id' => $installment->id,
            'amount' => 25000,
            'payment_method' => 'online_transfer',
            'proof' => UploadedFile::fake()->create('large-proof.pdf', 301, 'application/pdf'),
        ])->assertSessionHasErrors('proof');

        $this->actingAs($customerUser)->post(route('customer.payments.store'), [
            'booking_id' => $booking->id,
            'payment_type' => 'installment',
            'installment_id' => $installment->id,
            'amount' => 10000,
            'payment_method' => 'online_transfer',
            'proof' => UploadedFile::fake()->image('partial.png'),
        ])->assertSessionHasErrors('amount');

        $this->actingAs($customerUser)->post(route('customer.payments.store'), [
            'booking_id' => $booking->id,
            'payment_type' => 'installment',
            'installment_id' => $installment->id,
            'amount' => 25000,
            'payment_method' => 'online_transfer',
            'transaction_reference' => 'BANK-123',
            'proof' => UploadedFile::fake()->image('transfer.png'),
        ])->assertRedirect()->assertSessionHas('success');

        $payment = Payment::where('status', 'pending')->firstOrFail();
        Http::assertSent(fn ($request) => $request['to'] === '923001112233'
            && $request['type'] === 'text'
            && str_contains($request['text']['body'], 'Installment payment received')
            && str_contains($request['text']['body'], $payment->receipt_number));
        Storage::disk('local')->assertExists($payment->proof_path);
        $this->assertEquals(0, (float) $installment->refresh()->paid_amount);
        $this->assertDatabaseCount('commissions', 0);
        $this->actingAs($customerUser)->get(route('dashboard'))
            ->assertOk()->assertSee('Under review')->assertDontSee('Pay installment');

        $this->actingAs($admin)->get(route('payments.edit', $payment))
            ->assertOk()->assertSee('Customer payment proof')->assertSee('payment-proof-preview')
            ->assertSee('Amount submitted')->assertSee('Save decision');
        $this->actingAs($admin)->put(route('payments.update', $payment), [
            'payment_method' => 'online_transfer',
            'transaction_reference' => 'BANK-123',
            'status' => 'verified',
        ])->assertRedirect(route('payments.index'));

        $this->assertEquals('verified', $payment->refresh()->status);
        $this->assertEquals(25000, (float) $installment->refresh()->paid_amount);
        $this->assertEquals('paid', $installment->status);
        $this->assertEquals(1250, (float) Commission::where('payment_id', $payment->id)->value('amount'));
        Mail::assertSent(PaymentVerifiedMail::class, fn (PaymentVerifiedMail $mail) => $mail->hasTo($customerUser->email));
        Mail::assertNotSent(PlanActivatedMail::class);
    }

    private function records(): array
    {
        $customerUser = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $project = Project::create(['name' => 'Test', 'slug' => 'payment-test', 'location' => 'Test', 'gross_area_marla' => 1000, 'saleable_area_marla' => 1000]);
        $package = PlotPackage::create(['project_id' => $project->id, 'name' => 'Plan', 'size_marla' => 5, 'booking_amount' => 100000, 'months' => 12, 'monthly_amount' => 25000, 'month_12_balloon' => 0, 'month_24_balloon' => 0, 'month_36_balloon' => 0]);
        CommissionRule::create(['package_id' => $package->id, 'level' => 1, 'percentage' => 5, 'status' => true]);
        $agent = User::factory()->create(['role' => 'customer', 'status' => true]);
        $customerUser->update(['role' => 'customer', 'name' => 'Customer', 'cnic' => '11111-1111111-1', 'phone' => '0300', 'referral_code' => 'REF-PAY']);
        $customer = Customer::findOrFail($customerUser->id);
        $booking = Booking::create(['booking_number' => 'BOOK-PAY', 'project_id' => $project->id, 'package_id' => $package->id, 'customer_id' => $customer->id, 'agent_id' => $agent->id, 'booking_date' => today(), 'total_price' => 400000, 'booking_amount' => 100000, 'financed_amount' => 300000, 'status' => 'active']);
        Payment::create(['receipt_number' => 'RC-FIRST', 'booking_id' => $booking->id, 'customer_id' => $customer->id, 'amount' => 100000, 'payment_method' => 'cash', 'payment_date' => today(), 'status' => 'verified']);
        $installment = InstallmentSchedules::create(['booking_id' => $booking->id, 'installment_number' => 1, 'due_date' => today(), 'regular_amount' => 25000, 'balloon_amount' => 0, 'total_due' => 25000]);

        return [$customerUser, $admin, $booking, $installment];
    }
}
