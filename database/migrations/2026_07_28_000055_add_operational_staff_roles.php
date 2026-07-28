<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissionIds = [];

        foreach ([
            'access management',
            'view dashboard',
            'manage bookings',
            'manage payments',
            'manage withdrawals',
        ] as $name) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $name,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $permissionIds[$name] = DB::table('permissions')
                ->where('name', $name)
                ->where('guard_name', 'web')
                ->value('id');
        }

        foreach ([
            'booking' => ['access management', 'view dashboard', 'manage bookings'],
            'verification' => ['access management', 'view dashboard', 'manage payments'],
            'withdrawal' => ['access management', 'view dashboard', 'manage withdrawals'],
        ] as $roleName => $permissions) {
            DB::table('roles')->insertOrIgnore([
                'name' => $roleName,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $roleId = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->value('id');

            foreach ($permissions as $permission) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permissionIds[$permission],
                    'role_id' => $roleId,
                ]);
            }
        }

        $withdrawalPermissionId = $permissionIds['manage withdrawals'];
        $managementRoleIds = DB::table('roles')
            ->whereIn('name', ['super_admin', 'admin', 'staff'])
            ->where('guard_name', 'web')
            ->pluck('id');

        foreach ($managementRoleIds as $roleId) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $withdrawalPermissionId,
                'role_id' => $roleId,
            ]);
        }
    }

    public function down(): void
    {
        // Operational roles may already be assigned to users, so rollback is
        // intentionally non-destructive.
    }
};
