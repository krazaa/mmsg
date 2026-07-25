<x-app-layout>
    <x-slot name="header"><div><h2 class="text-xl font-black text-gray-900">Plot allotments</h2><p class="text-sm text-gray-500">Assign individual inventory plots to active bookings.</p></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl space-y-6 px-4">
        @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">{{ $errors->first() }}</div>@endif

        <div class="flex flex-wrap items-end justify-between gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
            <form method="GET"><label class="text-sm font-bold text-gray-700">Project<select name="project" onchange="this.form.submit()" class="mt-1 block min-w-64 rounded-xl border-gray-300"><option value="">All projects</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected(request('project')==$project->id)>{{ $project->name }}</option>@endforeach</select></label></form>
            <div class="flex gap-3"><div class="rounded-xl bg-indigo-50 px-4 py-3 text-sm text-indigo-800"><b>{{ $bookings->count() }}</b> awaiting allotment</div><div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><b>{{ $plots->count() }}</b> available plots</div></div>
        </div>

        <details class="group rounded-2xl bg-white shadow-sm ring-1 ring-gray-200" @if($errors->hasAny(['project_id','block_id','plot_number','size_marla','category','base_price','premium_amount'])) open @endif>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-6">
                <div><h3 class="text-lg font-black">Add plot manually</h3><p class="mt-1 text-sm text-gray-500">Create a single available inventory plot without importing a plotting plan.</p></div>
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-50 text-xl font-black text-indigo-700 transition group-open:rotate-45">+</span>
            </summary>
            <form method="POST" action="{{ route('inventory.plots.store') }}" x-data="{ project: '{{ old('project_id', request('project')) }}', block: '{{ old('block_id') }}' }" class="grid gap-4 border-t border-gray-100 p-6 sm:grid-cols-2 lg:grid-cols-4">@csrf
                <label class="text-sm font-semibold text-gray-700">Project<select name="project_id" x-model="project" @change="block = ''" required class="mt-1.5 w-full rounded-xl border-gray-300"><option value="">Select project</option>@foreach($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold text-gray-700">Block<select name="block_id" x-model="block" :disabled="!project" required class="mt-1.5 w-full rounded-xl border-gray-300 disabled:cursor-not-allowed disabled:bg-gray-100"><option value="" x-text="project ? 'Select block' : 'Select a project first'"></option>@foreach($blocks as $block)<option x-show="project === '{{ $block->project_id }}'" value="{{ $block->id }}">{{ $block->name }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold text-gray-700">Plot number<input name="plot_number" value="{{ old('plot_number') }}" maxlength="50" required placeholder="e.g. 24-A" class="mt-1.5 w-full rounded-xl border-gray-300"></label>
                <label class="text-sm font-semibold text-gray-700">Size (marla)<input type="number" name="size_marla" value="{{ old('size_marla') }}" min="0.01" step="0.01" required class="mt-1.5 w-full rounded-xl border-gray-300"></label>
                <label class="text-sm font-semibold text-gray-700">Category<select name="category" required class="mt-1.5 w-full rounded-xl border-gray-300">@foreach(['residential'=>'Residential','commercial'=>'Commercial','farmhouse'=>'Farmhouse'] as $value=>$label)<option value="{{ $value }}" @selected(old('category')===$value)>{{ $label }}</option>@endforeach</select></label>
                <label class="text-sm font-semibold text-gray-700">Base price (Rs)<input type="number" name="base_price" value="{{ old('base_price',0) }}" min="0" step="0.01" required class="mt-1.5 w-full rounded-xl border-gray-300"></label>
                <label class="text-sm font-semibold text-gray-700">Premium (Rs)<input type="number" name="premium_amount" value="{{ old('premium_amount',0) }}" min="0" step="0.01" class="mt-1.5 w-full rounded-xl border-gray-300"></label>
                <div class="flex items-end"><button class="w-full rounded-xl bg-slate-950 px-5 py-3 font-black text-white hover:bg-indigo-700">Add to inventory</button></div>
            </form>
        </details>

        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
            <div class="mb-5"><h3 class="text-lg font-black">New allotment</h3><p class="text-sm text-gray-500">Only active bookings and matching available plots can be selected.</p></div>
            @if($bookings->isEmpty())
                <div class="rounded-xl bg-gray-50 p-5 text-sm text-gray-500">No active bookings are awaiting plot allotment.</div>
            @elseif($plots->isEmpty())
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">No available plot inventory found. Add blocks and plots before making an allotment.</div>
            @else
                <form method="POST" action="{{ route('allotments.store') }}" data-allotment-selects class="grid gap-4 lg:grid-cols-3">@csrf
                    <label class="text-sm font-semibold text-gray-700">Project<select data-project-select required class="mt-1.5 w-full rounded-xl border-gray-300"><option value=""></option>@foreach($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach</select></label>
                    <label class="text-sm font-semibold text-gray-700">Active booking<select name="booking_id" data-booking-select required disabled class="mt-1.5 w-full rounded-xl border-gray-300"><option value=""></option>@foreach($bookings as $booking)<option data-project="{{ $booking->project_id }}" value="{{ $booking->id }}">{{ $booking->booking_number }} — {{ $booking->customer->name }} — {{ $booking->package->name }}</option>@endforeach</select></label>
                    <label class="text-sm font-semibold text-gray-700">
                        Available matching plot
                        <select name="plot_id" data-plot-select required disabled class="mt-1.5 w-full rounded-xl border-gray-300 disabled:bg-gray-100">
                            <option value=""></option>
                            @foreach($bookings as $eligibleBooking)
                                @php
                                    $matchingPlots = $plots->where('project_id', $eligibleBooking->project_id)
                                        ->filter(fn ($plot) => abs((float) $plot->size_marla - (float) $eligibleBooking->package->size_marla) < 0.001);
                                @endphp
                                @foreach($matchingPlots as $plot)
                                    <option data-booking="{{ $eligibleBooking->id }}" value="{{ $plot->id }}">
                                        {{ $plot->block->name }} / Plot {{ $plot->plot_number }} — {{ number_format($plot->size_marla, 2) }} marla
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-semibold text-gray-700">Allotment date<input type="date" name="allotment_date" value="{{ old('allotment_date',today()->toDateString()) }}" required class="mt-1.5 w-full rounded-xl border-gray-300"></label>
                    <label class="text-sm font-semibold text-gray-700 lg:col-span-2">Notes<input name="notes" value="{{ old('notes') }}" maxlength="2000" placeholder="Optional allotment notes" class="mt-1.5 w-full rounded-xl border-gray-300"></label>
                    <div class="flex items-end"><button class="w-full rounded-xl bg-indigo-600 px-5 py-3 font-black text-white hover:bg-indigo-700">Allot plot</button></div>
                </form>
            @endif
        </section>

        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200"><div class="border-b border-gray-100 p-5"><h3 class="font-black">Allotment history</h3></div><div class="overflow-x-auto"><table class="w-full min-w-[900px] text-sm"><thead class="bg-gray-50 text-left text-xs uppercase text-gray-500"><tr><th class="px-5 py-3">Allotment</th><th class="px-5 py-3">Customer / booking</th><th class="px-5 py-3">Project</th><th class="px-5 py-3">Allotted plot</th><th class="px-5 py-3">Date</th><th class="px-5 py-3">By</th></tr></thead><tbody class="divide-y divide-gray-100">@forelse($allotments as $allotment)<tr><td class="px-5 py-4 font-mono font-bold text-indigo-700">{{ $allotment->allotment_number }}</td><td class="px-5 py-4"><b class="block">{{ $allotment->booking->customer->name }}</b><a href="{{ route('bookings.show',$allotment->booking) }}" class="font-mono text-xs text-indigo-600">{{ $allotment->booking->booking_number }}</a></td><td class="px-5 py-4">{{ $allotment->booking->project->name }}<span class="block text-xs text-gray-500">{{ $allotment->booking->package->name }}</span></td><td class="px-5 py-4"><b>{{ $allotment->plot->block->name }} / Plot {{ $allotment->plot->plot_number }}</b><span class="block text-xs text-gray-500">{{ number_format($allotment->plot->size_marla,2) }} marla</span></td><td class="px-5 py-4">{{ $allotment->allotment_date->format('d M Y') }}</td><td class="px-5 py-4">{{ $allotment->allottedBy?->name ?? 'System' }}</td></tr>@empty<tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No plots have been allotted yet.</td></tr>@endforelse</tbody></table></div>@if($allotments->hasPages())<div class="border-t p-4">{{ $allotments->links() }}</div>@endif</section>
    </div></div>
</x-app-layout>
