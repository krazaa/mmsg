<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->unsignedTinyInteger('level');
            $table->decimal('percentage', 5, 2);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['package_id', 'level']);
        });

        Schema::create('commission_payouts', function (Blueprint $table) {
            $table->id();
            $table->string('payout_number', 50)->unique();
            $table->foreignId('agent_id')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 30);
            $table->string('transaction_reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->index(['agent_id', 'paid_at']);
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('beneficiary_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('commission_payout_id')->nullable()->constrained('commission_payouts')->nullOnDelete();
            $table->unsignedTinyInteger('level');
            $table->decimal('percentage', 5, 2);
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('earned');
            $table->timestamps();

            $table->unique(['payment_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('commission_payouts');
        Schema::dropIfExists('commission_rules');
    }
};
