<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roleId = DB::table('roles')->where('name', 'super_admin')->where('guard_name', 'web')->value('id');
        if (! $roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'super_admin',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('permissions')->pluck('id')->each(fn (int $permissionId) => DB::table('role_has_permissions')->insertOrIgnore([
            'permission_id' => $permissionId,
            'role_id' => $roleId,
        ]));

        $user = DB::table('users')->where('email', 'sadmin@mmsg.com')->first();
        if ($user) {
            DB::table('users')->where('id', $user->id)->update(['role' => 'super_admin', 'updated_at' => now()]);
            DB::table('model_has_roles')->where('model_type', User::class)->where('model_id', $user->id)->delete();
            DB::table('model_has_roles')->insert([
                'role_id' => $roleId,
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
        }
    }

    public function down(): void
    {
        $roleId = DB::table('roles')->where('name', 'super_admin')->where('guard_name', 'web')->value('id');
        if ($roleId) {
            DB::table('model_has_roles')->where('role_id', $roleId)->delete();
            DB::table('role_has_permissions')->where('role_id', $roleId)->delete();
            DB::table('roles')->where('id', $roleId)->delete();
        }

        DB::table('users')->where('email', 'sadmin@mmsg.com')->where('role', 'super_admin')->update(['role' => 'admin']);
    }
};
