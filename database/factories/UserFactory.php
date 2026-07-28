<?php

namespace Database\Factories;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $roleName = $user->role ?? 'staff';
            $permissions = in_array($roleName, ['super_admin', 'admin'], true)
                ? Permissions::all()
                : match ($roleName) {
                    'staff' => array_values(array_diff(Permissions::all(), [Permissions::MANAGE_STAFF, ...Permissions::customer()])),
                    'customer' => Permissions::customer(),
                    default => [],
                };
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            }
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
            $user->assignRole($role);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
