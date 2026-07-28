@csrf
@isset($staff) @method('PUT') @endisset

<div class="grid gap-5 sm:grid-cols-2">
    <label class="block text-sm font-medium text-gray-700">Referral code
        <input value="{{ $staff->referral_code ?? 'Generated automatically after creation' }}" disabled class="mt-1 w-full rounded-md border-gray-300 bg-gray-100 font-mono text-gray-600">
        <span class="mt-1 block text-xs font-normal text-gray-500">The system assigns this code automatically.</span>
    </label>
    <label class="block text-sm font-medium text-gray-700">Referral agent
        <input value="System (Direct Sales)" disabled class="mt-1 w-full rounded-md border-gray-300 bg-gray-100 text-gray-600">
        <span class="mt-1 block text-xs font-normal text-gray-500">Assigned automatically when the account is created.</span>
    </label>
    <label class="block text-sm font-medium text-gray-700">Name
        <input name="name" value="{{ old('name', $staff->name ?? '') }}" required class="mt-1 w-full rounded-md border-gray-300">
    </label>
    <label class="block text-sm font-medium text-gray-700">Father name
        <input name="father_name" value="{{ old('father_name', $staff->father_name ?? '') }}" required class="mt-1 w-full rounded-md border-gray-300">
    </label>
    <label class="block text-sm font-medium text-gray-700">CNIC
        <input name="cnic" value="{{ old('cnic', $staff->cnic ?? '') }}" required maxlength="15" inputmode="numeric" placeholder="12345-1234567-1" class="mt-1 w-full rounded-md border-gray-300">
    </label>
    <label class="block text-sm font-medium text-gray-700">Email
        <input type="email" name="email" value="{{ old('email', $staff->email ?? '') }}" required class="mt-1 w-full rounded-md border-gray-300">
    </label>
    <label class="block text-sm font-medium text-gray-700">Role
        <select name="role" required class="mt-1 w-full rounded-md border-gray-300">
            <option value="staff" @selected(old('role', $staff->role ?? 'staff') === 'staff')>Staff</option>
            <option value="admin" @selected(old('role', $staff->role ?? 'staff') === 'admin')>Administrator</option>
            <option value="booking" @selected(old('role', $staff->role ?? 'staff') === 'booking')>Booking</option>
            <option value="verification" @selected(old('role', $staff->role ?? 'staff') === 'verification')>Verification</option>
            <option value="withdrawal" @selected(old('role', $staff->role ?? 'staff') === 'withdrawal')>Withdrawal</option>
            @if(auth()->user()->hasRole('super_admin') || ($staff->role ?? null) === 'super_admin')
                <option value="super_admin" @selected(old('role', $staff->role ?? 'staff') === 'super_admin')>Super Admin</option>
            @endif
        </select>
        <span class="mt-1 block text-xs font-normal text-gray-500">Booking, Verification and Withdrawal roles only access their assigned workflow.</span>
    </label>
    <label class="block text-sm font-medium text-gray-700">Phone
        <input name="phone" value="{{ old('phone', $staff->phone ?? '') }}" required autocomplete="tel" class="mt-1 w-full rounded-md border-gray-300">
    </label>
    <label class="block text-sm font-medium text-gray-700 sm:col-span-2">Address
        <textarea name="address" rows="3" required class="mt-1 w-full rounded-md border-gray-300">{{ old('address', $staff->address ?? '') }}</textarea>
    </label>
    <label class="block text-sm font-medium text-gray-700">{{ isset($staff) ? 'New password (optional)' : 'Password' }}
        <input type="password" name="password" {{ isset($staff) ? '' : 'required' }} autocomplete="new-password" class="mt-1 w-full rounded-md border-gray-300">
    </label>
    <label class="block text-sm font-medium text-gray-700">Confirm password
        <input type="password" name="password_confirmation" {{ isset($staff) ? '' : 'required' }} autocomplete="new-password" class="mt-1 w-full rounded-md border-gray-300">
    </label>
    <label class="flex items-center gap-2 self-end pb-3 text-sm font-medium text-gray-700">
        <input type="hidden" name="status" value="0">
        <input type="checkbox" name="status" value="1" @checked(old('status', $staff->status ?? true)) class="rounded border-gray-300 text-indigo-600">
        Active account
    </label>
</div>

<div class="mt-6 flex gap-3">
    <button class="rounded-md bg-indigo-600 px-5 py-2.5 font-semibold text-white">{{ isset($staff) ? 'Save changes' : 'Create staff account' }}</button>
    <a href="{{ route('staff.index') }}" class="rounded-md border px-5 py-2.5 text-gray-700">Cancel</a>
</div>
