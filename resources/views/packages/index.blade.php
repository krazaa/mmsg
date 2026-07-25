<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-1 flex items-center gap-2 text-xs font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                    Inventory
                </div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Plot packages</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage pricing and payment plans for {{ $project->name }}.</p>
            </div>
            <a href="{{ route('packages.create', ['project' => $project->id]) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add package
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="GET" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Filter packages</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Refine the plans shown below.</p>
                    </div>
                    @if($status !== 'all')
                        <a href="{{ route('packages.index', ['project' => $project->id, 'sort' => $sort, 'direction' => $direction]) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            Clear filters
                        </a>
                    @endif
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Project</span>
                        <select name="project" onchange="this.form.submit()" class="mt-2 w-full rounded-xl border-gray-300 bg-white py-2.5 text-sm font-medium text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            @foreach($projects as $item)<option value="{{ $item->id }}" @selected($item->is($project))>{{ $item->name }}</option>@endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</span>
                        <select name="status" class="mt-2 w-full rounded-xl border-gray-300 bg-white py-2.5 text-sm font-medium text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <option value="all" @selected($status === 'all')>All statuses</option>
                            <option value="active" @selected($status === 'active')>Active only</option>
                            <option value="inactive" @selected($status === 'inactive')>Inactive only</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sort by</span>
                        <select name="sort" onchange="this.form.submit()" class="mt-2 w-full rounded-xl border-gray-300 bg-white py-2.5 text-sm font-medium text-gray-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            @foreach(['size'=>'Plot size','name'=>'Package name','total'=>'Total price','booking_amount'=>'First payment','monthly_amount'=>'Monthly installment','bookings'=>'Booking count','status'=>'Status'] as $value=>$label)<option value="{{ $value }}" @selected($sort===$value)>{{ $label }}</option>@endforeach
                        </select>
                    </label>
                    <div class="flex items-end">
                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700 dark:bg-indigo-600 dark:hover:bg-indigo-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.012L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                            Apply filters
                        </button>
                    </div>
                </div>
            </form>

            <div class="flex items-center justify-between gap-4">
                <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold text-gray-900 dark:text-white">{{ $packages->count() }}</span> {{ Str::plural('package', $packages->count()) }} found</p>
                <p class="hidden text-xs text-gray-400 sm:block">Sorted by {{ ['size'=>'plot size','name'=>'package name','total'=>'total price','booking_amount'=>'first payment','monthly_amount'=>'monthly installment','bookings'=>'booking count','status'=>'status'][$sort] }}</p>
            </div>

            <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3" aria-label="Packages">
                @forelse($packages as $package)
                    <article class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-700">
                        <div class="h-1 {{ $package->status ? 'bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                        <div class="border-b border-gray-100 p-5 dark:border-gray-700">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="truncate text-lg font-bold text-gray-900 dark:text-white">{{ $package->name }}</h3>
                                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold {{ $package->status ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-500/20 dark:bg-gray-700 dark:text-gray-300' }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $package->status ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                            {{ $package->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ number_format($package->size_marla, 2) }} marla · {{ $package->months }} months</p>
                                </div>
                                <div class="rounded-xl bg-indigo-50 px-3 py-2 text-right dark:bg-indigo-950/60">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400">Bookings</p>
                                    <p class="text-lg font-bold leading-tight text-indigo-700 dark:text-indigo-300">{{ $package->bookings_count }}</p>
                                </div>
                            </div>
                            <div class="mt-5">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total price</p>
                                <p class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Rs {{ number_format($package->total_price) }}</p>
                            </div>
                            @php
                                $upfrontShare = $package->total_price > 0 ? min(100, ($package->booking_amount / $package->total_price) * 100) : 0;
                                $installmentShare = $package->total_price > 0 ? min(100 - $upfrontShare, (($package->monthly_amount * $package->months) / $package->total_price) * 100) : 0;
                            @endphp
                            <div class="mt-4" title="Payment composition">
                                <div class="flex h-1.5 overflow-hidden rounded-full bg-amber-400">
                                    <span class="bg-indigo-500" style="width: {{ $upfrontShare }}%"></span>
                                    <span class="bg-sky-400" style="width: {{ $installmentShare }}%"></span>
                                </div>
                                <div class="mt-2 flex gap-4 text-[10px] font-medium text-gray-400">
                                    <span class="flex items-center gap-1"><i class="h-1.5 w-1.5 rounded-full bg-indigo-500"></i>Upfront</span>
                                    <span class="flex items-center gap-1"><i class="h-1.5 w-1.5 rounded-full bg-sky-400"></i>Installments</span>
                                    <span class="flex items-center gap-1"><i class="h-1.5 w-1.5 rounded-full bg-amber-400"></i>Balloons</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-px bg-gray-100 dark:bg-gray-700">
                            <div class="bg-gray-50/80 p-4 dark:bg-gray-800/80"><p class="text-xs text-gray-500 dark:text-gray-400">First payment</p><p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">Rs {{ number_format($package->booking_amount) }}</p></div>
                            <div class="bg-gray-50/80 p-4 dark:bg-gray-800/80"><p class="text-xs text-gray-500 dark:text-gray-400">Monthly installment</p><p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">Rs {{ number_format($package->monthly_amount) }}</p></div>
                        </div>

                        <div class="p-5">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Balloon payments</p>
                            <div class="mt-3 flex min-h-[28px] flex-wrap gap-2">
                                @forelse($package->balloonPayments() as $balloon)
                                    <span class="rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs font-medium text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">Month {{ $balloon['month'] }} · Rs {{ number_format($balloon['amount']) }}</span>
                                @empty
                                    <span class="text-sm text-gray-400">No balloon payments</span>
                                @endforelse
                            </div>
                            <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-700">
                                <a href="{{ route('packages.edit', $package) }}" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125 16.862 4.487M18 14v4.75A2.25 2.25 0 0 1 15.75 21h-10.5A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                    Edit
                                </a>
                                @if($package->bookings_count > 0)
                                    <span class="cursor-not-allowed px-3 py-2 text-xs font-medium text-gray-400" title="Packages with bookings cannot be deleted">In use</span>
                                @else
                                    <form method="POST" action="{{ route('packages.destroy', $package) }}" onsubmit="return confirm('Delete this package? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-1.327L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-white px-6 py-16 text-center md:col-span-2 xl:col-span-3 dark:border-gray-700 dark:bg-gray-800">
                        <div class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25M21 7.5v9l-9 5.25m0-9L3 7.5m9 5.25v9M3 7.5v9l9 5.25"/></svg></div>
                        <h3 class="mt-4 font-bold text-gray-900 dark:text-white">{{ $status !== 'all' ? 'No matching packages' : 'No packages yet' }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $status !== 'all' ? 'Try changing or clearing the current filters.' : 'Create the first pricing plan for '.$project->name.'.' }}</p>
                        @if($status !== 'all')
                            <a href="{{ route('packages.index', ['project' => $project->id]) }}" class="mt-5 inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">Clear filters</a>
                        @else
                            <a href="{{ route('packages.create', ['project' => $project->id]) }}" class="mt-5 inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Add package</a>
                        @endif
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
