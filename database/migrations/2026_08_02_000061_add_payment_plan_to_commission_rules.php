<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_rules', function (Blueprint $table) {
            $table->string('payment_plan', 20)->default('installment')->after('package_id');
            $table->dropUnique('commission_rules_package_id_level_unique');
            $table->unique(['package_id', 'payment_plan', 'level'], 'commission_rules_package_plan_level_unique');
        });

        $now = now();
        DB::table('commission_rules')->orderBy('id')->get()->each(function ($rule) use ($now) {
            DB::table('commission_rules')->insert([
                'package_id' => $rule->package_id,
                'payment_plan' => 'cash',
                'level' => $rule->level,
                'percentage' => $rule->percentage,
                'status' => $rule->status,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        DB::table('commission_rules')->where('payment_plan', 'cash')->delete();

        Schema::table('commission_rules', function (Blueprint $table) {
            $table->dropUnique('commission_rules_package_plan_level_unique');
            $table->dropColumn('payment_plan');
            $table->unique(['package_id', 'level']);
        });
    }
};
