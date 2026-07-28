<?php

namespace App\Http\Controllers;

use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    private const EDITABLE_ROLES = ['admin', 'staff', 'booking', 'verification', 'withdrawal'];

    public function edit(): View
    {
        $roles = Role::query()
            ->whereIn('name', self::EDITABLE_ROLES)
            ->with('permissions')
            ->get()
            ->keyBy('name');

        $permissions = Permission::query()
            ->whereNotIn('name', [
                Permissions::USE_CUSTOMER_PORTAL,
                Permissions::CUSTOMER_BOOKINGS_CREATE,
                Permissions::CUSTOMER_BOOKINGS_STORE,
                Permissions::CUSTOMER_PAYMENTS,
                Permissions::CUSTOMER_PAYMENTS_RECEIPT,
                Permissions::CUSTOMER_PAYMENTS_STORE,
            ])
            ->orderBy('name')
            ->get();

        return view('role-permissions.edit', compact('roles', 'permissions'));
    }

    public function update(Request $request, string $role): RedirectResponse
    {
        $data = $request->validate([
            'role' => [Rule::in(self::EDITABLE_ROLES)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        abort_unless(in_array($role, self::EDITABLE_ROLES, true) && $role === ($data['role'] ?? null), 404);

        $allowed = Permission::query()
            ->whereNotIn('name', [
                Permissions::USE_CUSTOMER_PORTAL,
                Permissions::CUSTOMER_BOOKINGS_CREATE,
                Permissions::CUSTOMER_BOOKINGS_STORE,
                Permissions::CUSTOMER_PAYMENTS,
                Permissions::CUSTOMER_PAYMENTS_RECEIPT,
                Permissions::CUSTOMER_PAYMENTS_STORE,
            ])
            ->pluck('name');

        $selected = collect($data['permissions'] ?? [])
            ->intersect($allowed)
            ->push(Permissions::ACCESS_MANAGEMENT, Permissions::VIEW_DASHBOARD)
            ->unique()
            ->values();

        Role::findByName($role)->syncPermissions($selected);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', ucfirst($role).' role permissions updated.');
    }
}
