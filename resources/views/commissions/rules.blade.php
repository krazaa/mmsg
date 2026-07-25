<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Package commission levels</h2>
            <p class="mt-1 text-sm text-gray-500">Set three-level commission rates for each project package.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-5 px-4">
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">{{ $errors->first() }}</div>
            @endif

            <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                <div class="grid items-end gap-4 md:grid-cols-[minmax(0,1fr)_auto]">
                    <label class="text-sm font-semibold text-gray-700">
                        Project
                        <select
                            class="mt-2 w-full rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            onchange="window.location.href = '{{ route('commission-rules.index') }}?project=' + this.value"
                        >
                            @foreach($projects as $item)
                                <option value="{{ $item->id }}" @selected($item->is($project))>
                                    {{ $item->name }} ({{ $item->packages->count() }} {{ Str::plural('package', $item->packages->count()) }})
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <div class="rounded-xl bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                        <span class="font-bold">{{ $packages->count() }}</span> packages in {{ $project->name }}
                    </div>
                </div>

                @if($packages->isNotEmpty())
                    <div class="mt-5 border-t border-gray-100 pt-5">
                        <p class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">Packages for {{ $project->name }}</p>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($packages as $item)
                                <a
                                    href="{{ route('commission-rules.index', ['project' => $project, 'package' => $item]) }}"
                                    @class([
                                        'group rounded-xl border p-4 transition',
                                        'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-100' => $package?->is($item),
                                        'border-gray-200 hover:border-indigo-300 hover:bg-gray-50' => ! $package?->is($item),
                                    ])
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $item->name }}</div>
                                            <div class="mt-1 text-sm text-gray-500">{{ number_format($item->size_marla, 2) }} marla</div>
                                        </div>
                                        @if($package?->is($item))
                                            <span class="rounded-full bg-indigo-600 px-2.5 py-1 text-xs font-bold text-white">Selected</span>
                                        @endif
                                    </div>
                                    <div class="mt-3 flex gap-2">
                                        @foreach([1, 2, 3] as $level)
                                            @php($itemRule = $item->commissionRules->firstWhere('level', $level))
                                            <span class="rounded-md bg-white px-2 py-1 text-xs text-gray-600 ring-1 ring-gray-200">L{{ $level }}: {{ number_format((float) ($itemRule?->percentage ?? 0), 2) }}%</span>
                                        @endforeach
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>

            @if($package)
                <form method="POST" action="{{ route('commission-rules.update', $package) }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    @csrf
                    @method('PUT')
                    <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-indigo-600">{{ $project->name }}</p>
                            <h3 class="mt-1 text-xl font-bold text-gray-900">{{ $package->name }} · {{ number_format($package->size_marla, 2) }} marla</h3>
                            <p class="mt-1 text-sm text-gray-500">Commission is calculated on every verified payment for this package.</p>
                        </div>
                        <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-gray-600">Three levels</span>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach([1, 2, 3] as $level)
                            @php($rule = $package->commissionRules->firstWhere('level', $level))
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                                <div class="mb-4 flex items-center justify-between">
                                    <div class="font-bold text-gray-900">Level {{ $level }}</div>
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">{{ $level }}</span>
                                </div>
                                <label class="text-sm font-medium text-gray-700">
                                    Commission percentage
                                    <div class="relative mt-2">
                                        <input type="number" min="0" max="100" step="0.01" name="levels[{{ $level }}]" value="{{ old('levels.'.$level, $rule?->percentage ?? 0) }}" required class="w-full rounded-xl border-gray-300 pr-10 focus:border-indigo-500 focus:ring-indigo-500">
                                        <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center font-bold text-gray-400">%</span>
                                    </div>
                                </label>
                                <label class="mt-4 flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <input type="hidden" name="active[{{ $level }}]" value="0">
                                    <input type="checkbox" name="active[{{ $level }}]" value="1" @checked(old('active.'.$level, $rule?->status ?? true)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    Active for this package
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <button class="mt-6 rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-700">Save commission levels</button>
                </form>
            @else
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center shadow-sm">
                    <div class="text-lg font-bold text-gray-800">No packages in {{ $project->name }}</div>
                    <p class="mt-1 text-sm text-gray-500">Add a package to this project before setting commission levels.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
