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
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\WithdrawalSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_manages_customer_announcement_on_its_own_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('customer-announcement.edit'))
            ->assertOk()
            ->assertSee('Customer announcement popup')
            ->assertSee('Save announcement');

        $this->actingAs($admin)->put(route('customer-announcement.update'), [
            'enabled' => 1,
            'title' => 'Project update',
            'message' => 'A new project update is now available.',
        ])->assertRedirect();

        $this->assertDatabaseHas('site_settings', ['key' => 'customer_announcement_enabled', 'value' => '1']);
        $this->assertDatabaseHas('site_settings', ['key' => 'customer_announcement_title', 'value' => 'Project update']);
        $this->assertDatabaseHas('site_settings', ['key' => 'customer_announcement_message', 'value' => 'A new project update is now available.']);
    }

    public function test_customer_portal_shows_active_announcement_popup(): void
    {
        $user = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);
        foreach ([
            'customer_announcement_enabled' => '1',
            'customer_announcement_title' => 'Office holiday notice',
            'customer_announcement_message' => 'The office will be closed on Monday.',
            'customer_announcement_version' => '123',
        ] as $key => $value) {
            SiteSetting::create(compact('key', 'value'));
        }

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Office holiday notice')
            ->assertSee('The office will be closed on Monday.')
            ->assertSee('Close announcement');
    }

    public function test_admin_setting_can_hide_referral_codes_from_the_customer_portal(): void
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'phone' => '0300',
            'referral_code' => 'REF-HIDDEN-PORTAL',
        ]);

        SiteSetting::create([
            'key' => 'customer_portal_show_referral_code',
            'value' => '0',
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('REF-HIDDEN-PORTAL')
            ->assertDontSee('Copy referral link');

        $this->actingAs($user)->get(route('customer.team'))
            ->assertOk()
            ->assertDontSee('REF-HIDDEN-PORTAL')
            ->assertDontSee('Copy referral link');
    }

    public function test_team_tree_labels_fully_paid_cash_and_installment_bookings(): void
    {
        $owner = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);
        $cashCustomer = User::factory()->create(['name' => 'Cash Team Member', 'role' => 'customer']);
        $installmentCustomer = User::factory()->create(['name' => 'Installment Team Member', 'role' => 'customer']);
        Referral::create(['user_id' => $cashCustomer->id, 'sponsor_id' => $owner->id]);
        Referral::create(['user_id' => $installmentCustomer->id, 'sponsor_id' => $owner->id]);
        $project = Project::create([
            'name' => 'Paid Plans',
            'slug' => 'paid-plans',
            'gross_area_marla' => 100,
            'saleable_area_marla' => 100,
            'status' => true,
        ]);
        $package = PlotPackage::create([
            'project_id' => $project->id,
            'name' => 'Paid Package',
            'size_marla' => 5,
            'cash_price' => 100000,
            'booking_amount' => 50000,
            'months' => 1,
            'monthly_amount' => 100000,
            'status' => true,
        ]);

        foreach ([[$cashCustomer, 'cash', 100000], [$installmentCustomer, 'installment', 150000]] as [$customer, $plan, $total]) {
            $booking = Booking::create([
                'booking_number' => 'PAID-'.strtoupper($plan),
                'project_id' => $project->id,
                'package_id' => $package->id,
                'customer_id' => $customer->id,
                'booking_date' => today(),
                'payment_plan' => $plan,
                'total_price' => $total,
                'booking_amount' => $plan === 'cash' ? $total : 50000,
                'financed_amount' => $plan === 'cash' ? 0 : 100000,
                'status' => 'completed',
            ]);
            Payment::create([
                'receipt_number' => 'RC-'.strtoupper($plan),
                'booking_id' => $booking->id,
                'customer_id' => $customer->id,
                'amount' => $total,
                'payment_method' => 'bank_transfer',
                'payment_date' => today(),
                'status' => 'verified',
            ]);
        }

        $this->actingAs($owner)->get(route('customer.team'))
            ->assertOk()
            ->assertSee('Paid Cash')
            ->assertSee('Paid Installments');
    }

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
        $payment->update(['verification_notes' => 'Payment proof checked and approved by the office.']);
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
            ->assertSee('Review notes')
            ->assertSee('Payment proof checked and approved by the office.')
            ->assertSee('REF-PORTAL')
            ->assertSee('href="'.route('dashboard').'#payments"', false)
            ->assertSee(route('customer.payments.receipt', $payment))
            ->assertSee('RC-PORTAL')
            ->assertSee('25,000.00')
            ->assertDontSee('77,777.00')
            ->assertDontSee('Manage projects');

        $this->actingAs($user)->get(route('customer.payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Office review notes')
            ->assertSee('Payment proof checked and approved by the office.');

        $this->actingAs($user)->get(route('customer.installments'))
            ->assertOk()
            ->assertSee('Plot installment schedules')
            ->assertSee('BOOK-PORTAL')
            ->assertSee('Flexible Package · 5.00 marla')
            ->assertSee('RC-FIRST-PORTAL')
            ->assertSee('First payment receipt')
            ->assertSee('25,000.00')
            ->assertSee('77,777.00')
            ->assertSee('Upcoming');

        $this->actingAs($user)->get(route('customer.team'))
            ->assertOk()
            ->assertSee('My referral network')
            ->assertSee('Your referral network across three levels')
            ->assertSee('Level One Member')
            ->assertSee('Level Two Member')
            ->assertSee('Generation 2')
            ->assertSee('Copy referral link')
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

        $this->assertSame('weekly', $user->refresh()->withdrawal_frequency);
        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Withdrawal frequency')
            ->assertSee('Current policy set by the office.')
            ->assertDontSee('Only the office can change your request reset period.')
            ->assertSee('Weekly')
            ->assertDontSee('Save withdrawal frequency')
            ->assertSee('Withdrawal PIN')
            ->assertSee('Set withdrawal PIN')
            ->assertDontSee('Send me a new PIN');
        $this->actingAs($user)->patch(route('profile.withdrawal-pin.update'), [
            'current_password' => 'password',
            'withdrawal_pin' => '2468',
            'withdrawal_pin_confirmation' => '2468',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('success');

        $this->actingAs($user)->get(route('customer.withdrawals.index'))
            ->assertOk()
            ->assertSee('Payable commission')
            ->assertSee('500.00')
            ->assertSee('Weekly limit')
            ->assertDontSee('Save withdrawal frequency')
            ->assertSee('Enter the PIN you created in Profile & Security.', false)
            ->assertDontSee('Send me a new PIN')
            ->assertSee('Withdrawal history');
        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Send me a new PIN')
            ->assertSee('Send a new temporary PIN?')
            ->assertSee('Your current PIN will stop working.');
        $this->actingAs($user)->post(route('customer.withdrawals.store'), [
            'amount' => 500,
            'withdrawal_pin' => '9999',
            'payment_method' => 'bank_transfer',
            'account_title' => 'Portal Customer',
            'account_number' => 'PK00TEST123',
        ])->assertSessionHasErrors('withdrawal_pin');
        $this->assertSame(1, $user->refresh()->withdrawal_pin_failed_attempts);
        $selectedWeekday = now()->isoWeekday() === 7 ? 1 : now()->isoWeekday() + 1;
        WithdrawalSetting::where('frequency', 'weekly')->firstOrFail()->update([
            'withdrawal_day' => $selectedWeekday,
            'withdrawal_day_mode' => 'selected_day',
        ]);
        $this->actingAs($user)->post(route('customer.withdrawals.store'), [
            'amount' => 500,
            'withdrawal_pin' => '2468',
            'payment_method' => 'bank_transfer',
            'account_title' => 'Portal Customer',
            'account_number' => 'PK00TEST123',
        ])->assertSessionHasErrors('amount');
        WithdrawalSetting::where('frequency', 'weekly')->firstOrFail()->update([
            'withdrawal_day' => now()->isoWeekday(),
            'withdrawal_day_mode' => 'before_selected_day',
        ]);
        $this->actingAs($user)->post(route('customer.withdrawals.store'), [
            'amount' => 500,
            'withdrawal_pin' => '2468',
            'payment_method' => 'bank_transfer',
            'account_title' => 'Portal Customer',
            'account_number' => 'PK00TEST123',
        ])->assertSessionHasErrors('amount');
        WithdrawalSetting::where('frequency', 'weekly')->firstOrFail()->update([
            'withdrawal_day' => null,
        ]);
        WithdrawalSetting::where('frequency', 'weekly')->firstOrFail()->update(['maximum_amount' => 300]);
        $this->actingAs($user)->post(route('customer.withdrawals.store'), [
            'amount' => 500,
            'withdrawal_pin' => '2468',
            'payment_method' => 'bank_transfer',
            'account_title' => 'Portal Customer',
            'account_number' => 'PK00TEST123',
        ])->dumpSession()->assertSessionHasErrors('amount');
        WithdrawalSetting::where('frequency', 'weekly')->firstOrFail()->update(['maximum_amount' => 0]);
        WithdrawalSetting::query()->update([
            'fee_enabled' => true,
            'fee_type' => 'percentage',
            'fee_value' => 2,
        ]);
        $this->actingAs($user)->post(route('customer.withdrawals.store'), [
            'amount' => 500,
            'withdrawal_pin' => '2468',
            'payment_method' => 'bank_transfer',
            'account_title' => 'Portal Customer',
            'account_number' => 'PK00TEST123',
        ])->assertRedirect(route('customer.withdrawals.index'));
        $this->assertDatabaseHas('withdrawal_requests', [
            'customer_id' => $user->id,
            'amount' => 500,
            'status' => 'pending',
            'fee_amount' => 10,
            'net_amount' => 490,
        ]);
        $this->assertSame(0, $user->refresh()->withdrawal_pin_failed_attempts);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $anotherCustomer = User::factory()->create(['role' => 'customer', 'withdrawal_frequency' => 'weekly']);
        $this->actingAs($admin)->put(route('withdrawal-settings.update'), [
            'frequency' => 'monthly',
            'policies' => [
                'daily' => ['request_limit' => 1, 'withdrawal_day' => 2, 'withdrawal_day_mode' => 'selected_day', 'minimum_amount' => 100, 'maximum_amount' => 500],
                'weekly' => ['request_limit' => 2, 'withdrawal_day' => 5, 'withdrawal_day_mode' => 'before_selected_day', 'minimum_amount' => 200, 'maximum_amount' => 1000],
                'monthly' => ['request_limit' => 3, 'withdrawal_day' => 7, 'withdrawal_day_mode' => 'selected_day', 'minimum_amount' => 300, 'maximum_amount' => 1500],
            ],
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertSame('monthly', $user->refresh()->withdrawal_frequency);
        $this->assertSame('monthly', $anotherCustomer->refresh()->withdrawal_frequency);
        $this->actingAs($admin)->put(route('app-settings.update'), [
            'fee_enabled' => 1,
            'fee_type' => 'fixed',
            'fee_value' => 25,
            'pin_recovery_enabled' => 1,
            'customer_portal_show_referral_code' => 1,
            'maintenance_mode_enabled' => 0,
            'maintenance_page_title' => 'We will be back shortly.',
            'maintenance_page_message' => 'Scheduled improvements are underway.',
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertSame([
            'frequency' => 'monthly',
            'policies' => [
                'daily' => ['request_limit' => 1, 'withdrawal_day' => 2, 'withdrawal_day_mode' => 'selected_day', 'minimum_amount' => 100.0, 'maximum_amount' => 500.0],
                'weekly' => ['request_limit' => 2, 'withdrawal_day' => 5, 'withdrawal_day_mode' => 'before_selected_day', 'minimum_amount' => 200.0, 'maximum_amount' => 1000.0],
                'monthly' => ['request_limit' => 3, 'withdrawal_day' => 7, 'withdrawal_day_mode' => 'selected_day', 'minimum_amount' => 300.0, 'maximum_amount' => 1500.0],
            ],
            'fee' => ['enabled' => true, 'type' => 'fixed', 'value' => 25.0],
            'pin_recovery_enabled' => true,
        ], WithdrawalSetting::settings());
        WithdrawalSetting::where('frequency', 'monthly')->update(['withdrawal_day' => null]);
        $this->actingAs($admin)->get(route('withdrawal-settings.edit'))
            ->assertOk()
            ->assertSee('Withdrawal settings')
            ->assertSee('Daily limits')
            ->assertSee('Weekly limits')
            ->assertSee('Monthly limits')
            ->assertDontSee('Withdrawal fee')
            ->assertDontSee('Customer PIN recovery')
            ->assertSee('Save withdrawal settings');
        $this->actingAs($admin)->get(route('app-settings.edit'))
            ->assertOk()
            ->assertSee('App settings')
            ->assertSee('Withdrawal fee')
            ->assertSee('Customer PIN recovery')
            ->assertSee('Enable for customers')
            ->assertSee('Customer portal')
            ->assertSee('Show referral code')
            ->assertSee('Maintenance mode')
            ->assertSee('Preview maintenance page')
            ->assertSee('Save app settings');
        $this->actingAs($admin)->get(route('withdrawal-requests.index'))
            ->assertOk()
            ->assertSee('Withdrawal requests')
            ->assertDontSee('Save withdrawal settings')
            ->assertSee('Portal Customer')
            ->assertSee('500.00')
            ->assertSee('Review & pay', false)
            ->assertSee('Templates')
            ->assertSee('Withdrawal paid successfully to the selected account.')
            ->assertSee('Payout account details are incorrect.');
        $this->actingAs($user)->post(route('customer.withdrawals.store'), [
            'amount' => 200,
            'withdrawal_pin' => '2468',
            'payment_method' => 'bank_transfer',
            'account_title' => 'Portal Customer',
            'account_number' => 'PK00TEST123',
        ])->dumpSession()->assertSessionHasErrors('amount');
        $withdrawal = WithdrawalRequest::firstOrFail();
        $this->actingAs($admin)->patch(route('withdrawal-requests.review', $withdrawal), [
            'decision' => 'paid',
            'transaction_reference' => 'BANK-WDR-500',
            'review_notes' => 'Transferred to customer account.',
        ])->assertRedirect();
        $this->assertSame('approved', $withdrawal->refresh()->status);
        $this->actingAs($user)->get(route('customer.withdrawals.index'))
            ->assertOk()
            ->assertSee('Reviewed at')
            ->assertSee($withdrawal->reviewed_at->format('d M Y'));
        $wrongPinRequest = [
            'amount' => 100,
            'withdrawal_pin' => '9999',
            'payment_method' => 'bank_transfer',
            'account_title' => 'Portal Customer',
            'account_number' => 'PK00TEST123',
        ];
        foreach (range(1, 4) as $attempt) {
            $this->actingAs($user)->post(route('customer.withdrawals.store'), $wrongPinRequest)
                ->dumpSession()->assertSessionHasErrors('withdrawal_pin');
        }
        $user->refresh();
        $this->assertSame(4, $user->withdrawal_pin_failed_attempts);
        $this->assertTrue($user->withdrawal_pin_locked_until->isFuture());
        $this->actingAs($user)->get(route('customer.withdrawals.index'))
            ->assertOk()
            ->assertSee('Withdrawals temporarily locked')
            ->assertSee($user->withdrawal_pin_locked_until->format('d M Y, h:i A'));
        $this->actingAs($user)->post(route('customer.withdrawals.store'), array_merge($wrongPinRequest, ['withdrawal_pin' => '2468']))
            ->assertSessionHasErrors('withdrawal_pin');
        $this->actingAs($admin)->get(route('withdrawal-requests.index'))
            ->assertOk()
            ->assertSee('Withdrawals temporarily locked')
            ->assertSee('Remove temporary lock')
            ->assertSee($user->name)
            ->assertSee('Referral: '.$user->referral_code);
        $this->actingAs($admin)->patch(route('withdrawal-pin-locks.destroy', $user))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSame(0, $user->refresh()->withdrawal_pin_failed_attempts);
        $this->assertNull($user->withdrawal_pin_locked_until);
        $this->assertDatabaseHas('commission_payouts', [
            'agent_id' => $user->id,
            'amount' => 500,
            'fee_amount' => 10,
            'net_amount' => 490,
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
