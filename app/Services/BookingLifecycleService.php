<?php

namespace App\Services;

use App\Contracts\BookingLifecycleManager;
use App\Contracts\InstallmentScheduleGenerator;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingLifecycleService implements BookingLifecycleManager
{
    public function __construct(private readonly InstallmentScheduleGenerator $schedules) {}

    public function update(Booking $booking, array $data): bool
    {
        return DB::transaction(function () use ($booking, $data): bool {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $project = Project::query()->lockForUpdate()->findOrFail($locked->project_id);
            $package = $locked->package;
            $oldStatus = $locked->status;
            $newStatus = $data['status'];

            if ($oldStatus === 'pending' && ! in_array($newStatus, ['pending', 'approved', 'cancelled'], true)) {
                throw ValidationException::withMessages(['status' => 'A pending request must be approved or cancelled first.']);
            }

            if ($oldStatus === 'approved' && $newStatus === 'active') {
                if (! $locked->payments()->whereNull('installment_schedule_id')->where('status', 'verified')->exists()) {
                    throw ValidationException::withMessages(['status' => 'The first payment must be verified before this booking can become active.']);
                }
                $project->decrement('reserved_area_marla', (float) $package->size_marla);
                $project->increment('sold_area_marla', (float) $package->size_marla);
            }

            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $this->cancel($locked, $project, (float) $package->size_marla, $oldStatus);
            }

            if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                $this->reactivate($locked, $project, (float) $package->size_marla);
            }

            if ($newStatus === 'completed' && $locked->installments()->whereNotIn('status', ['paid', 'waived', 'cancelled'])->exists()) {
                throw ValidationException::withMessages(['status' => 'All installments must be paid or waived before completing the booking.']);
            }

            $locked->customer->update(collect($data)->only(['name', 'father_name', 'cnic', 'phone', 'email', 'address'])->all());
            $locked->update(['agent_id' => $data['agent_id'] ?? null, 'booking_date' => $data['booking_date'], 'status' => $newStatus]);

            if ($oldStatus === 'pending' && $newStatus === 'approved' && $locked->payment_plan === 'installment' && ! $locked->installments()->exists()) {
                $this->schedules->generate($locked->refresh());
            }

            return $oldStatus !== 'approved' && $newStatus === 'approved';
        });
    }

    private function cancel(Booking $booking, Project $project, float $plotSize, string $oldStatus): void
    {
        if (Commission::where('booking_id', $booking->id)->where('status', 'paid')->exists()) {
            throw ValidationException::withMessages(['status' => 'This booking has paid-out commission and cannot be cancelled until the payout is resolved.']);
        }
        $installmentPayments = max(0, (float) $booking->payments()->where('status', 'verified')->sum('amount') - (float) $booking->booking_amount);
        if ($installmentPayments > 0) {
            throw ValidationException::withMessages(['status' => 'A booking with installment payments cannot be cancelled until its payments are reversed or refunded.']);
        }

        in_array($oldStatus, ['pending', 'approved'], true)
            ? $project->decrement('reserved_area_marla', $plotSize)
            : $project->decrement('sold_area_marla', $plotSize);

        $booking->installments()->whereIn('status', ['pending', 'partial'])->update(['status' => 'cancelled']);
        Commission::where('booking_id', $booking->id)->update(['status' => 'reversed', 'updated_by' => auth()->id()]);
    }

    private function reactivate(Booking $booking, Project $project, float $plotSize): void
    {
        if ($project->available_area_marla < $plotSize) {
            throw ValidationException::withMessages(['status' => 'This booking cannot be reactivated because sufficient project inventory is unavailable.']);
        }
        $project->increment('sold_area_marla', $plotSize);
        $booking->installments()->where('status', 'cancelled')->update(['status' => 'pending']);
        Commission::where('booking_id', $booking->id)->update(['status' => 'earned', 'updated_by' => auth()->id()]);
    }
}
