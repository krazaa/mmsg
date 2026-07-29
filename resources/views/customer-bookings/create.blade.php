<x-app-layout>

    <div class="min-h-screen bg-gradient-to-b from-indigo-50/80 via-slate-50 to-white py-6 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 sm:py-8"><div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6">
        @if($errors->any())<div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 shadow-sm"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-600 text-white">!</span>{{ $errors->first() }}</div>@endif
        @if($pendingBooking)<div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900 shadow-sm"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-500 font-black text-white">!</span><div><div class="font-black">Your booking request {{ $pendingBooking->booking_number }} is awaiting office approval.</div><p class="mt-1 text-amber-800">You can submit another plot request after this request is approved or cancelled.</p></div></div>@endif

        @php($packageCount = $projects->sum(fn ($project) => $project->packages->count()))
        <section class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-indigo-900 to-violet-700 text-white shadow-2xl shadow-indigo-200 dark:shadow-none">
            <div class="absolute -right-20 -top-24 -z-10 h-80 w-80 rounded-full bg-fuchsia-400/30 blur-3xl"></div>
            <div class="absolute -bottom-28 left-1/3 -z-10 h-64 w-64 rounded-full bg-cyan-300/20 blur-3xl"></div>
            <div class="absolute inset-0 -z-10 opacity-[.06]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px"></div>
            <div class="grid lg:grid-cols-2">
                <header class="flex flex-col justify-center p-6 sm:p-7 lg:p-8">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[.18em] text-indigo-100 backdrop-blur">Property marketplace</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-emerald-200"><i class="h-1.5 w-1.5 rounded-full bg-emerald-300"></i>Live inventory</span>
                    </div>
                    <h1 class="mt-4 max-w-3xl text-2xl font-black tracking-tight sm:text-3xl lg:text-4xl lg:leading-tight">Choose the right property plan for your future.</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-indigo-100 sm:text-base">Compare cash and installment prices, select your preferred plot size, and send a secure request for office approval.</p>
                    <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-[11px] font-bold text-indigo-100">
                        <span class="inline-flex items-center gap-2"><i class="grid h-5 w-5 place-items-center rounded-full bg-white/10 text-[9px]">1</i>Choose a plan</span>
                        <span class="inline-flex items-center gap-2"><i class="grid h-5 w-5 place-items-center rounded-full bg-white/10 text-[9px]">2</i>Send request</span>
                        <span class="inline-flex items-center gap-2"><i class="grid h-5 w-5 place-items-center rounded-full bg-white/10 text-[9px]">3</i>Office approval</span>
                    </div>
                </header>
                <aside class="flex flex-col justify-center border-t border-white/10 bg-white/[.08] p-6 backdrop-blur-md lg:border-l lg:border-t-0 sm:p-7 lg:p-8">
                    <div class="flex items-center justify-between gap-4">
                        <div><div class="text-[10px] font-black uppercase tracking-[.18em] text-indigo-200">Marketplace overview</div><div class="mt-1 text-sm font-bold">Available to book</div></div>
                        <span class="grid h-10 w-10 place-items-center rounded-xl border border-white/15 bg-white/10 text-lg">⌂</span>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4"><div class="text-[9px] font-black uppercase tracking-wider text-indigo-200">Projects</div><div class="mt-1 text-2xl font-black">{{ $projects->count() }}</div></div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4"><div class="text-[9px] font-black uppercase tracking-wider text-indigo-200">Plans</div><div class="mt-1 text-2xl font-black">{{ $packageCount }}</div></div>
                    </div>
                    <div class="mt-3 rounded-2xl border border-white/10 bg-slate-950/35 p-4">
                        <div class="text-[9px] font-black uppercase tracking-wider text-indigo-200">Your current amount due</div>
                        <div class="mt-1 text-xl font-black text-white">Rs {{ number_format($dueNow, 2) }}</div>
                        <p class="mt-1 text-[10px] leading-4 text-indigo-200">No booking payment is charged before office approval.</p>
                    </div>
                </aside>
            </div>
        </section>

        <section class="grid gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:grid-cols-3">
            <div class="flex items-center gap-3 rounded-xl bg-indigo-50 p-3 dark:bg-indigo-950/50"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-600 font-black text-white">1</span><div><div class="text-sm font-bold text-gray-900 dark:text-white">Choose a plan</div><div class="text-xs text-gray-500 dark:text-gray-300">Compare size and pricing</div></div></div>
            <div class="flex items-center gap-3 rounded-xl bg-violet-50 p-3 dark:bg-violet-950/50"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-600 font-black text-white">2</span><div><div class="text-sm font-bold text-gray-900 dark:text-white">Send request</div><div class="text-xs text-gray-500 dark:text-gray-300">Inventory is reserved</div></div></div>
            <div class="flex items-center gap-3 rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/50"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-600 font-black text-white">3</span><div><div class="text-sm font-bold text-gray-900 dark:text-white">Office approval</div><div class="text-xs text-gray-500 dark:text-gray-300">Pay only after approval</div></div></div>
        </section>

        @if($projects->count() > 1)<nav class="flex gap-2 overflow-x-auto pb-1">@foreach($projects as $project)<a href="#project-{{ $project->id }}" class="whitespace-nowrap rounded-full border border-indigo-200 bg-white px-4 py-2 text-sm font-bold text-indigo-700 shadow-sm hover:bg-indigo-600 hover:text-white dark:border-gray-600 dark:bg-gray-800 dark:text-indigo-300">{{ $project->name }}</a>@endforeach</nav>@endif

        @forelse($projects as $project)
            @php($availablePercent = (float)$project->saleable_area_marla > 0 ? min(100, $project->available_area_marla / (float)$project->saleable_area_marla * 100) : 0)
            <section id="project-{{ $project->id }}" class="scroll-mt-20 space-y-5">
                <div class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-center sm:justify-between"><div><div class="mb-1 text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-300">Available project</div><h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ $project->name }}</h3><p class="mt-1 text-sm text-gray-500 dark:text-gray-300">{{ $project->location }}</p></div><div class="w-full sm:max-w-xs"><div class="mb-2 flex justify-between text-xs font-bold"><span class="text-gray-500 dark:text-gray-300">Land availability</span><span class="text-emerald-600">{{ number_format($project->available_area_marla, 2) }} marla</span></div><div class="h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700"><div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-400" style="width: {{ $availablePercent }}%"></div></div></div></div>

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">@forelse($project->packages as $package)
                    @php($available = $project->available_area_marla >= (float)$package->size_marla)
                    @php($canRequest = $available && !$pendingBooking)
                    @php($offersCash = $package->offersCash())
                    @php($offersInstallments = $package->offersInstallments())
                    @php($singlePlan = ($offersCash xor $offersInstallments))
                    @php($initialPlan = $offersInstallments ? 'installment' : 'cash')
                    <article class="group relative overflow-hidden rounded-3xl border border-indigo-100 bg-white shadow-lg shadow-slate-200/70 transition duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-2xl dark:border-gray-700 dark:bg-gray-800 dark:shadow-none">
                        <div class="absolute right-0 top-0 h-28 w-28 rounded-bl-[5rem] bg-gradient-to-br from-indigo-100 to-violet-100 opacity-80 dark:from-indigo-900 dark:to-violet-900"></div>
                        <div class="relative p-6"><div class="flex items-start justify-between gap-4"><div><div class="text-xs font-black uppercase tracking-[0.16em] text-indigo-600 dark:text-indigo-300">Property plan</div><h4 class="mt-2 text-3xl font-black tracking-tight text-gray-950 dark:text-white">{{ $package->name }}</h4><div class="mt-2 flex items-center gap-2 text-sm font-semibold text-gray-500 dark:text-gray-300"><span class="rounded-md bg-indigo-50 px-2 py-1 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">{{ $package->size_marla }} marla</span><span>·</span><span>{{ $package->months }} months</span></div></div><span class="relative rounded-full px-2.5 py-1 text-xs font-black {{ $available?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-700' }}">{{ $available?'Available':'Full' }}</span></div>

                            <form x-data="{ confirmOpen: false, submitting: false, paymentPlan: @js($initialPlan), cashPrice: {{ (float) $package->effective_cash_price }}, installmentPrice: {{ (float) $package->total_price }}, money(value) { return 'Rs ' + Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) } }" x-ref="bookingForm" method="POST" action="{{ route('customer.bookings.store') }}" @submit.prevent="if (paymentPlan) confirmOpen = true" class="mt-5">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $package->id }}">
                                <fieldset class="mb-4 grid {{ !$singlePlan ? 'grid-cols-2' : 'grid-cols-1' }} gap-2">
                                    <legend class="col-span-2 mb-2 min-h-9">
                                        <span class="block text-xs font-black uppercase tracking-wide text-slate-700 dark:text-slate-200">Choose payment plan</span>
                                        <span class="mt-1 block text-[10px] font-medium normal-case tracking-normal text-slate-500 dark:text-slate-400">{{ $singlePlan ? 'This is the only payment plan available for this package.' : 'Installments are selected by default. Choose Cash to compare plans.' }}</span>
                                    </legend>
                                    @if($offersCash)
                                        <label class="min-h-[92px] cursor-pointer overflow-hidden rounded-xl border p-3 transition" :class="paymentPlan === 'cash' ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-100 dark:bg-emerald-950/30' : 'border-slate-200 dark:border-slate-700'">
                                            <span class="flex min-h-5 items-start justify-between gap-2">
                                                <input type="radio" name="payment_plan" value="cash" x-model="paymentPlan" @checked($initialPlan === 'cash') class="mt-0.5 shrink-0 text-emerald-600">
                                                <span x-show="paymentPlan === 'cash'" x-cloak class="shrink-0 rounded-full bg-emerald-600 px-2 py-0.5 text-[8px] font-black uppercase text-white">Selected</span>
                                            </span>
                                            <span class="mt-1 block text-sm font-black text-slate-900 dark:text-white">{{ $singlePlan ? 'Cash Only' : 'Cash' }}</span>
                                            <span class="mt-1 block text-[10px] text-slate-500">Pay full cash rate{{ $singlePlan ? ' · Installments not available' : '' }}</span>
                                        </label>
                                    @endif
                                    @if($offersInstallments)
                                        <label class="min-h-[92px] cursor-pointer overflow-hidden rounded-xl border p-3 transition" :class="paymentPlan === 'installment' ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-100 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-700'">
                                            <span class="flex min-h-5 items-start justify-between gap-2">
                                                <input type="radio" name="payment_plan" value="installment" x-model="paymentPlan" @checked($initialPlan === 'installment') class="mt-0.5 shrink-0 text-indigo-600">
                                                <span x-show="paymentPlan === 'installment'" x-cloak class="shrink-0 rounded-full bg-indigo-600 px-2 py-0.5 text-[8px] font-black uppercase text-white">Selected</span>
                                            </span>
                                            <span class="mt-1 block text-sm font-black text-slate-900 dark:text-white">{{ $singlePlan ? 'Installments Only' : 'Installments' }}</span>
                                            <span class="mt-1 block text-[10px] text-slate-500">{{ $package->months }} monthly payments{{ $singlePlan ? ' · Cash not available' : '' }}</span>
                                        </label>
                                    @endif
                                </fieldset>

                                <div x-show="paymentPlan" x-cloak class="mb-4 overflow-hidden rounded-2xl border" :class="paymentPlan === 'cash' ? 'border-emerald-200 bg-emerald-50/70 dark:border-emerald-900 dark:bg-emerald-950/30' : 'border-indigo-200 bg-indigo-50/70 dark:border-indigo-900 dark:bg-indigo-950/30'">
                                    <div x-show="paymentPlan === 'cash'" x-cloak class="p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="pt-1 text-[10px] font-black uppercase tracking-wider text-emerald-600">Selected cash plan</div>
                                            @if($offersInstallments && $package->total_price > (float) $package->cash_price)
                                                <span class="shrink-0 rounded-full bg-emerald-600 px-3 py-1.5 text-[9px] font-black leading-tight text-white">Save Rs {{ number_format($package->total_price - (float) $package->cash_price) }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-2 whitespace-nowrap text-2xl font-black tracking-tight text-emerald-950 dark:text-emerald-100" x-text="money(cashPrice)"></div>
                                        <div class="mt-3 grid grid-cols-2 gap-2 text-xs"><div class="rounded-xl bg-white/70 p-3 dark:bg-slate-900/50"><span class="block text-[9px] font-bold uppercase text-slate-400">Payment due</span><b class="mt-1 block text-emerald-800 dark:text-emerald-200">Full amount</b></div><div class="rounded-xl bg-white/70 p-3 dark:bg-slate-900/50"><span class="block text-[9px] font-bold uppercase text-slate-400">Schedule</span><b class="mt-1 block text-emerald-800 dark:text-emerald-200">No installments</b></div></div>
                                    </div>
                                    <div x-show="paymentPlan === 'installment'" class="p-4">
                                        <div class="text-[10px] font-black uppercase tracking-wider text-indigo-600">Selected installment plan</div>
                                        <div class="mt-1 text-2xl font-black text-indigo-950 dark:text-indigo-100" x-text="money(installmentPrice)"></div>
                                        <div class="mt-3 grid grid-cols-3 gap-2 text-xs"><div class="rounded-xl bg-white/70 p-3 dark:bg-slate-900/50"><span class="block text-[9px] font-bold uppercase text-slate-400">First payment</span><b class="mt-1 block text-indigo-800 dark:text-indigo-200">Rs {{ number_format($package->booking_amount) }}</b></div><div class="rounded-xl bg-white/70 p-3 dark:bg-slate-900/50"><span class="block text-[9px] font-bold uppercase text-slate-400">Monthly</span><b class="mt-1 block text-indigo-800 dark:text-indigo-200">Rs {{ number_format($package->monthly_amount) }}</b></div><div class="rounded-xl bg-white/70 p-3 dark:bg-slate-900/50"><span class="block text-[9px] font-bold uppercase text-slate-400">Duration</span><b class="mt-1 block text-indigo-800 dark:text-indigo-200">{{ $package->months }} months</b></div></div>
                                        @if($package->balloonPayments())
                                            <div class="mt-3 rounded-xl border border-amber-200 bg-white/70 p-3 dark:border-amber-900 dark:bg-slate-900/60">
                                                <div class="mb-3 flex items-center gap-2.5"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-amber-500 text-[11px] font-black text-white shadow-sm">B</span><div><div class="text-[9px] font-black uppercase tracking-[.14em] text-amber-700 dark:text-amber-300">Balloon payments</div><div class="mt-0.5 text-[9px] text-slate-500 dark:text-slate-400">Additional scheduled payments</div></div></div>
                                                <div class="grid grid-cols-3 gap-2">
                                                    @foreach($package->balloonPayments() as $balloon)
                                                        <div class="min-w-0 rounded-lg border border-amber-100 bg-amber-50/70 px-1.5 py-2.5 text-center dark:border-amber-900/60 dark:bg-amber-950/30">
                                                            <span class="block text-[8px] font-black uppercase tracking-wider text-amber-600/70 dark:text-amber-400">Month</span>
                                                            <b class="mt-0.5 block text-sm font-black text-slate-800 dark:text-white">{{ $balloon['month'] }}</b>
                                                            <span class="mt-1 block whitespace-nowrap text-[9px] font-black text-amber-800 dark:text-amber-200 sm:text-[10px]">Rs {{ number_format($balloon['amount']) }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3 flex items-center gap-2 rounded-xl bg-amber-50 p-3 text-xs leading-5 text-amber-800 dark:bg-amber-950/40 dark:text-amber-200"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-200 font-black text-amber-800">i</span><span x-show="paymentPlan === 'cash'">You can make the full cash payment after your booking is approved by the office.</span><span x-show="paymentPlan !== 'cash'">You can make the first payment after your booking is approved by the office.</span></div>
                                <button :disabled="!paymentPlan || @js(!$canRequest)" class="w-full rounded-xl px-5 py-3.5 font-black text-white shadow-lg transition disabled:cursor-not-allowed disabled:bg-gray-300 disabled:shadow-none dark:disabled:bg-gray-700 {{ $canRequest?'bg-gradient-to-r from-indigo-600 to-violet-600 shadow-indigo-200 hover:from-indigo-700 hover:to-violet-700':'cursor-not-allowed bg-gray-300 shadow-none dark:bg-gray-700' }}"><span x-show="!paymentPlan">{{ $pendingBooking ? 'Approval pending' : ($available ? 'Choose Cash or Installments' : 'Currently unavailable') }}</span><span x-show="paymentPlan === 'cash'">{{ $pendingBooking ? 'Approval pending' : ($available ? 'Continue with Cash' : 'Currently unavailable') }}</span><span x-show="paymentPlan === 'installment'">{{ $pendingBooking ? 'Approval pending' : ($available ? 'Continue with Installments' : 'Currently unavailable') }}</span></button>

                                <template x-teleport="body">
                                    <div x-show="confirmOpen" x-cloak @keydown.escape.window="if(!submitting) confirmOpen=false" class="fixed inset-0 z-[120] flex items-end justify-center p-0 sm:items-center sm:p-4">
                                        <div x-show="confirmOpen" x-transition.opacity class="absolute inset-0 bg-slate-950/75 backdrop-blur-sm" @click="if(!submitting) confirmOpen=false"></div>
                                        <section x-show="confirmOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-8 opacity-0 sm:scale-95" x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100" class="relative w-full overflow-hidden rounded-t-[2rem] bg-white shadow-2xl sm:max-w-lg sm:rounded-[2rem] dark:bg-slate-900">
                                            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-violet-700 to-slate-950 px-6 pb-7 pt-6 text-white">
                                                <div class="absolute -right-8 -top-12 h-40 w-40 rounded-full bg-fuchsia-400/25 blur-3xl"></div>
                                                <div class="absolute -bottom-16 left-10 h-36 w-36 rounded-full bg-cyan-300/15 blur-3xl"></div>
                                                <div class="relative flex items-start justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl border border-white/20 bg-white/15 shadow-lg backdrop-blur">
                                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12 11.204 3.045a1.125 1.125 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                                                        </span>
                                                        <div><div class="text-[10px] font-black uppercase tracking-[.2em] text-indigo-200">Confirm plan</div><h3 class="mt-1 text-xl font-black" x-text="paymentPlan === 'cash' ? 'Book with cash?' : 'Book with installments?'"></h3></div>
                                                    </div>
                                                    <button type="button" @click="confirmOpen=false" :disabled="submitting" class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-white/10 text-xl text-white/70 transition hover:bg-white/20 hover:text-white">×</button>
                                                </div>
                                                <div class="relative mt-6 flex items-end justify-between gap-4 rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                                                    <div><div class="text-xs font-bold text-indigo-200">{{ $project->name }}</div><div class="mt-1 text-2xl font-black">{{ $package->name }}</div><div class="mt-1 text-xs text-indigo-100" x-text="paymentPlan === 'cash' ? '{{ $package->size_marla }} marla · Cash payment' : '{{ $package->size_marla }} marla · {{ $package->months }} month installment plan'"></div></div>
                                                    <div class="text-right"><div class="text-[9px] font-bold uppercase tracking-wider text-indigo-200" x-text="paymentPlan === 'cash' ? 'Cash price' : 'Installment price'"></div><div class="mt-1 text-lg font-black" x-text="'Rs ' + (paymentPlan === 'cash' ? cashPrice : installmentPrice).toLocaleString()"></div></div>
                                                </div>
                                            </div>

                                            <div class="p-6">
                                                <div x-show="paymentPlan === 'cash'" x-cloak class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/40">
                                                    <div class="text-[10px] font-black uppercase tracking-wider text-emerald-600">Full cash payment</div>
                                                    <div class="mt-1 text-2xl font-black text-emerald-950 dark:text-emerald-200" x-text="money(cashPrice)"></div>
                                                    <div class="mt-1 text-xs text-emerald-700 dark:text-emerald-300">One full payment after office approval.</div>
                                                </div>
                                                <div x-show="paymentPlan === 'installment'" class="grid grid-cols-2 gap-3">
                                                    <div class="rounded-2xl border border-violet-100 bg-violet-50 p-4 dark:border-violet-900 dark:bg-violet-950/40"><div class="text-[10px] font-black uppercase tracking-wider text-violet-500">First payment</div><div class="mt-1 text-lg font-black text-violet-950 dark:text-violet-200">Rs {{ number_format($package->booking_amount) }}</div></div>
                                                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/40"><div class="text-[10px] font-black uppercase tracking-wider text-emerald-600">Monthly payment</div><div class="mt-1 text-lg font-black text-emerald-950 dark:text-emerald-200">Rs {{ number_format($package->monthly_amount) }}</div></div>
                                                </div>
                                                @if($package->balloonPayments())
                                                    <div x-show="paymentPlan === 'installment'" class="mt-3 rounded-2xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-900 dark:bg-amber-950/30">
                                                        <div class="flex items-center gap-2"><span class="grid h-7 w-7 place-items-center rounded-lg bg-amber-500 text-[11px] font-black text-white">B</span><div><div class="text-[10px] font-black uppercase tracking-wider text-amber-700 dark:text-amber-300">Balloon payments</div><div class="text-[9px] text-slate-500 dark:text-slate-400">Additional scheduled payments</div></div></div>
                                                        <div class="mt-3 grid grid-cols-3 gap-2">
                                                            @foreach($package->balloonPayments() as $balloon)
                                                                <div class="rounded-xl border border-amber-100 bg-white px-2 py-2.5 text-center dark:border-amber-900/60 dark:bg-slate-900/70">
                                                                    <span class="block text-[8px] font-black uppercase tracking-wider text-amber-600">Month</span>
                                                                    <b class="mt-0.5 block text-sm font-black text-slate-900 dark:text-white">{{ $balloon['month'] }}</b>
                                                                    <span class="mt-1 block whitespace-nowrap text-[9px] font-black text-amber-800 dark:text-amber-200 sm:text-[10px]">Rs {{ number_format($balloon['amount']) }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="mt-4 flex gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-amber-200 text-xs font-black text-amber-800">i</span>
                                                    <div>
                                                        <div class="text-sm font-black" x-text="paymentPlan === 'cash' ? 'No cash payment is charged now' : 'No payment is charged now'"></div>
                                                        <p x-show="paymentPlan === 'cash'" class="mt-0.5 text-xs leading-5 text-amber-700 dark:text-amber-300">The office will review your request first. Your full cash payment becomes available only after approval.</p>
                                                        <p x-show="paymentPlan === 'installment'" class="mt-0.5 text-xs leading-5 text-amber-700 dark:text-amber-300">The office will review your request first. Your first payment becomes available only after approval.</p>
                                                    </div>
                                                </div>
                                                <div class="mt-6 grid grid-cols-2 gap-3">
                                                    <button type="button" @click="confirmOpen=false" :disabled="submitting" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50 disabled:opacity-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Keep browsing</button>
                                                    <button type="button" @click="submitting=true; $refs.bookingForm.submit()" :disabled="submitting" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-indigo-200 transition hover:from-indigo-700 hover:to-violet-700 disabled:cursor-wait disabled:opacity-70 dark:shadow-none">
                                                        <svg x-show="!submitting" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                        <svg x-show="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z"/></svg>
                                                        <span x-text="submitting ? 'Submitting…' : 'Confirm request'">Confirm request</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </section>
                                    </div>
                                </template>
                            </form>
                        </div>
                    </article>
                @empty<div class="rounded-2xl bg-white p-8 text-center text-sm text-gray-500 shadow-sm dark:bg-gray-800 dark:text-gray-300">No active packages for this project.</div>@endforelse</div>
            </section>
        @empty<div class="rounded-2xl border border-gray-100 bg-white p-12 text-center shadow"><div class="text-lg font-black text-gray-700">No properties available</div><p class="mt-1 text-sm text-gray-400">Please check back later for new projects.</p></div>@endforelse
    </div></div>
</x-app-layout>
