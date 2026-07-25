<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('installment_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('installment_number');
            $table->date('due_date');

            $table->decimal('regular_amount', 15, 2)->default(0);
            $table->decimal('balloon_amount', 15, 2)->default(0);
            $table->decimal('total_due', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);

            // pending, partial, paid, overdue, waived, cancelled
            $table->string('status', 30)->default('pending');
            $table->timestamp('reminder_sent_at')->nullable()->after('status');
            $table->timestamps();

            $table->unique(
                ['booking_id', 'installment_number'],
                'installments_booking_number_unique'
            );

            $table->index(['booking_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index(['due_date', 'status', 'reminder_sent_at'], 'installments_reminder_lookup');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_schedules');
    }
};
