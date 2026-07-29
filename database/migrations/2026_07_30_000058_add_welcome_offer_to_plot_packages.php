<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plot_packages', function (Blueprint $table) {
            $table->text('welcome_offer')->nullable()->after('payment_plan_options');
        });
    }

    public function down(): void
    {
        Schema::table('plot_packages', function (Blueprint $table) {
            $table->dropColumn('welcome_offer');
        });
    }
};
