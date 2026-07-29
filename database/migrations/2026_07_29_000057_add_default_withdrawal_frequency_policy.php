<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('frequency');
        });

        DB::table('withdrawal_settings')
            ->where('frequency', 'weekly')
            ->update(['is_default' => true]);

        DB::table('users')
            ->where('role', 'customer')
            ->update(['withdrawal_frequency' => 'weekly']);
    }

    public function down(): void
    {
        Schema::table('withdrawal_settings', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
