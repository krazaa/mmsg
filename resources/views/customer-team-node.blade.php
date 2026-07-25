@php
    $user = $node['user'];
    $initials = collect(preg_split('/\s+/', trim($user->name)))
        ->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('');
    $levelStyles = [
        1 => ['bar' => 'from-violet-600 via-fuchsia-500 to-indigo-600', 'avatar' => 'from-fuchsia-500 to-violet-700', 'badge' => 'bg-white/20 text-white ring-white/30', 'soft' => 'from-violet-700 via-indigo-700 to-indigo-950', 'glow' => 'shadow-violet-200/70'],
        2 => ['bar' => 'from-sky-500 via-cyan-400 to-indigo-600', 'avatar' => 'from-cyan-400 to-blue-700', 'badge' => 'bg-white/20 text-white ring-white/30', 'soft' => 'from-sky-700 via-blue-700 to-indigo-950', 'glow' => 'shadow-sky-200/70'],
        3 => ['bar' => 'from-emerald-500 via-teal-400 to-cyan-600', 'avatar' => 'from-emerald-400 to-teal-700', 'badge' => 'bg-white/20 text-white ring-white/30', 'soft' => 'from-emerald-700 via-teal-700 to-slate-900', 'glow' => 'shadow-emerald-200/70'],
    ][$node['level']];
@endphp

<div x-data="{ expanded: true }" class="network-branch relative flex shrink-0 flex-col items-center px-2 pt-6">
    <div class="absolute left-1/2 top-0 h-6 w-0.5 -translate-x-1/2 bg-gradient-to-b from-indigo-400 to-indigo-200"></div>

    <article class="network-node relative z-10 w-72 overflow-hidden rounded-2xl border border-white bg-white/95 text-left shadow-xl {{ $levelStyles['glow'] }} ring-1 ring-slate-200 backdrop-blur transition duration-300 hover:-translate-y-1 hover:shadow-2xl">
        <div class="h-1.5 bg-gradient-to-r {{ $levelStyles['bar'] }}"></div>
        <div class="relative overflow-hidden bg-gradient-to-br {{ $levelStyles['soft'] }} p-3.5 text-white">
            <div class="absolute -right-3 -top-8 select-none text-[72px] font-black leading-none text-white/5">L{{ $node['level'] }}</div>
            <div class="absolute -bottom-8 -left-8 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
            <div class="flex items-start gap-2.5">
                <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-white/30 bg-gradient-to-br {{ $levelStyles['avatar'] }} text-xs font-black text-white shadow-lg ring-2 ring-white/10">
                    {{ $initials }}
                    <i class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white {{ $user->status ? 'bg-emerald-400' : 'bg-slate-300' }}"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-black text-white">{{ $user->name }}</div>
                            <div class="truncate font-mono text-[9px] font-bold text-white/60">{{ $user->referral_code }}</div>
                        </div>
                        <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-black ring-1 ring-inset {{ $levelStyles['badge'] }}">L{{ $node['level'] }}</span>
                    </div>
                    @if($user->phone)
                    <a href="tel:{{ $user->phone }}" class="mt-1 inline-flex items-center gap-1 text-[10px] font-semibold text-white/75 hover:text-white">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102A1.125 1.125 0 0 0 5.872 2.25H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                        {{ $user->phone }}
                    </a>
                    @else
                        <div class="mt-1 text-[10px] font-semibold text-white/50">No contact number</div>
                    @endif
                </div>
            </div>

        </div>

        <div class="p-3">
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">Bookings</span>
                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[9px] font-black text-indigo-700">{{ $user->bookings_count }}</span>
                </div>
                <div class="max-h-36 space-y-1.5 overflow-y-auto pr-1">
                    @forelse($user->bookings as $booking)
                        @php
                            $bookingInstallments = $booking->installments->whereNotIn('status', ['waived', 'cancelled']);
                            $bookingPaid = $bookingInstallments->where('status', 'paid')->count();
                            $bookingPending = $bookingInstallments->whereIn('status', ['pending', 'partial', 'overdue'])->count();
                        @endphp
                        <div class="group/booking rounded-xl border border-slate-100 bg-gradient-to-r from-slate-50 to-white p-2.5 transition hover:border-indigo-200 hover:shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="truncate text-[10px] font-black text-slate-800">{{ $booking->package?->name ?? 'Package' }}</div>
                                    <div class="truncate text-[8px] text-slate-500">{{ $booking->project?->name }} · {{ $booking->booking_number }}</div>
                                </div>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[8px] font-bold ring-1 ring-inset {{ in_array($booking->status, ['active', 'completed']) ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20' }}">{{ ucfirst($booking->status) }}</span>
                            </div>
                            <div class="mt-1.5 flex gap-3 text-[8px] font-semibold">
                                <span class="text-slate-500">{{ $bookingInstallments->count() }} total</span>
                                <span class="text-emerald-600">{{ $bookingPaid }} paid</span>
                                <span class="text-amber-600">{{ $bookingPending }} pending</span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-200 px-2 py-3 text-center text-[9px] text-slate-400">No bookings</div>
                    @endforelse
                </div>
            </div>

            <div class="mt-2.5 flex items-center justify-between border-t border-slate-100 pt-2">
                <span class="text-[8px] font-bold uppercase tracking-wide text-amber-500">Payable commission</span>
                <span class="rounded-lg bg-amber-50 px-2 py-1 text-[11px] font-black text-amber-700">Rs {{ number_format($user->payable_commission ?? 0, 0) }}</span>
            </div>
        </div>
    </article>

    @if($node['children'])
        <button type="button" @click="expanded = ! expanded" class="relative z-20 mt-1.5 flex items-center gap-1 rounded-full border border-indigo-200 bg-white px-2 py-0.5 text-[9px] font-black text-indigo-700 shadow-sm">
            <span x-text="expanded ? '−' : '+'">−</span>{{ count($node['children']) }}
        </button>
        <div x-show="expanded" class="h-4 w-0.5 bg-indigo-300"></div>
        <div x-show="expanded" class="network-children relative flex items-start justify-center gap-2 border-t-2 border-indigo-200">
            @foreach($node['children'] as $child)
                @include('customer-team-node', ['node' => $child])
            @endforeach
        </div>
    @endif
</div>
