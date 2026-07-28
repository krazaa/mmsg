<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PlotPackage;
use App\Models\Referral;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomerAgentPaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_referrer_can_be_created_from_management_pages(): void
    {
        $admin = User::factory()->create();
        $sponsor = User::factory()->create(['role' => 'customer', 'referral_code' => 'REF-SPONSOR']);
        $this->actingAs($admin)->post(route('customers.store'), ['name' => 'Customer', 'file_no' => 'AT-0001', 'email' => 'customer@example.com', 'password' => 'password', 'cnic' => '11111-1111111-1', 'phone' => '0300', 'referred_by_code' => 'REF-SPONSOR', 'status' => 1])->assertRedirect(route('customers.index'));
        $customer = Customer::where('email', 'customer@example.com')->firstOrFail();
        $this->assertSame('customer', $customer->role);
        $this->assertEquals($sponsor->id, Referral::where('user_id', $customer->id)->value('sponsor_id'));
        $this->assertSame('AT-0001', $customer->file_no);
        $this->actingAs($admin)->get(route('customers.edit', $customer))->assertOk()->assertSee('Referred by referral code');
        $this->actingAs($admin)->get(route('customers.index', ['search' => 'AT-0001']))->assertOk()->assertSee('AT-0001');
    }

    public function test_reversing_payment_restores_installment_balance_and_reverses_commissions(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $agent = User::where('email', 'agent@abdullahtown.pk')->firstOrFail();
        $package = PlotPackage::where('name', '5 Marla')->firstOrFail();
        $this->actingAs($admin)->post(route('bookings.store'), ['package_id' => $package->id, 'name' => 'Ali', 'cnic' => '22222-2222222-2', 'phone' => '0300', 'agent_id' => $agent->id, 'booking_date' => '2026-07-20', 'payment_method' => 'cash']);
        $booking = Booking::firstOrFail();
        $this->actingAs($admin)->post(route('bookings.payments.store', $booking), ['installment_number' => 1, 'amount' => 50000, 'payment_method' => 'cash']);
        $payment = Payment::whereNotNull('installment_schedule_id')->firstOrFail();
        $this->assertEquals(50000, (float) $payment->installment->paid_amount);
        $this->actingAs($admin)->get(route('customers.show', $booking->customer))
            ->assertOk()->assertSee('Payment history')->assertSee($payment->receipt_number)->assertSee('Month 1')
            ->assertSee(route('bookings.show', $booking));
        $this->actingAs($admin)->get(route('payments.index'))
            ->assertOk()->assertSee($payment->receipt_number)->assertSee(route('bookings.show', $booking));

        $this->actingAs($admin)->put(route('payments.update', $payment), ['payment_method' => 'cash', 'status' => 'reversed', 'verification_notes' => 'Refunded'])->assertRedirect(route('payments.index'));
        $this->assertEquals(0, (float) $payment->installment->refresh()->paid_amount);
        $this->assertEquals('pending', $payment->installment->status);
        $this->assertEquals(3, Commission::where('payment_id', $payment->id)->where('status', 'reversed')->count());
    }

    public function test_updating_customer_automatically_restores_portal_permissions(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $customer = Customer::create([
            'name' => 'Portal Customer',
            'email' => 'customer@abdullahtown.pk',
            'password' => 'password',
            'phone' => '03001234567',
            'status' => true,
        ]);
        $user = User::findOrFail($customer->id);
        $user->syncRoles([]);
        Role::findByName('customer')->syncPermissions([]);

        $this->actingAs($admin)->put(route('customers.update', $customer), [
            'name' => $customer->name,
            'file_no' => 'AT-UPDATED',
            'father_name' => $customer->father_name,
            'email' => $customer->email,
            'cnic' => $customer->cnic,
            'phone' => $customer->phone,
            'address' => $customer->address,
            'status' => 1,
        ])->assertRedirect(route('customers.index'));

        $user->refresh();
        $this->assertSame('AT-UPDATED', $user->file_no);
        $this->assertTrue($user->hasRole('customer'));
        foreach (Permissions::customer() as $permission) {
            $this->assertTrue($user->can($permission), "Customer is missing [$permission].");
        }
    }
}
