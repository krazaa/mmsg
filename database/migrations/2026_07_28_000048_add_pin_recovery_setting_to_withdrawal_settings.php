<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->boolean('pin_recovery_enabled')->default(true)->after('fee_value');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->dropColumn('pin_recovery_enabled');
        });
    }
};
