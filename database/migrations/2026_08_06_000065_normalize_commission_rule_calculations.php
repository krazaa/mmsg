<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('commission_rules')
            ->where('calculation_type', 'fixed')
            ->update(['percentage' => 0]);

        DB::table('commission_rules')
            ->where('calculation_type', 'percentage')
            ->update(['fixed_amount' => 0]);
    }

    public function down(): void
    {
        // Removed values cannot be recovered reliably.
    }
};
