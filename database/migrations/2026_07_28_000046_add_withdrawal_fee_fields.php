<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->boolean('fee_enabled')->default(false)->after('maximum_amount');
            $table->string('fee_type', 12)->default('fixed')->after('fee_enabled');
            $table->decimal('fee_value', 15, 2)->default(0)->after('fee_type');
        });

        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->decimal('fee_amount', 15, 2)->default(0)->after('amount');
            $table->decimal('net_amount', 15, 2)->nullable()->after('fee_amount');
        });

        Schema::table('commission_payouts', function (Blueprint $table) {
            $table->decimal('fee_amount', 15, 2)->default(0)->after('amount');
            $table->decimal('net_amount', 15, 2)->nullable()->after('fee_amount');
        });
    }

    public function down(): void
    {
        Schema::table('commission_payouts', function (Blueprint $table) {
            $table->dropColumn(['fee_amount', 'net_amount']);
        });
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropColumn(['fee_amount', 'net_amount']);
        });
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->dropColumn(['fee_enabled', 'fee_type', 'fee_value']);
        });
    }
};
