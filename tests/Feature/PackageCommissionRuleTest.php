<?php

namespace Tests\Feature;

use App\Contracts\CommissionDistributor;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\CommissionRule;
use App\Models\InstallmentSchedules;
use App\Models\Payment;
use App\Models\PlotPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class PackageCommissionRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrators_can_set_commissions_but_staff_cannot_access_commission_rules(): void
    {
        $this->seed();
        $staff = User::factory()->create(['role' => 'staff']);
        $admin = User::factory()->create(['role' => 'admin']);
        $package = PlotPackage::firstOrFail();

        $this->actingAs($staff)->get(route('commission-rules.index', ['project' => $package->project_id]))->assertForbidden();
        $this->actingAs($staff)->put(route('commission-rules.update', $package), [])->assertForbidden();
        $this->actingAs($admin)->get(route('commission-rules.index', ['project' => $package->project_id]))->assertOk();
        $this->actingAs($admin)->put(route('commission-rules.update', $package), [
            'levels' => [
                'cash' => [1 => 6, 2 => 3, 3 => 2],
                'first_payment' => [1 => 5, 2 => 3, 3 => 2],
                'installment' => [1 => 4, 2 => 2, 3 => 1],
            ],
        ])->assertRedirect();

        $this->assertSame('percentage', CommissionRule::where('package_id', $package->id)->where('payment_plan', 'cash')->where('level', 1)->value('calculation_type'));
        $this->assertEquals(6, (float) CommissionRule::where('package_id', $package->id)->where('payment_plan', 'cash')->where('level', 1)->value('percentage'));
    }

    public function test_each_package_uses_its_own_three_commission_levels(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $agent = User::where('email', 'agent@abdullahtown.pk')->firstOrFail();
        $package = PlotPackage::where('name', '10 Marla')->firstOrFail();

        $this->actingAs($superAdmin)->put(route('commission-rules.update', $package), [
            'levels' => [
                'cash' => [1 => 6, 2 => 3, 3 => 1],
                'first_payment' => [1 => 7, 2 => 3, 3 => 1],
                'installment' => [1 => 8, 2 => 4, 3 => 1],
            ],
            'active' => [
                'cash' => [1 => 1, 2 => 1, 3 => 1],
                'first_payment' => [1 => 1, 2 => 1, 3 => 1],
                'installment' => [1 => 1, 2 => 1, 3 => 1],
            ],
            'calculation_types' => [
                'cash' => [1 => 'percentage', 2 => 'percentage', 3 => 'percentage'],
                'first_payment' => [1 => 'percentage', 2 => 'percentage', 3 => 'percentage'],
                'installment' => [1 => 'fixed', 2 => 'percentage', 3 => 'percentage'],
            ],
            'fixed_amounts' => [
                'cash' => [1 => 0, 2 => 0, 3 => 0],
                'first_payment' => [1 => 0, 2 => 0, 3 => 0],
                'installment' => [1 => 700, 2 => 0, 3 => 0],
            ],
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('bookings.store'), [
            'package_id' => $package->id, 'name' => 'Customer', 'cnic' => '33333-3333333-3',
            'phone' => '0300', 'agent_id' => $agent->id, 'booking_date' => '2026-07-20', 'payment_method' => 'cash',
        ]);

        $this->assertEquals([24500.0, 10500.0, 3500.0], Commission::orderBy('level')->pluck('amount')->map(fn ($amount) => (float) $amount)->all());
        $other = PlotPackage::where('name', '5 Marla')->firstOrFail();
        $this->assertEquals(5, (float) CommissionRule::where('package_id', $other->id)->where('payment_plan', 'first_payment')->where('level', 1)->value('percentage'));
        $this->assertEquals(9, CommissionRule::where('package_id', $package->id)->count());
        $booking = Booking::firstOrFail();
        $this->actingAs($admin)->get(route('bookings.show', $booking))->assertOk()->assertSee('AGT-AGENT');

        $this->actingAs($admin)->get(route('customers.show', $agent))
            ->assertOk()->assertSee('24,500.00');

        $this->actingAs($admin)->post(route('customers.commission-payouts.store', $agent), [
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'AGENT-PAY-1',
            'notes' => 'Monthly payout',
        ])->assertRedirect(route('customers.show', $agent));
        $payout = CommissionPayout::firstOrFail();
        $this->assertEquals(24500, (float) $payout->amount);
        $this->assertEquals('paid', Commission::where('beneficiary_id', $agent->id)->value('status'));
        $payoutActivity = Activity::where('subject_type', CommissionPayout::class)->where('subject_id', $payout->id)->where('event', 'created')->firstOrFail();
        $this->assertSame($admin->id, $payoutActivity->causer_id);
        $this->assertSame('AGENT-PAY-1', $payoutActivity->properties['attributes']['transaction_reference']);
        $this->assertTrue(Activity::where('subject_type', Commission::class)->where('event', 'updated')->where('properties->attributes->status', 'paid')->exists());
        $this->actingAs($admin)->get(route('customers.show', $agent))
            ->assertOk()->assertSee('AGENT-PAY-1');

        $cashBooking = $booking->replicate();
        $cashBooking->booking_number = 'B-CASH-RATE';
        $cashBooking->payment_plan = 'cash';
        $cashBooking->save();
        $cashPayment = Payment::create([
            'receipt_number' => 'CASH-RATE-1', 'booking_id' => $cashBooking->id,
            'customer_id' => $cashBooking->customer_id, 'amount' => 1000,
            'payment_method' => 'cash', 'payment_date' => today(), 'status' => 'verified',
        ]);
        app(CommissionDistributor::class)->distribute($cashPayment, $cashBooking);

        $this->assertEquals(60, (float) Commission::where('payment_id', $cashPayment->id)->where('level', 1)->value('amount'));

        $installmentPayment = Payment::create([
            'receipt_number' => 'INSTALLMENT-RATE-1', 'booking_id' => $booking->id,
            'customer_id' => $booking->customer_id, 'installment_schedule_id' => InstallmentSchedules::where('booking_id', $booking->id)->firstOrFail()->id,
            'amount' => 1000, 'payment_method' => 'cash', 'payment_date' => today(), 'status' => 'verified',
        ]);
        app(CommissionDistributor::class)->distribute($installmentPayment, $booking);

        $this->assertEquals(700, (float) Commission::where('payment_id', $installmentPayment->id)->where('level', 1)->value('amount'));
        $this->assertSame('fixed', Commission::where('payment_id', $installmentPayment->id)->where('level', 1)->value('calculation_type'));
        $this->assertEquals(0, (float) CommissionRule::where('package_id', $package->id)->where('payment_plan', 'installment')->where('level', 1)->value('percentage'));
    }
}
