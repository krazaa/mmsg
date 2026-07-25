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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 50)->unique();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('installment_schedule_id')
                ->nullable()
                ->constrained('installment_schedules')
                ->nullOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2);

            // cash, bank_transfer, cheque, card, easypaisa, jazzcash
            $table->string('payment_method', 30);

            $table->string('transaction_reference', 100)->nullable();
            $table->string('proof_path')->nullable();
            $table->string('proof_original_name')->nullable();
            $table->dateTime('payment_date');

            // pending, verified, rejected, reversed
            $table->string('status', 30)->default('pending');

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['booking_id', 'status']);
            $table->index(['customer_id', 'payment_date']);
            $table->index(['payment_method', 'status']);
            $table->index('transaction_reference');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
