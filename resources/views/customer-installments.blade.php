<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-indigo-900 to-violet-700 p-6 text-white shadow-xl sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-[.2em] text-indigo-200">Customer portal</div>
                        <h1 class="mt-2 text-3xl font-black">Plot installment schedules</h1>
                        <p class="mt-2 text-sm text-indigo-100">View the complete installment plan for each of your plot bookings.</p>
                    </div>
                    <a href="{{ route('dashboard') }}" class="w-fit rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white hover:bg-white/20">← Back to overview</a>
                </div>
            </section>

            @if($bookings->isNotEmpty())
            <div x-data="{ activeBooking: @js((string) $bookings->first()->id) }" class="space-y-5">
                <div class="overflow-x-auto rounded-2xl border border-indigo-100 bg-white p-2 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex min-w-max gap-2" role="tablist" aria-label="Plot bookings">
                        @foreach($bookings as $booking)
                            <button
                                type="button"
                                role="tab"
                                @click="activeBooking = @js((string) $booking->id)"
                                :aria-selected="activeBooking === @js((string) $booking->id)"
                                :class="activeBooking === @js((string) $booking->id) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 dark:shadow-none' : 'bg-slate-50 text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 dark:bg-slate-800 dark:text-slate-300'"
                                class="rounded-xl px-4 py-3 text-left transition"
                            >
                                <span class="block font-mono text-xs font-black">{{ $booking->booking_number }}</span>
                                <span class="mt-1 block whitespace-nowrap text-[10px] font-bold opacity-75">{{ $booking->package->name }} · {{ number_format($booking->package->size_marla, 2) }} marla</span>
                                <span class="mt-0.5 block whitespace-nowrap text-[9px] font-bold opacity-60">
                                    @if($booking->allotment)
                                        {{ $booking->allotment->plot->block->name }} · Plot {{ $booking->allotment->plot->plot_number }}
                                    @else
                                        Allotment pending
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

            @foreach($bookings as $booking)
                @php($scheduled = (float) $booking->installments->sum('total_due'))
                @php($paid = (float) $booking->installments->sum('paid_amount'))
                @php($overdue = (float) $booking->installments->filter(fn ($item) => in_array($item->status, ['pending', 'partial'], true) && $item->due_date->lt(today()))->sum(fn ($item) => max(0, (float) $item->total_due - (float) $item->paid_amount)))
                @php($nextUpcomingId = $booking->installments->where('status', 'pending')->filter(fn ($item) => $item->due_date->gt(today()))->sortBy('due_date')->first()?->id)
                @php($firstPayment = $booking->payments->first())
                <section x-show="activeBooking === @js((string) $booking->id)" x-cloak role="tabpanel" class="overflow-hidden rounded-3xl border border-indigo-100 bg-white shadow-lg shadow-indigo-100/40 dark:border-slate-700 dark:bg-slate-900 dark:shadow-none">
                    @if($firstPayment)
                        <div class="flex flex-wrap items-center gap-x-8 gap-y-3 border-b border-slate-800 bg-black px-5 py-4 text-white sm:px-7">
                            <div class="inline-flex min-w-0 items-center gap-3"><span class="shrink-0 text-[10px] font-black uppercase tracking-wider text-indigo-200">First payment receipt</span><b class="truncate font-mono text-sm">{{ $firstPayment->receipt_number }}</b></div>
                            <div class="inline-flex items-center gap-3"><span class="text-[10px] font-bold uppercase text-indigo-200">Date</span><b class="text-sm">{{ $firstPayment->payment_date->format('d M Y') }}</b></div>
                            <div class="inline-flex items-center gap-3"><span class="text-[10px] font-bold uppercase text-indigo-200">Amount</span><b class="text-sm">Rs {{ number_format($firstPayment->amount, 2) }}</b></div>
                            <span class="rounded-full px-3 py-1.5 text-xs font-black {{ $firstPayment->status === 'verified' ? 'bg-emerald-400 text-emerald-950' : ($firstPayment->status === 'pending' ? 'bg-amber-300 text-amber-950' : 'bg-rose-300 text-rose-950') }}">{{ $firstPayment->status === 'verified' ? 'Paid' : ucfirst($firstPayment->status) }}</span>
                        </div>
                    @endif
                    <div class="border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-violet-50 p-5 dark:border-slate-700 dark:from-slate-800 dark:to-slate-900 sm:p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-lg bg-indigo-600 px-3 py-1.5 font-mono text-xs font-black text-white">{{ $booking->booking_number }}</span>
                                    <span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-slate-600 shadow-sm dark:bg-slate-700 dark:text-slate-200">{{ ucfirst($booking->status) }}</span>
                                </div>
                                <h2 class="mt-3 text-xl font-black text-slate-950 dark:text-white">{{ $booking->project->name }} · {{ $booking->package->name }}</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">
                                    @if($booking->allotment)
                                        {{ $booking->allotment->plot->block->name }} · Plot {{ $booking->allotment->plot->plot_number }} · {{ number_format($booking->allotment->plot->size_marla, 2) }} marla
                                    @else
                                        Plot allotment pending · {{ number_format($booking->package->size_marla, 2) }} marla
                                    @endif
                                </p>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-right">
                                <div class="rounded-xl bg-white px-4 py-3 shadow-sm dark:bg-slate-800"><span class="block text-[10px] font-bold uppercase text-slate-400">Scheduled</span><b class="mt-1 block text-sm text-slate-950 dark:text-white">Rs {{ number_format($scheduled, 2) }}</b></div>
                                <div class="rounded-xl bg-emerald-50 px-4 py-3 dark:bg-emerald-950"><span class="block text-[10px] font-bold uppercase text-emerald-600">Paid</span><b class="mt-1 block text-sm text-emerald-800 dark:text-emerald-300">Rs {{ number_format($paid, 2) }}</b></div>
                                <div class="rounded-xl bg-rose-50 px-4 py-3 dark:bg-rose-950"><span class="block text-[10px] font-bold uppercase text-rose-600">Overdue</span><b class="mt-1 block text-sm text-rose-800 dark:text-rose-300">Rs {{ number_format($overdue, 2) }}</b></div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-left text-sm">
                            <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:bg-slate-800">
                                <tr><th class="p-4">Month</th><th>Due date</th><th>Regular</th><th>Balloon</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($booking->installments as $installment)
                                    @php($balance = max(0, (float) $installment->total_due - (float) $installment->paid_amount))
                                    @php($displayStatus = in_array($installment->status, ['pending', 'partial'], true) && $installment->due_date->lt(today()) ? 'overdue' : ($installment->id === $nextUpcomingId ? 'upcoming' : ($installment->status === 'pending' && $installment->due_date->gt(today()) ? 'scheduled' : $installment->status)))
                                    <tr class="hover:bg-indigo-50/40 dark:hover:bg-slate-800/60">
                                        <td class="p-4 font-black text-slate-950 dark:text-white">{{ $installment->installment_number }}</td>
                                        <td class="whitespace-nowrap text-slate-600 dark:text-slate-300">{{ $installment->due_date->format('d M Y') }}</td>
                                        <td class="text-slate-600 dark:text-slate-300">Rs {{ number_format($installment->regular_amount, 2) }}</td>
                                        <td class="text-slate-600 dark:text-slate-300">Rs {{ number_format($installment->balloon_amount, 2) }}</td>
                                        <td class="font-bold text-slate-900 dark:text-white">Rs {{ number_format($installment->total_due, 2) }}</td>
                                        <td class="font-bold text-emerald-600">Rs {{ number_format($installment->paid_amount, 2) }}</td>
                                        <td class="font-black text-slate-900 dark:text-white">Rs {{ number_format($balance, 2) }}</td>
                                        <td><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $displayStatus === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($displayStatus === 'overdue' ? 'bg-rose-100 text-rose-700' : ($displayStatus === 'partial' ? 'bg-blue-100 text-blue-700' : ($displayStatus === 'upcoming' ? 'bg-violet-100 text-violet-700' : ($displayStatus === 'scheduled' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700')))) }}">{{ ucfirst($displayStatus) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="p-10 text-center text-slate-400">No installments scheduled for this booking.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach
            </div>
            @else
                <div class="rounded-3xl border border-slate-100 bg-white p-12 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <h2 class="font-black text-slate-900 dark:text-white">No plot bookings found</h2>
                    <p class="mt-1 text-sm text-slate-400">Your installment schedules will appear here after a booking is created.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
