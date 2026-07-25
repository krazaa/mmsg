<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $systemId = DB::table('users')->where('email', 'direct-sales@abdullahtown.pk')->value('id');
        if (! $systemId) {
            return;
        }

        $managementIds = DB::table('users')->whereIn('role', ['admin', 'staff'])->whereNull('referral_agent_id')->pluck('id');
        DB::table('users')->whereIn('id', $managementIds)->update(['referral_agent_id' => $systemId]);

        foreach ($managementIds as $userId) {
            DB::table('referrals')->updateOrInsert(
                ['user_id' => $userId],
                ['sponsor_id' => $systemId, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $systemId = DB::table('users')->where('email', 'direct-sales@abdullahtown.pk')->value('id');
        if (! $systemId) {
            return;
        }

        $managementIds = DB::table('users')->whereIn('role', ['admin', 'staff'])->where('referral_agent_id', $systemId)->pluck('id');
        DB::table('referrals')->whereIn('user_id', $managementIds)->where('sponsor_id', $systemId)->delete();
        DB::table('users')->whereIn('id', $managementIds)->update(['referral_agent_id' => null]);
    }
};
