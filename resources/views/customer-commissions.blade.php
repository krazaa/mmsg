<x-app-layout>
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-indigo-50 py-6 dark:from-slate-950 dark:via-slate-950 dark:to-emerald-950/30 sm:py-8">
        <div class="pointer-events-none absolute -left-32 top-32 h-96 w-96 rounded-full bg-emerald-200/35 blur-3xl dark:bg-emerald-900/20"></div>
        <div class="pointer-events-none absolute -right-32 top-[34rem] h-96 w-96 rounded-full bg-violet-200/35 blur-3xl dark:bg-violet-900/20"></div>
        <div class="relative w-full space-y-6 px-3 sm:px-5 lg:px-6">
            @if($portalPreview ?? false)<div class="flex flex-col gap-3 rounded-xl border border-emerald-300 bg-emerald-950 px-4 py-3 text-white shadow-xl sm:flex-row sm:items-center sm:justify-between"><div><b class="text-sm">Admin commission preview</b><p class="text-xs text-emerald-200">Viewing {{ $customer->name }}'s commission history in read-only mode.</p></div><div class="flex gap-2"><a href="{{ route('customers.team', $customer) }}" class="rounded-lg bg-emerald-700 px-4 py-2 text-xs font-black text-white">View team</a><a href="{{ route('customers.show', $customer) }}" class="rounded-lg bg-white px-4 py-2 text-xs font-black text-emerald-800">Exit preview</a></div></div>@endif

            @php($commissionInitials = collect(preg_split('/\s+/', trim($customer->name)))->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->join(''))
            <section class="customer-theme-account-hero relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-indigo-900 to-violet-700 text-white shadow-2xl shadow-indigo-300/60 dark:shadow-none">
                <span class="sr-only">Commission history</span>
                <div class="customer-theme-blur absolute -right-20 -top-24 -z-10 h-80 w-80 rounded-full bg-fuchsia-400/25 blur-3xl"></div>
                <div class="customer-theme-blur absolute -bottom-32 left-1/3 -z-10 h-72 w-72 rounded-full bg-cyan-300/20 blur-3xl"></div>
                <div class="absolute inset-0 -z-10 opacity-[.07]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:23px 23px"></div>
                <div class="grid lg:grid-cols-[1.2fr_.8fr]">
                    <div class="p-6 sm:p-7">
                        <div class="flex items-center gap-4">
                            <div class="customer-theme-avatar relative grid h-16 w-16 shrink-0 place-items-center rounded-2xl border border-white/30 bg-gradient-to-br from-fuchsia-500 to-indigo-700 text-lg font-black shadow-xl ring-4 ring-white/10">
                                {{ $commissionInitials ?: 'CU' }}
                                <span class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full border-[3px] border-indigo-900 bg-emerald-400"></span>
                            </div>
                            <div class="min-w-0">
                                <div class="customer-theme-active-badge inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-300/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-emerald-200"><span class="customer-theme-active-dot h-1.5 w-1.5 rounded-full bg-emerald-300"></span>Referral earnings active</div>
                                <h1 class="mt-2 truncate text-2xl font-black tracking-tight sm:text-4xl">{{ $customer->name }}</h1>
                                <p class="mt-1 text-xs text-indigo-200">{{ $customer->file_no ? 'File '.$customer->file_no.' · ' : '' }}{{ $customer->referral_code ?: 'Customer account' }}</p>
                            </div>
                        </div>
                        <p class="mt-4 max-w-xl text-sm leading-6 text-indigo-100">Track every earning generated across your three-level referral team, from verified customer payments to completed payouts.</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @unless($portalPreview ?? false)<a href="{{ route('customer.withdrawals.index') }}" class="customer-light-action inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-black text-indigo-800 shadow-xl transition hover:-translate-y-0.5 hover:bg-indigo-50">Withdraw commission <span>↗</span></a>@endunless
                            <a href="{{ ($portalPreview ?? false) ? route('customers.team', $customer) : route('customer.team') }}" class="inline-flex items-center rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-bold text-white backdrop-blur transition hover:bg-white/20">View team</a>
                        </div>
                    </div>
                    <div class="customer-theme-hero-panel border-t border-white/10 bg-white/[.07] p-6 backdrop-blur-md lg:border-l lg:border-t-0 sm:p-7">
                        <div class="flex items-center justify-between"><div><div class="text-[10px] font-black uppercase tracking-[.2em] text-indigo-200">Commission snapshot</div><div class="mt-1 text-sm font-bold text-white">Your referral wallet</div></div><span class="grid h-10 w-10 place-items-center rounded-xl border border-white/15 bg-white/10 text-lg">%</span></div>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4"><div class="text-[9px] font-bold uppercase tracking-wider text-amber-200">Payable commission</div><div class="mt-1 truncate text-lg font-black text-amber-300">Rs {{ number_format($summary?->payable ?? 0, 2) }}</div><p class="mt-1 text-[10px] text-white/60">Ready to withdraw</p></div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4"><div class="text-[9px] font-bold uppercase tracking-wider text-emerald-200">Paid commission</div><div class="mt-1 truncate text-lg font-black text-emerald-300">Rs {{ number_format($summary?->paid ?? 0, 2) }}</div><p class="mt-1 text-[10px] text-white/60">Successfully paid</p></div>
                        </div>
                        <div class="mt-3 rounded-2xl border border-white/10 bg-gradient-to-r from-white/10 to-fuchsia-300/10 p-4">
                            <div class="flex items-end justify-between gap-3"><div><div class="text-[9px] font-bold uppercase tracking-[.16em] text-indigo-200">Lifetime earnings</div><div class="mt-1 text-xl font-black">Rs {{ number_format($summary?->lifetime ?? 0, 2) }}</div></div><span class="grid h-9 w-9 place-items-center rounded-xl bg-white/10 text-violet-200">★</span></div>
                        </div>
                    </div>
                </div>
            </section>

            <form method="GET" action="{{ ($portalPreview ?? false) ? route('customers.commissions', $customer) : route('customer.commissions') }}" class="rounded-3xl border border-indigo-100 bg-white/95 p-5 shadow-xl shadow-slate-200/60 backdrop-blur dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4 flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-100 text-indigo-700">⌕</span><div><h2 class="font-black text-slate-950 dark:text-white">Find a transaction</h2><p class="text-xs text-slate-500 dark:text-slate-300">Filter earnings by member, project, level or payout status.</p></div></div>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1.5fr_1fr_.7fr_.8fr_auto_auto] xl:items-end">
                    <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Search team or reference<input name="search" value="{{ request('search') }}" placeholder="Name, referral code, receipt or booking" class="mt-1.5 w-full rounded-xl border-slate-300 py-3 text-sm font-normal normal-case tracking-normal dark:border-slate-600 dark:bg-slate-800 dark:text-white"></label>
                    <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Project<select name="project" class="mt-1.5 w-full rounded-xl border-slate-300 py-3 text-sm font-normal normal-case dark:border-slate-600 dark:bg-slate-800 dark:text-white"><option value="">All projects</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected((string) request('project') === (string) $project->id)>{{ $project->name }}</option>@endforeach</select></label>
                    <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Level<select name="level" class="mt-1.5 w-full rounded-xl border-slate-300 py-3 text-sm font-normal normal-case dark:border-slate-600 dark:bg-slate-800 dark:text-white"><option value="">All levels</option>@foreach([1,2,3] as $level)<option value="{{ $level }}" @selected((string) request('level') === (string) $level)>Level {{ $level }}</option>@endforeach</select></label>
                    <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Status<select name="status" class="mt-1.5 w-full rounded-xl border-slate-300 py-3 text-sm font-normal normal-case dark:border-slate-600 dark:bg-slate-800 dark:text-white"><option value="">All statuses</option>@foreach(['earned' => 'Earned', 'paid' => 'Paid out', 'reversed' => 'Reversed'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white shadow-md hover:bg-indigo-700">Apply filters</button><a href="{{ ($portalPreview ?? false) ? route('customers.commissions', $customer) : route('customer.commissions') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-bold text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300">Clear</a>
                </div>
            </form>
            @include('customer-commission-table')
        </div>
    </div>
</x-app-layout>
