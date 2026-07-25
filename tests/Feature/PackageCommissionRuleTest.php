<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\CommissionRule;
use App\Models\PlotPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class PackageCommissionRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_package_uses_its_own_three_commission_levels(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $agent = User::where('email', 'agent@abdullahtown.pk')->firstOrFail();
        $package = PlotPackage::where('name', '10 Marla')->firstOrFail();

        $this->actingAs($admin)->put(route('commission-rules.update', $package), [
            'levels' => [1 => 8, 2 => 4, 3 => 1],
            'active' => [1 => 1, 2 => 1, 3 => 1],
        ])->assertRedirect();
        $this->actingAs($admin)->post(route('bookings.store'), [
            'package_id' => $package->id, 'name' => 'Customer', 'cnic' => '33333-3333333-3',
            'phone' => '0300', 'agent_id' => $agent->id, 'booking_date' => '2026-07-20', 'payment_method' => 'cash',
        ]);

        $this->assertEquals([28000.0, 14000.0, 3500.0], Commission::orderBy('level')->pluck('amount')->map(fn ($amount) => (float) $amount)->all());
        $other = PlotPackage::where('name', '5 Marla')->firstOrFail();
        $this->assertEquals(5, (float) CommissionRule::where('package_id', $other->id)->where('level', 1)->value('percentage'));
        $this->assertEquals(3, CommissionRule::where('package_id', $package->id)->count());
        $booking = Booking::firstOrFail();
        $this->actingAs($admin)->get(route('bookings.show', $booking))->assertOk()->assertSee('AGT-AGENT');

        $this->actingAs($admin)->get(route('agents.show', $agent))
            ->assertOk()->assertSee('Commission history')->assertSee('28,000.00')
            ->assertSee('Sponsor hierarchy')->assertSee('Who this agent reports to, up to 3 sponsors.')
            ->assertSee('Sales Manager')->assertSee('Sales Director');

        $manager = User::where('email', 'manager@abdullahtown.pk')->firstOrFail();
        $this->actingAs($admin)->get(route('agents.show', $manager))
            ->assertOk()->assertSee('Three-level downline')->assertSee('Sales Agent');

        $this->actingAs($admin)->post(route('agents.payouts.store', $agent), [
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'AGENT-PAY-1',
            'notes' => 'Monthly payout',
        ])->assertRedirect(route('agents.show', $agent));
        $payout = CommissionPayout::firstOrFail();
        $this->assertEquals(28000, (float) $payout->amount);
        $this->assertEquals('paid', Commission::where('beneficiary_id', $agent->id)->value('status'));
        $payoutActivity = Activity::where('subject_type', CommissionPayout::class)->where('subject_id', $payout->id)->where('event', 'created')->firstOrFail();
        $this->assertSame($admin->id, $payoutActivity->causer_id);
        $this->assertSame('AGENT-PAY-1', $payoutActivity->properties['attributes']['transaction_reference']);
        $this->assertTrue(Activity::where('subject_type', Commission::class)->where('event', 'updated')->where('properties->attributes->status', 'paid')->exists());
        $this->actingAs($admin)->get(route('agents.show', $agent))
            ->assertOk()->assertSee('Payout history')->assertSee('AGENT-PAY-1')->assertSee('Paid out');
    }
}
