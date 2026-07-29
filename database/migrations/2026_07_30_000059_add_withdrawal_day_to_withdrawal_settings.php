<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('withdrawal_day')->nullable()->after('request_limit');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->dropColumn('withdrawal_day');
        });
    }
};
