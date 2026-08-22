<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_rules', function (Blueprint $table) {
            $table->string('calculation_type', 20)->default('percentage')->after('percentage');
            $table->decimal('fixed_amount', 15, 2)->default(0)->after('calculation_type');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->string('calculation_type', 20)->default('percentage')->after('percentage');
            $table->decimal('fixed_amount', 15, 2)->default(0)->after('calculation_type');
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn(['calculation_type', 'fixed_amount']);
        });

        Schema::table('commission_rules', function (Blueprint $table) {
            $table->dropColumn(['calculation_type', 'fixed_amount']);
        });
    }
};
