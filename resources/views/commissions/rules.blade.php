<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-5 px-3 sm:px-4">
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-indigo-950 to-violet-900 p-6 text-white shadow-2xl sm:p-8">
                <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-violet-500/20 blur-3xl"></div>
                <div class="absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl"></div>
                <div class="relative grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[.2em] text-indigo-100">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span> Package earnings
                        </div>
                        <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Commission rules</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-indigo-100/75">Set independent three-level commission rates for cash and installment bookings. The selected booking plan automatically decides which rates apply.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-5 py-4 backdrop-blur"><div class="text-[9px] font-black uppercase tracking-widest text-indigo-200">Project</div><div class="mt-1 max-w-40 truncate text-sm font-black">{{ $project->name }}</div></div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-5 py-4 backdrop-blur"><div class="text-[9px] font-black uppercase tracking-widest text-indigo-200">Packages</div><div class="mt-1 text-xl font-black">{{ $packages->count() }}</div></div>
                    </div>
                </div>
            </section>

            @if(session('success'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800"><span class="grid h-8 w-8 place-items-center rounded-full bg-emerald-500 text-white">✓</span>{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-800"><span class="grid h-8 w-8 place-items-center rounded-full bg-rose-500 text-white">!</span>{{ $errors->first() }}</div>
            @endif

            <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 sm:p-5">
                <div class="grid gap-4 lg:grid-cols-[minmax(240px,.6fr)_minmax(0,1.4fr)] lg:items-end">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-300">Select project
                        <select class="mt-2 w-full rounded-xl border-slate-300 bg-slate-50 py-3 text-sm font-bold text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white" onchange="window.location.href = '{{ route('commission-rules.index') }}?project=' + this.value">
                            @foreach($projects as $item)<option value="{{ $item->id }}" @selected($item->is($project))>{{ $item->name }} · {{ $item->packages->count() }} {{ Str::plural('package', $item->packages->count()) }}</option>@endforeach
                        </select>
                    </label>
                    <div>
                        <div class="mb-2 text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-300">Select package</div>
                        <div class="flex gap-2 overflow-x-auto pb-1">
                            @forelse($packages as $item)
                                <a href="{{ route('commission-rules.index', ['project' => $project, 'package' => $item]) }}" class="min-w-[150px] rounded-xl border px-4 py-2.5 transition {{ $package?->is($item) ? 'border-indigo-600 bg-indigo-600 text-white shadow-lg shadow-indigo-200 dark:shadow-none' : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-indigo-300 hover:bg-indigo-50 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200' }}">
                                    <b class="block truncate text-sm">{{ $item->name }}</b><span class="mt-0.5 block text-[10px] font-bold {{ $package?->is($item) ? 'text-indigo-100' : 'text-slate-400' }}">{{ number_format($item->size_marla, 2) }} marla</span>
                                </a>
                            @empty
                                <span class="rounded-xl bg-slate-100 px-4 py-3 text-sm text-slate-500">No packages available</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            @if($package)
                <form method="POST" action="{{ route('commission-rules.update', $package) }}" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-800">
                    @csrf @method('PUT')
                    <div class="border-b border-slate-100 p-5 dark:border-slate-700 sm:p-7">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                            <div><div class="text-[10px] font-black uppercase tracking-[.18em] text-indigo-600">{{ $project->name }}</div><h2 class="mt-1 text-2xl font-black text-slate-950 dark:text-white">{{ $package->name }}</h2><p class="mt-1 text-sm text-slate-500 dark:text-slate-300">{{ number_format($package->size_marla, 2) }} marla · Configure commission by booking plan</p></div>
                            <div class="flex flex-wrap gap-2"><span class="rounded-full bg-emerald-100 px-3 py-1.5 text-[10px] font-black uppercase tracking-wide text-emerald-700">Cash rates</span><span class="rounded-full bg-indigo-100 px-3 py-1.5 text-[10px] font-black uppercase tracking-wide text-indigo-700">Installment rates</span></div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-4 sm:p-5 xl:grid-cols-2 xl:items-start">
                        @foreach(['cash' => ['Cash commission', 'Used when the booking payment plan is Cash.', 'emerald'], 'installment' => ['Installment commission', 'Used when the booking payment plan is Installments.', 'indigo']] as $plan => [$label, $description, $color])
                            <section class="rounded-2xl border p-3 {{ $plan === 'cash' ? 'border-emerald-200 bg-emerald-50/30' : 'border-indigo-200 bg-indigo-50/30' }}">
                                <div class="mb-3 rounded-xl border px-3 py-2.5 {{ $plan === 'cash' ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-indigo-200 bg-indigo-50 text-indigo-900' }}">
                                    <div class="flex items-center gap-2.5"><span class="grid h-8 w-8 place-items-center rounded-lg text-sm font-black text-white {{ $plan === 'cash' ? 'bg-emerald-600' : 'bg-indigo-600' }}">%</span><div><h3 class="text-sm font-black">{{ $label }}</h3><p class="text-[10px] opacity-75">{{ $description }}</p></div></div>
                                </div>
                                <div class="grid gap-2.5 sm:grid-cols-3">
                                    @foreach([1, 2, 3] as $level)
                                        @php($rule = $package->commissionRules->where('payment_plan', $plan)->firstWhere('level', $level))
                                        <article class="rounded-xl border border-slate-200 bg-slate-50 p-3 transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-600 dark:bg-slate-900">
                                            <div class="flex items-center justify-between"><div><div class="text-[8px] font-black uppercase tracking-wider text-slate-400">Referral tier</div><h4 class="mt-0.5 text-sm font-black text-slate-900 dark:text-white">Level {{ $level }}</h4></div><span class="grid h-7 w-7 place-items-center rounded-lg text-[10px] font-black {{ $plan === 'cash' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }}">L{{ $level }}</span></div>
                                            <label class="mt-3 block text-[9px] font-black uppercase tracking-wide text-slate-500">Percentage<div class="relative mt-1.5"><input type="number" min="0" max="100" step="0.01" name="levels[{{ $plan }}][{{ $level }}]" value="{{ old('levels.'.$plan.'.'.$level, $rule?->percentage ?? 0) }}" required class="w-full rounded-lg border-slate-300 bg-white py-2 pr-8 text-sm font-black text-slate-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white"><span class="pointer-events-none absolute inset-y-0 right-2.5 flex items-center text-sm font-black text-slate-400">%</span></div></label>
                                            <label class="mt-2.5 flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-[10px] font-bold text-slate-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200"><span>Active rate</span><span><input type="hidden" name="active[{{ $plan }}][{{ $level }}]" value="0"><input type="checkbox" name="active[{{ $plan }}][{{ $level }}]" value="1" @checked(old('active.'.$plan.'.'.$level, $rule?->status ?? true)) class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"></span></label>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>

                    <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50 px-5 py-4 dark:border-slate-700 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Saving updates both Cash and Installment rates.</p>
                        <button class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-0.5 hover:shadow-xl dark:shadow-none">Save commission rates</button>
                    </div>
                </form>
            @else
                <div class="rounded-3xl border-2 border-dashed border-slate-200 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-800"><h3 class="font-black text-slate-900 dark:text-white">No package selected</h3><p class="mt-1 text-sm text-slate-500">Add a package to this project before setting commission levels.</p></div>
            @endif
        </div>
    </div>
</x-app-layout>
