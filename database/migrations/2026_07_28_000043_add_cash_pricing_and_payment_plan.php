<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plot_packages', function (Blueprint $table) {
            $table->decimal('cash_price', 15, 2)->nullable()->after('size_marla');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_plan', 20)->default('installment')->after('booking_date');
            $table->index('payment_plan');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['payment_plan']);
            $table->dropColumn('payment_plan');
        });

        Schema::table('plot_packages', function (Blueprint $table) {
            $table->dropColumn('cash_price');
        });
    }
};
