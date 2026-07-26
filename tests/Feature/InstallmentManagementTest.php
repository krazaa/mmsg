<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\InstallmentSchedules;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_installment_can_be_changed_and_booking_total_is_recalculated(): void
    {
        [$user, $booking, $installment] = $this->records();

        $this->actingAs($user)->put(route('installments.update', $installment), [
            'due_date' => '2026-09-01', 'regular_amount' => 60000,
            'balloon_amount' => 10000, 'status' => 'pending',
        ])->assertRedirect(route('installments.index', ['booking' => $booking->id]));

        $this->assertEquals(70000, (float) $installment->refresh()->total_due);
        $this->assertEquals(420000, (float) $booking->refresh()->total_price);
        $this->assertEquals(70000, (float) $booking->financed_amount);
    }

    public function test_installment_cannot_be_reduced_below_amount_paid(): void
    {
        [$user, $booking, $installment] = $this->records();
        $installment->update(['paid_amount' => 40000, 'status' => 'partial']);

        $this->actingAs($user)->put(route('installments.update', $installment), [
            'due_date' => '2026-09-01', 'regular_amount' => 20000,
            'balloon_amount' => 0, 'status' => 'pending',
        ])->assertSessionHasErrors('regular_amount');
    }

    public function test_installment_list_links_each_row_to_its_booking(): void
    {
        [$user, $booking] = $this->records();

        $this->actingAs($user)->get(route('installments.index'))
            ->assertOk()
            ->assertSee($booking->booking_number)
            ->assertSee(route('bookings.show', $booking));
    }

    public function test_future_pending_installment_is_shown_as_upcoming(): void
    {
        [$user, $booking, $installment] = $this->records();
        $installment->update(['due_date' => today()->addMonth(), 'status' => 'pending']);
        $laterInstallment = InstallmentSchedules::create([
            'booking_id' => $booking->id,
            'installment_number' => 2,
            'due_date' => today()->addMonths(2),
            'regular_amount' => 50000,
            'balloon_amount' => 0,
            'total_due' => 50000,
        ]);

        $this->actingAs($user)->get(route('installments.index', ['status' => 'upcoming']))
            ->assertOk()
            ->assertSee($booking->booking_number)
            ->assertSee('Upcoming')
            ->assertSee(route('installments.edit', $installment))
            ->assertDontSee(route('installments.edit', $laterInstallment));
    }

    public function test_only_one_upcoming_installment_is_shown_per_customer(): void
    {
        [$user, $booking, $installment] = $this->records();
        $installment->update(['due_date' => today()->addMonth(), 'status' => 'pending']);
        $otherBooking = $booking->replicate();
        $otherBooking->booking_number = 'B-2';
        $otherBooking->save();
        $otherInstallment = InstallmentSchedules::create([
            'booking_id' => $otherBooking->id,
            'installment_number' => 1,
            'due_date' => today()->addMonths(2),
            'regular_amount' => 50000,
            'balloon_amount' => 0,
            'total_due' => 50000,
        ]);

        $this->actingAs($user)->get(route('installments.index', ['status' => 'upcoming']))
            ->assertOk()
            ->assertSee(route('installments.edit', $installment))
            ->assertDontSee(route('installments.edit', $otherInstallment));
    }

    public function test_default_list_hides_later_installments_for_the_same_customer(): void
    {
        [$user, $booking, $installment] = $this->records();
        $installment->update(['due_date' => today()->addMonth(), 'status' => 'pending']);
        $laterInstallment = InstallmentSchedules::create([
            'booking_id' => $booking->id,
            'installment_number' => 2,
            'due_date' => today()->addMonths(2),
            'regular_amount' => 50000,
            'balloon_amount' => 0,
            'total_due' => 50000,
        ]);

        $this->actingAs($user)->get(route('installments.index'))
            ->assertOk()
            ->assertSee(route('installments.edit', $installment))
            ->assertDontSee(route('installments.edit', $laterInstallment));
    }

    private function records(): array
    {
        $user = User::factory()->create();
        $project = Project::create(['name' => 'Test', 'slug' => 'test', 'location' => 'Test', 'gross_area_marla' => 1000, 'saleable_area_marla' => 1000]);
        $package = PlotPackage::create(['project_id' => $project->id, 'name' => '5 Marla', 'size_marla' => 5, 'booking_amount' => 350000, 'months' => 36, 'monthly_amount' => 50000, 'month_12_balloon' => 150000, 'month_24_balloon' => 250000, 'month_36_balloon' => 350000]);
        $customer = Customer::create(['name' => 'Ali', 'cnic' => '12345-1234567-1', 'phone' => '0300']);
        $booking = Booking::create(['booking_number' => 'B-1', 'project_id' => $project->id, 'package_id' => $package->id, 'customer_id' => $customer->id, 'booking_date' => today(), 'total_price' => 400000, 'booking_amount' => 350000, 'financed_amount' => 50000, 'status' => 'active']);
        $installment = InstallmentSchedules::create(['booking_id' => $booking->id, 'installment_number' => 1, 'due_date' => '2026-08-01', 'regular_amount' => 50000, 'balloon_amount' => 0, 'total_due' => 50000]);

        return [$user, $booking, $installment];
    }
}
