<?php

use App\Contracts\InstallmentScheduleGenerator;
use App\Models\Booking;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Booking::query()->where('status', 'pending')->update(['status' => 'approved']);

        Booking::query()
            ->where('status', 'approved')
            ->where('payment_plan', 'installment')
            ->whereDoesntHave('installments')
            ->eachById(fn (Booking $booking) => app(InstallmentScheduleGenerator::class)->generate($booking));
    }

    public function down(): void
    {
        // Existing bookings must not be returned to an approval-dependent state.
    }
};
