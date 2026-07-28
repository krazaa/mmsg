<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        DB::table('customers')->orderBy('id')->each(function (object $legacy): void {
            if (! $legacy->user_id || ! DB::table('users')->where('id', $legacy->user_id)->exists()) {
                return;
            }

            $user = DB::table('users')->where('id', $legacy->user_id)->first();
            $updates = [];

            foreach (['father_name', 'cnic', 'phone', 'address', 'referral_code', 'referral_agent_id'] as $field) {
                if (blank($user->{$field} ?? null) && filled($legacy->{$field} ?? null)) {
                    $updates[$field] = $legacy->{$field};
                }
            }

            if ($updates !== []) {
                DB::table('users')->where('id', $legacy->user_id)->update($updates);
            }
        });

        Schema::drop('customers');
    }

    public function down(): void
    {
        // The retired duplicate table must not be recreated.
    }
};
