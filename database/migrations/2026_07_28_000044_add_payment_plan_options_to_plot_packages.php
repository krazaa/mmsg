<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plot_packages', function (Blueprint $table) {
            $table->string('payment_plan_options', 20)->default('both')->after('cash_price');
        });

        DB::table('plot_packages')->whereNull('cash_price')->update(['payment_plan_options' => 'installment']);
    }

    public function down(): void
    {
        Schema::table('plot_packages', function (Blueprint $table) {
            $table->dropColumn('payment_plan_options');
        });
    }
};
