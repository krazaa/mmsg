<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $email = config('app.super_admin.email');
        $password = config('app.super_admin.password');

        $user = User::updateOrCreate(['email' => $email], [
            'name' => config('app.super_admin.name'),
            'password' => $password,
            'role' => 'super_admin',
            'status' => true,
            'email_verified_at' => now(),
        ]);

        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $role->syncPermissions(Permission::where('guard_name', 'web')->get());
        $user->syncRoles($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info("Super Admin ready: {$email}");
    }
}
