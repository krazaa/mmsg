<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Existing installment rates were previously used for both the first
     * payment and later installments. Preserve them as the new first-payment
     * defaults, while retaining the original installment rates.
     */
    public function up(): void
    {
        $now = now();

        DB::table('commission_rules')
            ->where('payment_plan', 'installment')
            ->orderBy('id')
            ->get()
            ->each(function ($rule) use ($now) {
                DB::table('commission_rules')->updateOrInsert(
                    [
                        'package_id' => $rule->package_id,
                        'payment_plan' => 'first_payment',
                        'level' => $rule->level,
                    ],
                    [
                        'percentage' => $rule->percentage,
                        'status' => $rule->status,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            });
    }

    public function down(): void
    {
        DB::table('commission_rules')->where('payment_plan', 'first_payment')->delete();
    }
};
