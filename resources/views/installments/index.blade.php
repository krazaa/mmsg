<x-app-layout>
    <div class="py-5 sm:py-8"><div class="mx-auto max-w-7xl px-3 sm:px-4 space-y-5">
        <section class="admin-command-card admin-command-summary p-5 text-white shadow-2xl sm:p-8">
            <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full"></div>
            <div class="absolute -bottom-24 left-1/3 h-56 w-56 rounded-full"></div>
            <div class="relative"><div class="admin-command-badge inline-flex rounded-lg px-3 py-1.5 text-[10px] font-black uppercase tracking-[.22em]">Payment schedule management</div><h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Installments</h1><p class="mt-2 max-w-xl text-sm leading-6 text-violet-100/80">Monitor upcoming payments, overdue schedules and customer payment progress.</p></div>
            <div class="admin-command-stat-grid mt-4 grid grid-cols-2 gap-2.5 lg:grid-cols-4">
                @foreach([
                    ['All installments', $summary['total'], 'Total scheduled records', 'bg-violet-400', false],
                    ['Upcoming', $summary['upcoming'], 'Next customer payments', 'bg-cyan-400', false],
                    ['Overdue', $summary['overdue'], 'Require follow-up', 'bg-rose-400', false],
                    ['Amount received', $summary['received'], 'Paid toward schedules', 'bg-emerald-400', true],
                ] as [$label, $value, $hint, $accent, $currency])
                    <div class="admin-command-stat rounded-2xl border p-3.5 backdrop-blur sm:p-4">
                        <div class="flex items-center gap-2 text-[9px] font-black uppercase tracking-wider text-violet-200 sm:text-[10px]"><span class="h-2 w-2 shrink-0 rounded-full {{ $accent }}"></span>{{ $label }}</div>
                        <div class="mt-2 break-words text-xl font-black tabular-nums text-white sm:text-3xl">{{ $currency ? 'Rs '.number_format($value) : number_format($value) }}</div>
                        <div class="mt-1 text-[10px] leading-4 text-violet-100/65 sm:text-xs">{{ $hint }}</div>
                    </div>
                @endforeach
            </div>
        </section>
        @if(session('success'))<div class="rounded-lg bg-green-100 p-4 text-green-800">{{ session('success') }}</div>@endif
        <form method="GET" class="grid gap-4 rounded-xl bg-white p-4 shadow sm:grid-cols-2 sm:p-5 lg:grid-cols-4 dark:bg-slate-800 dark:text-white">
            <label class="text-sm font-medium">Project<select name="project" class="mt-1 w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-900"><option value="">All projects</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected(request('project')==$project->id)>{{ $project->name }}</option>@endforeach</select></label>
            <label class="text-sm font-medium">Booking<select name="booking" data-booking-filter-select class="mt-1 w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-900"><option value="">All bookings</option>@foreach($bookings as $booking)<option value="{{ $booking->id }}" @selected(request('booking')==$booking->id)>{{ $booking->booking_number }} — {{ $booking->customer->name }}</option>@endforeach</select></label>
            <label class="text-sm font-medium">Status<select name="status" class="mt-1 w-full rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-900"><option value="">Next upcoming per customer</option>@foreach(['upcoming','pending','partial','paid','overdue','waived','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
            <div class="grid grid-cols-2 items-end gap-2"><button class="rounded-md bg-indigo-600 px-5 py-2.5 font-semibold text-white">Filter</button><a href="{{ route('installments.index') }}" class="rounded-md border px-4 py-2.5 text-center dark:border-slate-600">Clear</a></div>
        </form>
        <div class="rounded-xl bg-white shadow dark:bg-slate-800"><table class="admin-responsive-table w-full text-sm"><thead class="bg-gray-50 text-left text-gray-500 dark:bg-slate-900 dark:text-slate-300"><tr><th class="p-4">Booking / Customer</th><th>Project</th><th>Month</th><th>Due date</th><th>Regular</th><th>Balloon</th><th>Total</th><th>Paid</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse($installments as $installment) @php($displayStatus = in_array($installment->status,['pending','partial']) && $installment->due_date->lt(today()) ? 'overdue' : ($nextUpcomingIds->contains($installment->id) ? 'upcoming' : $installment->status))
        <tr class="border-t dark:border-slate-700 dark:text-white"><td data-label="Booking" class="p-4"><span><a href="{{ route('bookings.show',$installment->booking) }}" class="font-semibold text-indigo-600 dark:text-indigo-300">{{ $installment->booking->booking_number }}</a><small class="block text-xs text-gray-500 dark:text-slate-400">{{ $installment->booking->customer->name }}</small></span></td><td data-label="Project">{{ $installment->booking->project->name }}</td><td data-label="Month">{{ $installment->installment_number }}</td><td data-label="Due date">{{ $installment->due_date->format('d M Y') }}</td><td data-label="Regular">Rs {{ number_format($installment->regular_amount) }}</td><td data-label="Balloon">Rs {{ number_format($installment->balloon_amount) }}</td><td data-label="Total" class="font-semibold">Rs {{ number_format($installment->total_due) }}</td><td data-label="Paid">Rs {{ number_format($installment->paid_amount) }}</td><td data-label="Status"><span class="rounded-full px-2 py-1 {{ $displayStatus==='paid'?'bg-green-100 text-green-700':($displayStatus==='overdue'?'bg-red-100 text-red-700':'bg-amber-100 text-amber-700') }}">{{ ucfirst($displayStatus) }}</span></td><td data-label="Action" class="pe-4"><a href="{{ route('installments.edit',$installment) }}" class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 font-bold text-white">Edit</a></td></tr>
        @empty<tr><td colspan="10" class="p-10 text-center text-gray-500">No installments found.</td></tr>@endforelse</tbody></table></div>
        {{ $installments->links() }}
    </div></div>
</x-app-layout>
