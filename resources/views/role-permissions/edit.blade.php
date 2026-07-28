<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50 py-7 dark:from-slate-950 dark:via-slate-950 dark:to-indigo-950/30">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6">
            <section class="overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-indigo-950 to-violet-800 p-6 text-white shadow-2xl sm:p-8">
                <div class="text-[10px] font-black uppercase tracking-[.2em] text-indigo-300">Super Admin only</div>
                <h1 class="mt-2 text-3xl font-black">Roles & permissions</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-indigo-100">Choose which management areas Administrators and Staff can access. Super Admin always retains complete access.</p>
            </section>

            @if(session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
            @endif

            <div class="grid items-start gap-6 lg:grid-cols-2">
                @foreach(['admin' => 'Administrator', 'staff' => 'Staff'] as $roleName => $roleLabel)
                    @php($assigned = $roles->get($roleName)?->permissions->pluck('name') ?? collect())
                    <form method="POST" action="{{ route('role-permissions.update', $roleName) }}" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="role" value="{{ $roleName }}">
                        <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r {{ $roleName === 'admin' ? 'from-violet-50 to-white' : 'from-sky-50 to-white' }} p-5 dark:border-slate-800 dark:from-slate-800 dark:to-slate-900">
                            <div><h2 class="text-lg font-black text-slate-950 dark:text-white">{{ $roleLabel }}</h2><p class="mt-1 text-xs text-slate-500">{{ $assigned->count() }} permissions currently assigned</p></div>
                            <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase {{ $roleName === 'admin' ? 'bg-violet-100 text-violet-700' : 'bg-sky-100 text-sky-700' }}">{{ $roleLabel }}</span>
                        </div>
                        <div class="space-y-2 p-5">
                            @foreach($permissions as $permission)
                                @php($required = in_array($permission->name, [\App\Support\Permissions::ACCESS_MANAGEMENT, \App\Support\Permissions::VIEW_DASHBOARD], true))
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-indigo-300 hover:bg-indigo-50/40 dark:border-slate-700 dark:hover:bg-slate-800">
                                    @if($required)<input type="hidden" name="permissions[]" value="{{ $permission->name }}">@endif
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked($required || $assigned->contains($permission->name)) @disabled($required) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="min-w-0 flex-1"><b class="block text-sm text-slate-800 dark:text-white">{{ str($permission->name)->headline() }}</b>@if($required)<span class="text-[10px] text-slate-400">Required for management access</span>@endif</span>
                                    @if($required)<span class="rounded-full bg-slate-100 px-2 py-1 text-[9px] font-black uppercase text-slate-500">Required</span>@endif
                                </label>
                            @endforeach
                        </div>
                        <div class="border-t border-slate-100 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-800/50">
                            <button class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-indigo-200 transition hover:from-indigo-700 hover:to-violet-700 dark:shadow-none">Save {{ $roleLabel }} permissions</button>
                        </div>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
