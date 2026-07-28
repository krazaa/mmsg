<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $agentIds = DB::table('users')->where('role', 'agent')->pluck('id');
        DB::table('users')->whereIn('id', $agentIds)->update(['role' => 'customer']);

        $customerRoleId = DB::table('roles')->where('name', 'customer')->where('guard_name', 'web')->value('id');
        $agentRoleId = DB::table('roles')->where('name', 'agent')->where('guard_name', 'web')->value('id');

        if ($customerRoleId) {
            foreach ($agentIds as $userId) {
                DB::table('model_has_roles')->updateOrInsert([
                    'role_id' => $customerRoleId,
                    'model_type' => User::class,
                    'model_id' => $userId,
                ]);
            }
        }

        if ($agentRoleId) {
            DB::table('model_has_roles')->where('role_id', $agentRoleId)->delete();
            DB::table('role_has_permissions')->where('role_id', $agentRoleId)->delete();
            DB::table('roles')->where('id', $agentRoleId)->delete();
        }

        $obsoletePermissionIds = DB::table('permissions')
            ->whereIn('name', ['manage agents', 'use agent portal'])
            ->where('guard_name', 'web')
            ->pluck('id');

        DB::table('model_has_permissions')->whereIn('permission_id', $obsoletePermissionIds)->delete();
        DB::table('role_has_permissions')->whereIn('permission_id', $obsoletePermissionIds)->delete();
        DB::table('permissions')->whereIn('id', $obsoletePermissionIds)->delete();
    }

    public function down(): void
    {
        // Customers and referral agents are intentionally one account type.
    }
};
