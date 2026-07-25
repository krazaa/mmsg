<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('permissions')->insertOrIgnore([
            'name' => 'manage staff', 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $permissionId = DB::table('permissions')->where('name', 'manage staff')->where('guard_name', 'web')->value('id');
        $adminRoleId = DB::table('roles')->where('name', 'admin')->where('guard_name', 'web')->value('id');
        if ($permissionId && $adminRoleId) {
            DB::table('role_has_permissions')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $adminRoleId]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('name', 'manage staff')->where('guard_name', 'web')->value('id');
        if ($permissionId) {
            DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
