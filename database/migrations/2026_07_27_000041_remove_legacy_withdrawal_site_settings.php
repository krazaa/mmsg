<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('site_settings')
            ->where('key', 'like', 'withdrawal_%')
            ->delete();
    }

    public function down(): void
    {
        // Withdrawal policies now belong exclusively to withdrawal_settings.
    }
};
