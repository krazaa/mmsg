<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-black uppercase tracking-[.18em] text-indigo-600 dark:text-indigo-400">My account</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950 dark:text-white">Customer profile</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">View and maintain your complete account information.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-50/70 via-slate-50 to-violet-50/60 py-8 dark:from-slate-950 dark:via-slate-950 dark:to-indigo-950/30">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6">
            @if($user->role === 'customer')
                @php($initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->join(''))
                <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-700 via-violet-700 to-slate-950 p-6 text-white shadow-xl sm:p-8">
                    <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-fuchsia-400/20 blur-3xl"></div>
                    <div class="absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-cyan-400/10 blur-3xl"></div>
                    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
                        <div class="grid h-20 w-20 shrink-0 place-items-center rounded-3xl border border-white/30 bg-white/15 text-2xl font-black shadow-xl ring-4 ring-white/10 backdrop-blur">{{ $initials ?: 'CU' }}</div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="truncate text-2xl font-black">{{ $user->name }}</h3>
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $user->status ? 'bg-emerald-400/20 text-emerald-200 ring-1 ring-emerald-300/30' : 'bg-rose-400/20 text-rose-200 ring-1 ring-rose-300/30' }}">{{ $user->status ? 'Active' : 'Inactive' }}</span>
                            </div>
                            <p class="mt-1 text-sm text-indigo-100">{{ $user->email }}</p>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                <span class="rounded-lg bg-white/10 px-3 py-1.5"><b class="text-indigo-200">File:</b> {{ $user->file_no ?: 'Not assigned' }}</span>
                                <span class="rounded-lg border border-violet-300/20 bg-violet-400/15 px-3 py-1.5"><b class="text-violet-200">Referral Code:</b> <strong class="ml-1 font-mono tracking-wider text-white">{{ $user->referral_code ?: 'Not assigned' }}</strong></span>
                                <span class="rounded-lg bg-white/10 px-3 py-1.5"><b class="text-indigo-200">Member since:</b> {{ $user->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8 dark:border-slate-800 dark:bg-slate-900">
                    @include('profile.partials.update-profile-information-form')
                </section>

                <aside class="space-y-6">
                    @include('profile.partials.manage-passkeys')

                    @if($user->role === 'customer')
                        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-violet-50 px-5 py-4 dark:border-slate-800 dark:from-indigo-950/50 dark:to-violet-950/40">
                                <h3 class="font-black text-slate-900 dark:text-white">Account overview</h3>
                                <p class="mt-0.5 text-xs text-slate-500">System-managed customer details</p>
                            </div>
                            <dl class="divide-y divide-slate-100 px-5 dark:divide-slate-800">
                                @foreach([
                                    ['File number', $user->file_no ?: 'Not assigned'],
                                    ['Referral Code', $user->referral_code ?: 'Not assigned'],
                                    ['Sponsor name', $user->customer?->referralAgent?->name ?? 'Direct Sales'],
                                    ['Account status', $user->status ? 'Active' : 'Inactive'],
                                    ['Email status', $user->hasVerifiedEmail() ? 'Verified' : 'Not verified'],
                                    ['Membership date', $user->created_at->format('d M Y')],
                                ] as [$label, $value])
                                    <div class="flex items-center justify-between gap-4 py-3.5">
                                        <dt class="text-xs font-semibold text-slate-500">{{ $label }}</dt>
                                        <dd class="text-right text-sm font-black text-slate-900 dark:text-white">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </section>
                    @endif

                    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        @include('profile.partials.update-password-form')
                    </section>

                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
