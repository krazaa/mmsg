<x-app-layout>

  <div class="py-8">
    <div class="mx-auto max-w-7xl space-y-6 px-4">

        @if (session('success'))
            <div class="rounded-lg bg-green-100 p-4 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-red-100 p-4 text-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <nav class="flex items-center gap-2 overflow-x-auto rounded-xl bg-white p-2 shadow-sm">
            <a
                href="{{ route('customers.show', $customer) }}"
                class="whitespace-nowrap rounded-lg px-5 py-2.5 text-sm font-semibold
                    {{ request('tab', 'overview') === 'overview'
                        ? 'bg-indigo-600 text-white'
                        : 'text-gray-600 hover:bg-gray-100' }}"
            >
                Overview
            </a>

            <a
                href="{{ route('customers.downline', $customer) }}"
                class="whitespace-nowrap rounded-lg px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-100"
            >
                Downline

                <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700">
                    {{ $downline->count() }}
                </span>

                <span aria-hidden="true">→</span>
            </a>

            <div class="ml-auto flex items-center gap-2">
                <a
                    href="{{ route('customers.portal', $customer) }}"
                    class="whitespace-nowrap rounded-lg bg-violet-50 px-4 py-2.5 text-sm font-semibold text-violet-700 hover:bg-violet-100"
                >
                    View portal
                </a>
                <a
                    href="{{ route('customers.team', $customer) }}"
                    class="whitespace-nowrap rounded-lg bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100"
                >
                    View team
                </a>
                <a href="{{ route('customers.commissions', $customer) }}" class="whitespace-nowrap rounded-lg bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">Commissions</a>
                <a
                    href="{{ route('customers.edit', $customer) }}"
                    class="whitespace-nowrap rounded-lg px-4 py-2.5 text-sm font-semibold text-amber-700 hover:bg-amber-50"
                >
                    Edit
                </a>

                <a
                    href="{{ route('customers.index') }}"
                    class="whitespace-nowrap rounded-lg px-4 py-2.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-50"
                >
                    ← Customers
                </a>
            </div>
        </nav>

        @if($agent)
            <section class="overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-700 via-violet-700 to-purple-800 text-white shadow-lg">
                <div class="grid gap-6 p-6 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <div class="text-3xl text-indigo-100">{{ $customer->name }} | {{ $customer->email }}</div>
                        @if($customer->file_no)<div class="mt-2 inline-flex rounded-lg border border-white/20 bg-white/10 px-3 py-1 font-mono text-sm font-bold text-white">File No: {{ $customer->file_no }}</div>@endif
                        <div class="mt-1 text-2xl font-bold tracking-wide">{{ $customer->referral_code }}</div>
                        <div class="mt-2 text-sm text-indigo-100">Sponsor: {{ $agent->referral?->sponsor?->name ?? 'Top level / direct' }} | Contact: {{ $agent->referral?->sponsor?->phone ?? 'N/A' }}</div></div>
                    <div class="grid grid-cols-3 gap-3 text-center"><div class="rounded-xl bg-white/10 p-4"><div class="text-xs text-indigo-100">Payable</div><div class="mt-1 font-bold">Rs {{ number_format($agentSummary->payable,2) }}</div></div><div class="rounded-xl bg-white/10 p-4"><div class="text-xs text-indigo-100">Paid out</div><div class="mt-1 font-bold">Rs {{ number_format($agentSummary->paid,2) }}</div></div><div class="rounded-xl bg-white/10 p-4"><div class="text-xs text-indigo-100">Lifetime</div><div class="mt-1 font-bold">Rs {{ number_format($agentSummary->lifetime,2) }}</div></div></div>
                </div>

            </section>

            <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(320px,0.55fr)]">
                <div class="overflow-x-auto rounded-xl bg-white shadow-sm"><div class="border-b p-5"><h3 class="font-bold">Commission history</h3><p class="mt-1 text-sm text-gray-500">Lifetime commission earned through verified customer payments.</p></div><table class="w-full min-w-[700px] text-sm"><thead class="bg-gray-50 text-left text-gray-500"><tr><th class="p-4">Date</th><th>Booking / customer</th><th>Level</th><th>Rate</th><th>Amount</th><th>Status</th></tr></thead><tbody>@forelse($agentCommissions as $commission)<tr class="border-t"><td class="p-4">{{ $commission->created_at->format('d M Y') }}</td><td>{{ $commission->booking?->booking_number ?? '—' }}<div class="text-xs text-gray-500">{{ $commission->booking?->customer?->name ?? '—' }}</div></td><td>Level {{ $commission->level }}</td><td>{{ $commission->calculation_type === 'fixed' ? 'Rs '.number_format($commission->fixed_amount, 2) : number_format($commission->percentage, 2).'%' }}</td><td class="font-semibold">Rs {{ number_format($commission->amount,2) }}</td><td><span class="rounded-full px-2 py-1 text-xs {{ $commission->status === 'earned' ? 'bg-amber-100 text-amber-700' : ($commission->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">{{ ucfirst($commission->status) }}</span></td></tr>@empty<tr><td colspan="6" class="p-8 text-center text-gray-500">No commission history.</td></tr>@endforelse</tbody></table></div>
                <div class="space-y-6">
                    <div class="rounded-xl bg-white p-5 shadow-sm"><h3 class="font-bold">Payout history</h3><div class="mt-4 space-y-3">@forelse($agentPayouts as $payout)<div class="rounded-lg border p-3"><div class="flex justify-between gap-3"><b>Rs {{ number_format($payout->amount,2) }}</b><span class="text-xs text-gray-500">{{ $payout->paid_at->format('d M Y') }}</span></div><div class="mt-1 text-xs text-gray-500">{{ $payout->payout_number }} · {{ ucwords(str_replace('_',' ',$payout->payment_method)) }}</div>@if($payout->transaction_reference)<div class="mt-1 text-xs">Ref: {{ $payout->transaction_reference }}</div>@endif</div>@empty<p class="text-sm text-gray-500">No payouts recorded.</p>@endforelse</div></div>
                </div>
            </section>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><div class="rounded-xl bg-white p-5 shadow-sm"><div class="text-sm text-gray-500">Verified payments</div><div class="mt-2 text-2xl font-bold text-green-700">Rs {{ number_format($verifiedTotal,2) }}</div></div><div class="rounded-xl bg-white p-5 shadow-sm"><div class="text-sm text-gray-500">Reversed payments</div><div class="mt-2 text-2xl font-bold text-red-700">Rs {{ number_format($reversedTotal,2) }}</div></div><div class="rounded-xl bg-white p-5 shadow-sm"><div class="text-sm text-gray-500">Active booking value</div><div class="mt-2 text-2xl font-bold">Rs {{ number_format($bookingTotal,2) }}</div></div><div class="rounded-xl bg-white p-5 shadow-sm"><div class="text-sm text-gray-500">Outstanding</div><div class="mt-2 text-2xl font-bold text-amber-700">Rs {{ number_format(max(0,$bookingTotal-$verifiedTotal),2) }}</div></div></div>
        <div class="rounded-xl bg-white p-5 shadow-sm"><h3 class="mb-4 font-bold">Bookings</h3><div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">@forelse($bookings as $booking)<a href="{{ route('bookings.show',$booking) }}" class="rounded-lg border p-4 hover:border-indigo-400"><div class="flex justify-between"><b class="text-indigo-600">{{ $booking->booking_number }}</b><span class="text-xs">{{ ucfirst($booking->status) }}</span></div><div class="mt-2 text-sm">{{ $booking->project->name }} · {{ $booking->package->name }}</div><div class="mt-1 text-xs text-gray-500">Referred by: {{ $booking->agent?->name ?? 'Direct' }} · Rs {{ number_format($booking->total_price) }}</div></a>@empty<div class="text-sm text-gray-500">No bookings.</div>@endforelse</div></div>
        <form method="GET" class="flex items-end gap-3 rounded-xl bg-white p-4 shadow-sm"><label class="text-sm font-medium">Payment status<select name="status" class="mt-1 rounded-md border-gray-300"><option value="">All statuses</option>@foreach(['verified','reversed'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></label><button class="rounded bg-indigo-600 px-5 py-2.5 text-white">Filter</button><a href="{{ route('customers.show',$customer) }}" class="rounded border px-4 py-2.5">Clear</a></form>
        <div class="overflow-hidden rounded-xl bg-white shadow-sm"><div class="border-b p-5"><h3 class="font-bold">Payment history</h3><p class="mt-1 text-sm text-gray-500">All payment transactions recorded for this customer.</p></div><div class="overflow-x-auto"><table class="w-full min-w-[1050px] text-sm"><thead class="bg-gray-50 text-left text-gray-500"><tr><th class="p-4">Date / Receipt</th><th>Booking</th><th>Project / Package</th><th>Applied to</th><th>Method</th><th>Reference</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>@forelse($payments as $payment)<tr class="border-t"><td class="p-4"><div>{{ $payment->payment_date->format('d M Y, h:i A') }}</div><div class="text-xs font-semibold">{{ $payment->receipt_number }}</div></td><td><a href="{{ route('bookings.show',$payment->booking) }}" class="text-indigo-600">{{ $payment->booking->booking_number }}</a></td><td>{{ $payment->booking->project->name }}<div class="text-xs text-gray-500">{{ $payment->booking->package->name }}</div></td><td>{{ $payment->installment?'Month '.$payment->installment->installment_number:'Booking payment' }}</td><td>{{ ucwords(str_replace('_',' ',$payment->payment_method)) }}</td><td>{{ $payment->transaction_reference??'—' }}</td><td class="font-bold">Rs {{ number_format($payment->amount,2) }}</td><td><span class="rounded-full px-2 py-1 {{ $payment->status==='verified'?'bg-green-100 text-green-700':'bg-red-100 text-red-700' }}">{{ ucfirst($payment->status) }}</span></td><td><a href="{{ route('payments.edit',$payment) }}" class="text-indigo-600">Manage</a></td></tr>@empty<tr><td colspan="9" class="p-10 text-center text-gray-500">No payment transactions.</td></tr>@endforelse</tbody></table></div></div>{{ $payments->links() }}
        @if(false)
            <section class="space-y-5">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-950 via-indigo-900 to-violet-700 p-5 text-white shadow-lg"><div class="absolute -right-5 -top-5 h-24 w-24 rounded-full bg-white/10"></div><div class="text-xs font-bold uppercase tracking-widest text-indigo-200">Network size</div><div class="mt-2 text-4xl font-black">{{ $downline->count() }}</div><div class="mt-1 text-xs text-indigo-200">Total customers across 3 levels</div></div>@foreach([1=>'Direct referrals',2=>'Second generation',3=>'Third generation'] as $level=>$label) @php($colors=$level===1?'border-violet-100 text-violet-600 bg-violet-500 ring-violet-100':($level===2?'border-sky-100 text-sky-600 bg-sky-500 ring-sky-100':'border-emerald-100 text-emerald-600 bg-emerald-500 ring-emerald-100'))<div class="rounded-2xl border bg-white p-5 shadow-sm {{ explode(' ',$colors)[0] }}"><div class="flex items-center justify-between"><div><div class="text-xs font-bold uppercase tracking-widest {{ explode(' ',$colors)[1] }}">Level {{ $level }}</div><div class="mt-2 text-4xl font-black text-slate-900">{{ $downlineCounts->get($level,0) }}</div><div class="mt-1 text-xs text-slate-400">{{ $label }}</div></div><div class="h-3 w-3 rounded-full ring-4 {{ explode(' ',$colors)[2] }} {{ explode(' ',$colors)[3] }}"></div></div></div>@endforeach</div>
                <div x-data="{ zoom: 1, fullscreen: false, center(){ this.$nextTick(()=>{ this.$refs.network.scrollTo({left:(this.$refs.network.scrollWidth-this.$refs.network.clientWidth)/2,behavior:'smooth'}) }) } }" x-init="center()" :class="fullscreen ? 'fixed inset-0 z-[100] rounded-none' : 'rounded-3xl'" class="overflow-hidden border border-indigo-100 bg-white shadow-xl">
                    <div class="flex flex-col gap-4 border-b border-indigo-100 bg-gradient-to-r from-slate-950 via-indigo-950 to-violet-900 p-5 text-white sm:flex-row sm:items-center sm:justify-between"><div><div class="flex items-center gap-2"><h3 class="text-lg font-black">Referral network</h3><span class="rounded-full bg-emerald-400/20 px-2 py-1 text-[9px] font-black uppercase tracking-widest text-emerald-300">Live tree</span></div><p class="mt-1 text-sm text-indigo-200">Explore, zoom and collapse branches to focus on relationships</p></div><div class="flex flex-wrap items-center gap-2"><div class="hidden items-center gap-3 rounded-full bg-white/10 px-3 py-2 text-[10px] font-bold md:flex"><span class="flex items-center gap-1"><i class="h-2 w-2 rounded-full bg-violet-400"></i>L1</span><span class="flex items-center gap-1"><i class="h-2 w-2 rounded-full bg-sky-400"></i>L2</span><span class="flex items-center gap-1"><i class="h-2 w-2 rounded-full bg-emerald-400"></i>L3</span></div><div class="flex rounded-lg bg-white/10 p-1"><button type="button" @click="zoom=Math.max(.6,zoom-.1)" class="rounded px-2.5 py-1.5 font-black hover:bg-white/10" title="Zoom out">−</button><button type="button" @click="zoom=1;center()" class="min-w-14 rounded px-2 py-1.5 text-xs font-bold hover:bg-white/10" x-text="Math.round(zoom*100)+'%'"></button><button type="button" @click="zoom=Math.min(1.4,zoom+.1)" class="rounded px-2.5 py-1.5 font-black hover:bg-white/10" title="Zoom in">+</button></div><button type="button" @click="$refs.network.scrollBy({left:-500,behavior:'smooth'})" class="rounded-lg bg-white/10 px-3 py-2 font-black hover:bg-white/20" aria-label="Scroll left">←</button><button type="button" @click="$refs.network.scrollBy({left:500,behavior:'smooth'})" class="rounded-lg bg-white/10 px-3 py-2 font-black hover:bg-white/20" aria-label="Scroll right">→</button><button type="button" @click="fullscreen=!fullscreen;center()" class="rounded-lg bg-white/10 px-3 py-2 text-xs font-black hover:bg-white/20" x-text="fullscreen?'Exit':'Fullscreen'"></button></div></div>
                    <div x-ref="network" :class="fullscreen ? 'h-[calc(100vh-96px)]' : ''" class="network-canvas relative overflow-auto p-4">
                        <div class="pointer-events-none absolute left-8 top-8 rounded-full border border-indigo-100 bg-white/80 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-400 shadow-sm backdrop-blur">Root → branches → generations</div>
                        <div class="mx-auto flex min-w-max origin-top flex-col items-center pb-3 pt-4 transition-all duration-200" :style="`zoom:${zoom}`">
                            @php($rootInitials = collect(preg_split('/\s+/', trim($customer->name)))->filter()->take(2)->map(fn($part)=>strtoupper(substr($part,0,1)))->join(''))
                            <a href="{{ route('customers.show',$customer) }}" class="network-owner relative z-10 w-44 rounded-xl border-2 border-violet-300 bg-white/95 p-2.5 text-center shadow-lg ring-4 ring-violet-50 backdrop-blur transition hover:-translate-y-1">
                                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-lg bg-gradient-to-br from-violet-600 to-indigo-800 text-sm font-black text-white shadow">{{ $rootInitials }}</div>
                                <div class="mt-2 truncate font-black text-gray-900">{{ $customer->name }}</div>
                                <div class="mt-1 font-mono text-xs font-bold text-violet-600">{{ $customer->referral_code }}</div>
                                <div class="mt-2 grid grid-cols-2 gap-1.5"><div class="rounded-md bg-violet-50 px-1.5 py-1"><div class="text-[8px] font-bold uppercase text-violet-400">Direct</div><div class="text-sm font-black text-violet-800">{{ $downlineCounts->get(1,0) }}</div></div><div class="rounded-md bg-amber-50 px-1.5 py-1"><div class="text-[8px] font-bold uppercase text-amber-500">Payable</div><div class="truncate text-[11px] font-black text-amber-800">Rs {{ number_format($agentSummary->payable,0) }}</div></div></div>
                            </a>
                            @if($downlineTree)
                                <div class="h-5 w-px bg-indigo-300"></div>
                                <div class="relative flex items-start justify-center gap-1 border-t border-indigo-300">
                                    @foreach($downlineTree as $node)
                                        @include('customers._downline_node',['node'=>$node])
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-8 rounded-xl border border-dashed border-gray-300 bg-white px-8 py-6 text-center text-sm text-gray-500">This customer does not have a downline yet.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div></div>
</x-app-layout>
