<x-app-layout>
    <x-slot name="header"><div class="flex flex-wrap items-center justify-between gap-4"><div><h2 class="text-xl font-black text-gray-900">Plots</h2><p class="text-sm text-gray-500">Manage individual plot inventory, availability, and pricing.</p></div><a href="{{ route('plots.create') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white">+ Add plot</a></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl space-y-5 px-4">
        @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">{{ $errors->first() }}</div>@endif
        <form method="GET" x-data="{ project: '{{ request('project') }}', block: '{{ request('block') }}' }" class="flex flex-nowrap items-center gap-3 overflow-x-auto rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200"><input name="search" value="{{ request('search') }}" placeholder="Plot number..." class="min-w-48 flex-1 rounded-xl border-gray-300"><select name="project" x-model="project" @change="block=''" class="min-w-52 flex-1 rounded-xl border-gray-300"><option value="">All projects</option>@foreach($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach</select><select name="block" x-model="block" :disabled="!project" class="min-w-52 flex-1 rounded-xl border-gray-300 disabled:bg-gray-100"><option value="" x-text="project ? 'All blocks' : 'Select a project first'"></option>@foreach($blocks as $block)<option x-show="project === '{{ $block->project_id }}'" value="{{ $block->id }}">{{ $block->name }}</option>@endforeach</select><select name="status" class="min-w-44 flex-1 rounded-xl border-gray-300"><option value="">All statuses</option>@foreach(['available','held','reserved','booked','sold','blocked','disputed'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="shrink-0 rounded-xl bg-slate-900 px-6 py-2.5 font-bold text-white">Filter</button><a href="{{ route('plots.index') }}" class="shrink-0 rounded-xl border border-gray-300 px-5 py-2.5 font-bold text-gray-700 hover:bg-gray-50">Reset</a></form>
        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <div><h3 class="font-black text-gray-900">Plot inventory</h3><p class="text-xs text-gray-500">{{ $plots->total() }} plots found</p></div>
                <span class="rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-black text-indigo-700">Live inventory</span>
            </div>
            <div class="max-h-[68vh] overflow-auto">
                <table class="w-full min-w-[850px] text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-200/95 text-left text-sm font-black uppercase tracking-wide text-slate-950 shadow-sm backdrop-blur [&_th]:!font-black [&_th]:[font-weight:900]">
                        <tr><th class="px-5 py-3.5">Plot</th><th class="px-5 py-3.5">Project</th><th class="px-5 py-3.5">Block</th><th class="px-5 py-3.5">Size</th><th class="px-5 py-3.5">Category</th><th class="px-5 py-3.5">Status</th><th class="px-5 py-3.5 text-right">Actions</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($plots as $plot)
                            @php
                                $statusClass = match($plot->status->value) {
                                    'available' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
                                    'held', 'reserved' => 'bg-amber-50 text-amber-700 ring-amber-600/10',
                                    'booked', 'sold' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/10',
                                    'blocked', 'disputed' => 'bg-rose-50 text-rose-700 ring-rose-600/10',
                                    default => 'bg-gray-100 text-gray-700 ring-gray-600/10',
                                };
                            @endphp
                            <tr class="odd:bg-white even:bg-slate-50/40 transition hover:bg-indigo-50/50">
                                <td class="px-5 py-4"><span class="inline-flex min-w-14 justify-center rounded-lg bg-slate-900 px-2.5 py-1.5 font-mono text-xs font-black text-white">{{ $plot->plot_number }}</span></td>
                                <td class="px-5 py-4 font-bold text-slate-800">{{ $plot->project?->name ?? 'Deleted project' }}</td>
                                <td class="px-5 py-4"><span class="rounded-lg bg-slate-100 px-2.5 py-1 font-semibold text-slate-700">{{ $plot->block?->name ?? 'Deleted block' }}</span></td>
                                <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-700">{{ number_format($plot->size_marla,2) }} <span class="text-xs font-normal text-slate-400">marla</span></td>
                                <td class="px-5 py-4 capitalize text-slate-600">{{ ucfirst($plot->category) }}</td>
                                <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black capitalize ring-1 ring-inset {{ $statusClass }}">{{ ucfirst($plot->status->value) }}</span></td>
                                <td class="px-5 py-4">
                                    @if($plot->allotment_exists)
                                        <div class="text-right"><span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-black text-emerald-700 ring-1 ring-emerald-600/10">✓ Allotted</span></div>
                                    @else
                                        <div class="flex justify-end gap-2"><a href="{{ route('plots.edit',$plot) }}" class="rounded-lg border border-indigo-200 bg-white px-3 py-2 text-xs font-black text-indigo-700 hover:bg-indigo-50">Edit</a><form method="POST" action="{{ route('plots.destroy',$plot) }}" onsubmit="return confirm('Delete this plot?')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-black text-rose-700 hover:bg-rose-50">Delete</button></form></div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-16 text-center"><span class="block text-3xl text-slate-300">⌗</span><b class="mt-2 block text-slate-600">No plots found</b><span class="text-sm text-slate-400">Try changing or resetting the filters.</span></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($plots->hasPages())<div class="border-t border-gray-100 p-4">{{ $plots->links() }}</div>@endif
        </div>
    </div></div>
</x-app-layout>
