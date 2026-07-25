<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_generates_schedule_updates_inventory_and_three_commissions(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $agent = User::where('email', 'agent@abdullahtown.pk')->firstOrFail();
        $package = PlotPackage::where('name', '5 Marla')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('bookings.store'), [
            'package_id' => $package->id, 'name' => 'Ali Khan', 'cnic' => '12345-1234567-1', 'phone' => '03001234567',
            'agent_id' => $agent->id, 'booking_date' => '2026-07-20', 'payment_method' => 'cash',
        ]);

        $booking = Booking::firstOrFail();
        $response->assertRedirect(route('bookings.show', $booking));
        $this->assertCount(36, $booking->installments);
        $this->assertEquals('2026-07-20', $booking->installments()->where('installment_number', 1)->value('due_date')->toDateString());
        $this->assertEquals(200000, (float) $booking->installments()->where('installment_number', 12)->value('total_due'));
        $this->assertEquals(5, (float) Project::first()->sold_area_marla);
        $this->assertCount(3, Commission::all());
        $this->assertEquals(17500, (float) Commission::where('level', 1)->value('amount'));
    }

    public function test_booking_is_rejected_when_land_is_not_available(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        Project::query()->update(['sold_area_marla' => 7999]);
        $package = PlotPackage::where('name', '5 Marla')->firstOrFail();
        $this->actingAs($admin)->post(route('bookings.store'), [
            'package_id' => $package->id, 'name' => 'Ali Khan', 'cnic' => '12345-1234567-2', 'phone' => '03001234567',
            'booking_date' => '2026-07-20', 'payment_method' => 'cash',
        ])->assertSessionHasErrors('package_id');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_schedule_uses_dynamic_balloon_payment_months(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $package = PlotPackage::where('name', '5 Marla')->firstOrFail();
        $package->update([
            'months' => 10,
            'balloon_payments' => [
                ['month' => 3, 'amount' => 125000],
                ['month' => 9, 'amount' => 225000],
            ],
        ]);

        $this->actingAs($admin)->post(route('bookings.store'), [
            'package_id' => $package->id, 'name' => 'Dynamic Plan Customer',
            'cnic' => '12345-1234567-8', 'phone' => '03001234567',
            'booking_date' => '2026-07-20', 'payment_method' => 'cash',
        ])->assertRedirect();

        $booking = Booking::firstOrFail();
        $this->assertCount(10, $booking->installments);
        $this->assertEquals(125000, (float) $booking->installments()->where('installment_number', 3)->value('balloon_amount'));
        $this->assertEquals(225000, (float) $booking->installments()->where('installment_number', 9)->value('balloon_amount'));
        $this->assertEquals(0, (float) $booking->installments()->where('installment_number', 6)->value('balloon_amount'));
    }

    public function test_existing_customer_can_make_another_booking_without_duplicate_cnic(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $customer = Customer::create(['name' => 'Existing Customer', 'cnic' => '55555-5555555-5', 'phone' => '0300', 'status' => true]);
        $package = PlotPackage::where('name', '5 Marla')->firstOrFail();

        $this->actingAs($admin)->get(route('sales.create'))->assertOk()
            ->assertSee('New customer')->assertSee('Existing customer')->assertSee('payment plan');

        $this->actingAs($admin)->post(route('bookings.store'), [
            'package_id' => $package->id, 'customer_id' => $customer->id,
            'booking_date' => '2026-07-20', 'payment_method' => 'cash',
        ])->assertRedirect();

        $this->assertSame(1, Customer::count());
        $this->assertEquals($customer->id, Booking::firstOrFail()->customer_id);
    }

    public function test_new_booking_packages_are_sorted_by_id(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $project = Project::firstOrFail();
        $expected = $project->packages()->where('status', true)->orderBy('id')->pluck('name')->all();

        $this->actingAs($admin)->get(route('sales.create', ['project' => $project->id]))
            ->assertOk()->assertSeeInOrder($expected);
    }
}
