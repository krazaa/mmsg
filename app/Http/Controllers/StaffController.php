<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staff = User::query()->whereIn('role', ['super_admin', 'admin', 'staff'])
            ->when($request->user()->role !== 'super_admin', fn ($query) => $query->where('role', '!=', 'super_admin'))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $search)->orWhere('email', 'like', $search)->orWhere('phone', 'like', $search)->orWhere('cnic', 'like', $search)->orWhere('referral_code', 'like', $search));
            })->latest()->paginate(25)->withQueryString();

        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data): void {
            $systemId = User::where('email', 'direct-sales@abdullahtown.pk')->firstOrFail()->id;
            $staff = User::create($data + [
                'email_verified_at' => now(),
                'referral_agent_id' => $systemId,
            ]);
            $staff->update(['referral_code' => $this->referralCode($staff)]);
            Referral::updateOrCreate(['user_id' => $staff->id], ['sponsor_id' => $systemId]);
        });

        return redirect()->route('staff.index')->with('success', 'Staff account created.');
    }

    public function edit(User $staff)
    {
        $this->ensureStaff($staff);
        $this->authorizeSuperAdminAccess(request(), $staff);

        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, User $staff)
    {
        $this->ensureStaff($staff);
        $this->authorizeSuperAdminAccess($request, $staff);
        $data = $this->validated($request, $staff);
        $this->protectAdministrator($request, $staff, $data['role'], (bool) $data['status']);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $staff->update($data);

        return redirect()->route('staff.index')->with('success', 'Staff account updated.');
    }

    public function destroy(User $staff)
    {
        $this->ensureStaff($staff);
        $this->authorizeSuperAdminAccess(request(), $staff);
        $this->protectAdministrator(request(), $staff, null, false, true);
        $staff->delete();

        return back()->with('success', 'Staff account deleted.');
    }

    private function validated(Request $request, ?User $staff = null): array
    {
        if (($request->input('role') === 'super_admin' || $staff?->role === 'super_admin')
            && $request->user()->role !== 'super_admin') {
            throw ValidationException::withMessages(['role' => 'Only the Super Admin can manage the Super Admin role.']);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'cnic' => ['required', 'string', 'max:15', Rule::unique('users')->ignore($staff)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($staff)],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:2000'],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'staff'])],
            'password' => [$staff ? 'nullable' : 'required', 'nullable', 'confirmed', Password::defaults()],
            'status' => ['required', 'boolean'],
        ]);
    }

    private function ensureStaff(User $staff): void
    {
        abort_unless(in_array($staff->role, ['super_admin', 'admin', 'staff'], true), 404);
    }

    private function referralCode(User $staff): string
    {
        $prefix = match ($staff->role) {
            'super_admin' => 'SADM',
            'admin' => 'ADM',
            default => 'STF',
        };
        $code = $prefix.'-'.str_pad((string) $staff->id, 6, '0', STR_PAD_LEFT);

        return User::where('referral_code', $code)->whereKeyNot($staff->id)->exists()
            ? $prefix.'-'.strtoupper(str()->random(8))
            : $code;
    }

    private function protectAdministrator(Request $request, User $staff, ?string $newRole, bool $active, bool $deleting = false): void
    {
        if ($staff->role === 'super_admin' && ($deleting || $newRole !== 'super_admin' || ! $active)) {
            throw ValidationException::withMessages(['role' => 'The Super Admin account cannot be demoted, deactivated, or deleted.']);
        }

        if ($staff->is($request->user()) && ($deleting || $newRole !== 'admin' || ! $active)) {
            throw ValidationException::withMessages(['role' => 'You cannot demote, deactivate, or delete your own administrator account.']);
        }

        $removingAdmin = $staff->role === 'admin' && ($deleting || $newRole !== 'admin' || ! $active);
        if ($removingAdmin && User::where('role', 'admin')->where('status', true)->count() <= 1) {
            throw ValidationException::withMessages(['role' => 'At least one active administrator account is required.']);
        }
    }

    private function authorizeSuperAdminAccess(Request $request, User $staff): void
    {
        abort_if($staff->role === 'super_admin' && $request->user()->role !== 'super_admin', 403);
    }
}
