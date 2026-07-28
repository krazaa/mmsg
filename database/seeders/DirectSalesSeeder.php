<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DirectSalesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::customer() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $role->syncPermissions(Permissions::customer());

        $user = User::query()
            ->where('referral_code', 'DIRECT-SALES')
            ->orWhereIn('email', ['direct-sales@mmsgroup.pk', 'direct-sales@abdullahtown.pk'])
            ->first();

        if (! $user) {
            $user = new User(['password' => Str::random(64)]);
        }

        $user->forceFill([
            'name' => 'Direct Sales',
            'email' => 'direct-sales@mmsgroup.pk',
            'role' => 'customer',
            'referral_code' => 'DIRECT-SALES',
            'referral_agent_id' => null,
            'status' => true,
            'email_verified_at' => $user->email_verified_at ?: now(),
        ])->save();

        $user->syncRoles($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info("Direct Sales ready: {$user->email}");
    }
}
