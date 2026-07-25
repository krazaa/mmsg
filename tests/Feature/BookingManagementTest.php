<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancelling_booking_returns_inventory_and_reverses_commissions(): void
    {
        [$user, $booking] = $this->createBooking();

        $this->actingAs($user)->put(route('bookings.update', $booking), $this->updateData($booking, 'cancelled'))
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertEquals('cancelled', $booking->refresh()->status);
        $this->assertEquals(0, (float) Project::first()->sold_area_marla);
        $this->assertDatabaseMissing('commissions', ['booking_id' => $booking->id, 'status' => 'earned']);
        $this->assertEquals(36, $booking->installments()->where('status', 'cancelled')->count());
    }

    public function test_booking_with_installment_payment_cannot_be_cancelled(): void
    {
        [$user, $booking] = $this->createBooking();
        $this->actingAs($user)->post(route('bookings.payments.store', $booking), [
            'installment_number' => 1, 'amount' => 50000, 'payment_method' => 'cash',
        ]);

        $this->actingAs($user)->put(route('bookings.update', $booking), $this->updateData($booking, 'cancelled'))
            ->assertSessionHasErrors('status');
        $this->assertEquals('active', $booking->refresh()->status);
        $this->assertEquals(5, (float) Project::first()->sold_area_marla);
    }

    private function createBooking(): array
    {
        $this->seed();
        $user = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $agent = User::where('email', 'agent@abdullahtown.pk')->firstOrFail();
        $package = PlotPackage::where('name', '5 Marla')->firstOrFail();
        $this->actingAs($user)->post(route('bookings.store'), [
            'package_id' => $package->id, 'name' => 'Ali Khan', 'cnic' => '12345-1234567-9',
            'phone' => '03001234567', 'agent_id' => $agent->id,
            'booking_date' => '2026-07-20', 'payment_method' => 'cash',
        ]);

        return [$user, Booking::firstOrFail()];
    }

    private function updateData(Booking $booking, string $status): array
    {
        return [
            'name' => $booking->customer->name, 'father_name' => $booking->customer->father_name,
            'cnic' => $booking->customer->cnic, 'phone' => $booking->customer->phone,
            'email' => $booking->customer->email, 'address' => $booking->customer->address,
            'agent_id' => $booking->agent_id, 'booking_date' => $booking->booking_date->toDateString(),
            'status' => $status,
        ];
    }
}
