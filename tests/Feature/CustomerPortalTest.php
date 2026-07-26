<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\InstallmentSchedules;
use App\Models\Payment;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_login_sees_only_their_property_installments_and_payments(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);
        $project = Project::create(['name' => 'Customer Project', 'slug' => 'customer-project', 'location' => 'Abbottabad', 'gross_area_marla' => 1000, 'saleable_area_marla' => 1000]);
        $package = PlotPackage::create(['project_id' => $project->id, 'name' => 'Flexible Package', 'size_marla' => 5, 'booking_amount' => 100000, 'months' => 24, 'monthly_amount' => 25000, 'month_12_balloon' => 50000, 'month_24_balloon' => 100000, 'month_36_balloon' => 0]);
        $user->update(['role' => 'customer', 'cnic' => '12345-1234567-9', 'phone' => '0300', 'referral_code' => 'REF-PORTAL']);
        $customer = Customer::findOrFail($user->id);
        $booking = Booking::create(['booking_number' => 'BOOK-PORTAL', 'project_id' => $project->id, 'package_id' => $package->id, 'customer_id' => $customer->id, 'booking_date' => today(), 'total_price' => 750000, 'booking_amount' => 100000, 'financed_amount' => 650000, 'status' => 'active']);
        $installment = InstallmentSchedules::create(['booking_id' => $booking->id, 'installment_number' => 1, 'due_date' => today(), 'regular_amount' => 25000, 'balloon_amount' => 0, 'total_due' => 25000]);
        InstallmentSchedules::create(['booking_id' => $booking->id, 'installment_number' => 2, 'due_date' => today()->addMonth(), 'regular_amount' => 77777, 'balloon_amount' => 0, 'total_due' => 77777]);
        Payment::create(['receipt_number' => 'RC-FIRST-PORTAL', 'booking_id' => $booking->id, 'customer_id' => $customer->id, 'amount' => 100000, 'payment_method' => 'cash', 'payment_date' => today(), 'status' => 'verified']);
        $payment = Payment::create(['receipt_number' => 'RC-PORTAL', 'booking_id' => $booking->id, 'customer_id' => $customer->id, 'installment_schedule_id' => $installment->id, 'amount' => 10000, 'payment_method' => 'cash', 'transaction_reference' => 'BANK-PORTAL', 'payment_date' => today(), 'status' => 'verified', 'verified_at' => now()]);
        Commission::create(['payment_id' => $payment->id, 'booking_id' => $booking->id, 'beneficiary_id' => $customer->id, 'level' => 1, 'percentage' => 5, 'amount' => 500, 'status' => 'earned']);
        $levelOne = User::factory()->create(['name' => 'Level One Member', 'role' => 'customer', 'phone' => '0303', 'referral_code' => 'REF-LEVEL-1']);
        $levelTwo = User::factory()->create(['name' => 'Level Two Member', 'role' => 'customer', 'phone' => '0304', 'referral_code' => 'REF-LEVEL-2']);
        Referral::create(['user_id' => $levelOne->id, 'sponsor_id' => $user->id]);
        Referral::create(['user_id' => $levelTwo->id, 'sponsor_id' => $levelOne->id]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My property account')
            ->assertSee('Customer Project')
            ->assertSee('Flexible Package')
            ->assertSee('Due now')
            ->assertSee('Booking number')
            ->assertSee('BOOK-PORTAL')
            ->assertSee('Installment schedule')
            ->assertSee('Pay installment')
            ->assertSee('The exact remaining balance is required.')
            ->assertSee('Payment history')
            ->assertSee('REF-PORTAL')
            ->assertSee('href="'.route('dashboard').'#payments"', false)
            ->assertSee(route('customer.payments.receipt', $payment))
            ->assertSee('RC-PORTAL')
            ->assertSee('25,000.00')
            ->assertDontSee('77,777.00')
            ->assertDontSee('Manage projects');

        $this->actingAs($user)->get(route('customer.installments'))
            ->assertOk()
            ->assertSee('Plot installment schedules')
            ->assertSee('BOOK-PORTAL')
            ->assertSee('RC-FIRST-PORTAL')
            ->assertSee('First payment receipt')
            ->assertSee('25,000.00')
            ->assertSee('77,777.00')
            ->assertSee('Upcoming');

        $this->actingAs($user)->get(route('customer.team'))
            ->assertOk()
            ->assertSee('My team')
            ->assertSee('My referral network')
            ->assertSee('Your referral network across three levels')
            ->assertSee('Level One Member')
            ->assertSee('Level Two Member')
            ->assertSee('Generation 2')
            ->assertSee('Copy code')
            ->assertSee('REF-PORTAL')
            ->assertDontSee('All transactions');

        $this->actingAs($user)->get(route('customer.commissions'))
            ->assertOk()
            ->assertSee('Commission history')
            ->assertSee('All transactions')
            ->assertSee('Search team or reference')
            ->assertSee('Customer Project')
            ->assertSee('500.00')
            ->assertSee('Level 1');

        $this->actingAs($user)->get(route('customer.commissions', ['search' => 'REF-PORTAL', 'project' => $project->id, 'level' => 1, 'status' => 'earned']))
            ->assertOk()
            ->assertSee('RC-PORTAL')
            ->assertSee('500.00');

        $this->actingAs($user)->get(route('customer.commissions', ['search' => 'unknown-member']))
            ->assertOk()
            ->assertSee('No commission transactions yet.')
            ->assertDontSee('RC-PORTAL');

        $this->actingAs($user)->get(route('customer.withdrawals.index'))
            ->assertOk()
            ->assertSee('Payable commission')
            ->assertSee('500.00')
            ->assertSee('Withdrawal history');
        $this->actingAs($user)->post(route('customer.withdrawals.store'), [
            'amount' => 500,
            'payment_method' => 'bank_transfer',
            'account_title' => 'Portal Customer',
            'account_number' => 'PK00TEST123',
        ])->assertRedirect(route('customer.withdrawals.index'));
        $this->assertDatabaseHas('withdrawal_requests', [
            'customer_id' => $user->id,
            'amount' => 500,
            'status' => 'pending',
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($admin)->get(route('withdrawal-requests.index'))
            ->assertOk()
            ->assertSee('Withdrawal requests')
            ->assertSee('Portal Customer')
            ->assertSee('500.00')
            ->assertSee('Review & pay', false);
        $this->actingAs($user)->post(route('customer.withdrawals.store'), [
            'amount' => 200,
            'payment_method' => 'bank_transfer',
            'account_title' => 'Portal Customer',
            'account_number' => 'PK00TEST123',
        ])->assertSessionHasErrors('amount');
        $withdrawal = \App\Models\WithdrawalRequest::firstOrFail();
        $this->actingAs($admin)->patch(route('withdrawal-requests.review', $withdrawal), [
            'decision' => 'paid',
            'transaction_reference' => 'BANK-WDR-500',
            'review_notes' => 'Transferred to customer account.',
        ])->assertRedirect();
        $this->assertSame('approved', $withdrawal->refresh()->status);
        $this->assertDatabaseHas('commission_payouts', [
            'agent_id' => $user->id,
            'amount' => 500,
            'transaction_reference' => 'BANK-WDR-500',
        ]);
        $this->assertDatabaseHas('commissions', [
            'beneficiary_id' => $user->id,
            'status' => 'paid',
        ]);

        $this->actingAs($user)->get(route('payments.index'))->assertForbidden();

        $this->actingAs($user)->patch(route('theme.update'), ['theme' => 'dark'])->assertRedirect();
        $this->assertEquals('dark', $user->refresh()->theme);
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('class="dark"', false);
    }

    public function test_customer_can_print_their_receipt_but_cannot_view_another_customers_receipt(): void
    {
        $customerUser = User::factory()->create(['role' => 'customer', 'email_verified_at' => now(), 'phone' => '0300', 'referral_code' => 'REF-OWNER']);
        $otherUser = User::factory()->create(['role' => 'customer', 'email_verified_at' => now(), 'phone' => '0301', 'referral_code' => 'REF-OTHER']);
        $project = Project::create(['name' => 'Receipt Project', 'slug' => 'receipt-project', 'location' => 'Abbottabad', 'gross_area_marla' => 1000, 'saleable_area_marla' => 1000]);
        $package = PlotPackage::create(['project_id' => $project->id, 'name' => 'Receipt Package', 'size_marla' => 5, 'booking_amount' => 100000, 'months' => 24, 'monthly_amount' => 25000, 'month_12_balloon' => 0, 'month_24_balloon' => 0, 'month_36_balloon' => 0]);
        $booking = Booking::create(['booking_number' => 'BOOK-RECEIPT', 'project_id' => $project->id, 'package_id' => $package->id, 'customer_id' => $customerUser->id, 'booking_date' => today(), 'total_price' => 700000, 'booking_amount' => 100000, 'financed_amount' => 600000, 'status' => 'active']);
        $payment = Payment::create(['receipt_number' => 'RC-PRINT-001', 'booking_id' => $booking->id, 'customer_id' => $customerUser->id, 'amount' => 100000, 'payment_method' => 'online_transfer', 'transaction_reference' => 'TX-123', 'payment_date' => today(), 'status' => 'verified', 'verified_at' => now()]);

        $this->actingAs($customerUser)->get(route('customer.payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Payment receipt')
            ->assertSee('RC-PRINT-001')
            ->assertSee('BOOK-RECEIPT')
            ->assertSee('Receipt Project')
            ->assertSee('100,000.00')
            ->assertSee('TX-123')
            ->assertSee('Print / save PDF');

        $this->actingAs($otherUser)->get(route('customer.payments.receipt', $payment))->assertNotFound();
    }

    public function test_management_can_preview_a_customer_portal_in_read_only_mode(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $customerUser = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone' => '0302',
            'referral_code' => 'REF-PREVIEW',
        ]);
        $customer = Customer::findOrFail($customerUser->id);

        $this->actingAs($admin)->get(route('customers.portal', $customer))
            ->assertOk()
            ->assertSee('Admin portal preview')
            ->assertSee($customer->name)
            ->assertSee('REF-PREVIEW')
            ->assertDontSee('+ Book a plot');

        $this->actingAs($admin)->get(route('customers.team', $customer))
            ->assertOk()
            ->assertSee('Admin team preview')
            ->assertSee('Your referral network across three levels')
            ->assertSee('REF-PREVIEW');

        $this->actingAs($admin)->get(route('customers.commissions', $customer))
            ->assertOk()
            ->assertSee('Admin commission preview')
            ->assertSee('Commission history');

        $this->actingAs($customerUser)->get(route('customers.portal', $customer))->assertForbidden();
        $this->actingAs($customerUser)->get(route('customers.team', $customer))->assertForbidden();
        $this->actingAs($customerUser)->get(route('customers.commissions', $customer))->assertForbidden();
        $this->actingAs($admin)->get(route('customer.team'))->assertForbidden();
        $this->actingAs($admin)->get(route('customer.commissions'))->assertForbidden();
    }
}
