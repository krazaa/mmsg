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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 50)->unique();

            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('plot_id')->nullable()
                ->constrained('plots')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->unsignedBigInteger('package_id');

            $table->foreignId('agent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->date('booking_date');

            $table->decimal('total_price', 15, 2);
            $table->decimal('booking_amount', 15, 2)->default(0);
            $table->decimal('financed_amount', 15, 2);

            // pending, approved, active, completed, cancelled, defaulted
            $table->string('status', 30)->default('pending');
            $table->text('management_notes')->nullable();
            // Optimistic locking for concurrent updates.
            $table->unsignedInteger('version')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['agent_id', 'status']);
            $table->index('booking_date');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
