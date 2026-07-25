<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $customer = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $agent = Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);

        $superAdmin->syncPermissions(Permissions::all());
        $admin->syncPermissions(Permissions::all());
        $staff->syncPermissions([Permissions::ACCESS_MANAGEMENT, Permissions::VIEW_DASHBOARD, Permissions::MANAGE_PROJECTS, Permissions::MANAGE_PACKAGES, Permissions::MANAGE_CUSTOMERS, Permissions::MANAGE_AGENTS, Permissions::MANAGE_BOOKINGS, Permissions::MANAGE_PAYMENTS, Permissions::MANAGE_INSTALLMENTS, Permissions::MANAGE_ALLOTMENTS, Permissions::MANAGE_COMMISSIONS, Permissions::MANAGE_NOTIFICATIONS, Permissions::VIEW_ACTIVITY_LOG]);
        $customer->syncPermissions(Permissions::customer());
        $agent->syncPermissions([Permissions::VIEW_DASHBOARD, Permissions::USE_AGENT_PORTAL]);

        User::where('email', 'sadmin@mmsg.com')->update(['role' => 'super_admin']);
        User::query()->select(['id', 'role'])->orderBy('id')->each(fn (User $user) => $user->syncRoles($user->role));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
