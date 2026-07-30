<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50/60 py-6 dark:from-slate-950 dark:via-slate-950 dark:to-slate-950/30 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6">
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-slate-950 to-blue-950 p-6 text-white shadow-2xl sm:p-8">
                <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl"></div>
                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-[.22em] text-blue-400">Customer management</div>
                        <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Customers</h1>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-blue-100/80">Manage customer accounts, property bookings, payments and referral relationships.</p>
                    </div>
                    <a href="{{ route('customers.create') }}" class="inline-flex w-fit items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-black text-blue-950 shadow-xl shadow-black/20 transition hover:-translate-y-0.5 hover:bg-blue-50">
                        <span class="text-lg leading-none">+</span> Add customer
                    </a>
                </div>
                <div class="relative mt-6 grid grid-cols-2 gap-2.5 lg:grid-cols-4">
                    @foreach([
                        ['All customers', $summary['total'], 'Total customer accounts', 'bg-blue-500'],
                        ['Active', $summary['active'], 'Currently active', 'bg-emerald-400'],
                        ['With bookings', $summary['booked'], 'Property customers', 'bg-cyan-400'],
                        ['Commission earners', $summary['commission'], 'Referral commissions', 'bg-amber-400'],
                    ] as [$label, $value, $hint, $accent])
                        <div class="rounded-2xl border border-white/10 bg-white/[.07] p-3.5 backdrop-blur sm:p-4">
                            <div class="flex items-center gap-2 text-[9px] font-black uppercase tracking-wider text-blue-200 sm:text-[10px]"><span class="h-2 w-2 shrink-0 rounded-full {{ $accent }}"></span>{{ $label }}</div>
                            <div class="mt-2 text-2xl font-black tabular-nums text-white sm:text-3xl">{{ number_format($value) }}</div>
                            <div class="mt-1 text-[10px] leading-4 text-blue-100/65 sm:text-xs">{{ $hint }}</div>
                        </div>
                    @endforeach
                </div>
            </section>

            @if(session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-800">{{ $errors->first() }}</div>
            @endif

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                <div class="border-b border-slate-100 p-4 dark:border-slate-800 sm:p-5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div class="inline-flex w-fit rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
                            <a href="{{ route('customers.index', array_filter(['search' => request('search'), 'referral_code' => request('referral_code')])) }}" class="rounded-lg px-4 py-2 text-xs font-black transition {{ request('type') !== 'commission' ? 'bg-slate-950 text-white shadow-sm dark:bg-blue-950' : 'text-slate-500 dark:text-slate-300' }}">All customers</a>
                            <a href="{{ route('customers.index', array_filter(['type' => 'commission', 'search' => request('search'), 'referral_code' => request('referral_code')])) }}" class="rounded-lg px-4 py-2 text-xs font-black transition {{ request('type') === 'commission' ? 'bg-slate-950 text-white shadow-sm dark:bg-blue-950' : 'text-slate-500 dark:text-slate-300' }}">Commission earners</a>
                        </div>

                        <form class="grid flex-1 gap-2 sm:grid-cols-[minmax(220px,1fr)_190px_auto] xl:max-w-3xl">
                            @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
                            <label class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 grid place-items-center text-slate-400">⌕</span>
                                <input name="search" value="{{ request('search') }}" placeholder="Search name, file, CNIC, phone or email" class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 pl-9 text-sm focus:border-blue-600 focus:ring-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            </label>
                            <input name="referral_code" value="{{ request('referral_code') }}" placeholder="Referral code" class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 font-mono text-sm uppercase focus:border-blue-600 focus:ring-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            <div class="flex gap-2">
                                <button class="flex-1 rounded-xl bg-blue-800 px-4 py-2.5 text-sm font-black text-white transition hover:bg-blue-900">Search</button>
                                @if(request()->filled('search') || request()->filled('referral_code'))
                                    <a href="{{ route('customers.index', array_filter(['type' => request('type')])) }}" class="grid place-items-center rounded-xl border border-slate-200 px-3 text-sm font-bold text-slate-500 dark:border-slate-700 dark:text-slate-300" title="Clear filters">×</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800 md:hidden">
                    @forelse($customers as $customer)
                        <article class="p-4">
                            <div class="flex items-start gap-3">
                                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-blue-700 to-blue-800 text-sm font-black text-white shadow-md">{{ str($customer->name)->substr(0, 1)->upper() }}</div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0"><a href="{{ route('customers.show', $customer) }}" class="block truncate font-black text-slate-900 dark:text-white">{{ $customer->name }}</a><div class="mt-0.5 truncate text-xs text-slate-500">{{ $customer->email ?: $customer->phone }}</div></div>
                                        <span class="shrink-0 rounded-full px-2 py-1 text-[9px] font-black uppercase {{ $customer->status ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $customer->status ? 'Active' : 'Inactive' }}</span>
                                    </div>
                                    <div class="mt-3 grid grid-cols-3 gap-2">
                                        <div class="rounded-xl bg-blue-50 p-2 dark:bg-slate-950/40"><span class="block text-[9px] font-bold uppercase text-blue-600">Bookings</span><b class="text-sm text-blue-950 dark:text-blue-200">{{ $customer->bookings_count }}</b></div>
                                        <div class="rounded-xl bg-emerald-50 p-2 dark:bg-emerald-950/40"><span class="block text-[9px] font-bold uppercase text-emerald-600">Payments</span><b class="text-sm text-emerald-950 dark:text-emerald-200">{{ $customer->payments_count }}</b></div>
                                        <div class="rounded-xl bg-amber-50 p-2 dark:bg-amber-950/40"><span class="block text-[9px] font-bold uppercase text-amber-500">Payable</span><b class="text-xs text-amber-900 dark:text-amber-200">Rs {{ number_format((float) $customer->payable_commission) }}</b></div>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between gap-2">
                                        <span class="truncate font-mono text-[10px] font-bold text-blue-800">{{ $customer->referral_code }}</span>
                                        <div class="flex gap-3 text-xs font-black"><a href="{{ route('customers.show', $customer) }}" class="text-blue-800">Manage</a><a href="{{ route('customers.edit', $customer) }}" class="text-amber-600">Edit</a></div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="p-12 text-center text-sm text-slate-500">No customers match your filters.</div>
                    @endforelse
                </div>

                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full min-w-[1050px] text-sm">
                        <thead class="bg-slate-50/80 text-left text-[10px] font-black uppercase tracking-widest text-slate-400 dark:bg-slate-800/60">
                            <tr><th class="px-5 py-4">Customer</th><th class="px-4">Contact</th><th class="px-4">Sponsor</th><th class="px-4 text-center">Activity</th><th class="px-4 text-right">Commission payable</th><th class="px-4">Status</th><th class="px-5 text-right">Actions</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($customers as $customer)
                                <tr class="group transition hover:bg-blue-50/40 dark:hover:bg-slate-800/50">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-blue-700 to-blue-800 text-sm font-black text-white">{{ str($customer->name)->substr(0, 1)->upper() }}</div>
                                            <div class="min-w-0">
                                                <a href="{{ route('customers.show', $customer) }}" class="block max-w-52 truncate font-black text-slate-900 hover:text-blue-800 dark:text-white">{{ $customer->name }}</a>
                                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                    @if($customer->file_no)<span class="rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[9px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">FILE {{ $customer->file_no }}</span>@endif
                                                    <span class="font-mono text-[9px] font-bold text-blue-800">{{ $customer->referral_code }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4"><div class="font-semibold text-slate-700 dark:text-slate-200">{{ $customer->phone ?: '—' }}</div><div class="mt-1 max-w-52 truncate text-xs text-slate-400">{{ $customer->email ?: 'No email address' }}</div></td>
                                    <td class="px-4 py-4"><div class="max-w-40 truncate font-semibold text-slate-700 dark:text-slate-200">{{ $customer->referral?->sponsor?->name ?? $customer->referralAgent?->name ?? 'Direct Sales' }}</div><div class="mt-1 text-[10px] text-slate-400">Referral sponsor</div></td>
                                    <td class="px-4 py-4">
                                        <div class="flex justify-center gap-2">
                                            <span class="rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs font-black text-blue-900 dark:bg-slate-950/50 dark:text-blue-400">{{ $customer->bookings_count }} <span class="font-medium text-blue-600">bookings</span></span>
                                            <span class="rounded-lg bg-emerald-50 px-2.5 py-1.5 text-xs font-black text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">{{ $customer->payments_count }} <span class="font-medium text-emerald-500">payments</span></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-right"><div class="font-black tabular-nums {{ (float) $customer->payable_commission > 0 ? 'text-amber-600' : 'text-slate-400' }}">Rs {{ number_format((float) $customer->payable_commission, 2) }}</div><div class="mt-1 text-[10px] text-slate-400">{{ $customer->commissions_count }} commission records</div></td>
                                    <td class="px-4 py-4"><span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $customer->status ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}"><span class="h-1.5 w-1.5 rounded-full {{ $customer->status ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>{{ $customer->status ? 'Active' : 'Inactive' }}</span></td>
                                    <td class="px-5 py-4"><div class="flex justify-end gap-2"><a href="{{ route('customers.show', $customer) }}" class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-black text-blue-900 transition hover:bg-blue-100 dark:bg-slate-950/50 dark:text-blue-400">Manage</a><a href="{{ route('customers.edit', $customer) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-black text-slate-500 transition hover:border-amber-300 hover:text-amber-600 dark:border-slate-700 dark:text-slate-300">Edit</a></div></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-16 text-center"><div class="text-3xl text-slate-300">⌕</div><div class="mt-2 font-bold text-slate-600 dark:text-slate-300">No customers found</div><div class="mt-1 text-xs text-slate-400">Try changing or clearing your filters.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($customers->hasPages())
                    <div class="border-t border-slate-100 px-4 py-4 dark:border-slate-800 sm:px-5">{{ $customers->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
