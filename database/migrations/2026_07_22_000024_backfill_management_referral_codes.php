<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereIn('role', ['admin', 'staff'])->whereNull('referral_code')->orderBy('id')->each(function (object $user): void {
            $prefix = $user->role === 'admin' ? 'ADM' : 'STF';
            DB::table('users')->where('id', $user->id)->update([
                'referral_code' => $prefix.'-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function down(): void
    {
        DB::table('users')->whereIn('role', ['admin', 'staff'])
            ->where(fn ($query) => $query->where('referral_code', 'like', 'ADM-%')->orWhere('referral_code', 'like', 'STF-%'))
            ->update(['referral_code' => null]);
    }
};
