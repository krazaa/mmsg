<x-app-layout>

    <div class="min-h-screen bg-gradient-to-b from-indigo-50/80 via-slate-50 to-white py-6 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950 sm:py-8"><div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6">
        @if($errors->any())<div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800 shadow-sm"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-600 text-white">!</span>{{ $errors->first() }}</div>@endif
        @if($pendingBooking)<div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900 shadow-sm"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-500 font-black text-white">!</span><div><div class="font-black">Your booking request {{ $pendingBooking->booking_number }} is awaiting office approval.</div><p class="mt-1 text-amber-800">You can submit another plot request after this request is approved or cancelled.</p></div></div>@endif

        @php($packageCount = $projects->sum(fn ($project) => $project->packages->count()))
        <section class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-950 via-indigo-700 to-violet-600 p-7 text-white shadow-2xl shadow-indigo-200 dark:shadow-none sm:p-10">
            <div class="absolute -right-20 -top-24 -z-10 h-80 w-80 rounded-full bg-fuchsia-400/30 blur-3xl"></div><div class="absolute -bottom-28 left-1/3 -z-10 h-64 w-64 rounded-full bg-cyan-300/20 blur-3xl"></div>
            <div class="max-w-3xl"><span class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-indigo-100 backdrop-blur">Property marketplace</span><h3 class="mt-5 text-3xl font-black tracking-tight sm:text-5xl">Find a place to build your future.</h3><p class="mt-4 max-w-2xl text-sm leading-6 text-indigo-100 sm:text-base">Compare payment plans, select your ideal plot size, and send a secure booking request. No payment is charged until the office reviews your selection.</p>
                <div class="mt-7 flex flex-wrap gap-3"><div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur"><div class="text-2xl font-black">{{ $projects->count() }}</div><div class="text-xs text-indigo-100">Active projects</div></div><div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur"><div class="text-2xl font-black">{{ $packageCount }}</div><div class="text-xs text-indigo-100">Available plans</div></div><div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur"><div class="text-2xl font-black">Rs {{ number_format($dueNow, 2) }}</div><div class="text-xs text-indigo-100">Your current amount due</div></div></div>
            </div>
        </section>

        <section class="grid gap-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:grid-cols-3 sm:p-5">
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
                    <article class="group relative overflow-hidden rounded-3xl border border-indigo-100 bg-white shadow-lg shadow-slate-200/70 transition duration-200 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-2xl dark:border-gray-700 dark:bg-gray-800 dark:shadow-none">
                        <div class="absolute right-0 top-0 h-28 w-28 rounded-bl-[5rem] bg-gradient-to-br from-indigo-100 to-violet-100 opacity-80 dark:from-indigo-900 dark:to-violet-900"></div>
                        <div class="relative p-6"><div class="flex items-start justify-between gap-4"><div><div class="text-xs font-black uppercase tracking-[0.16em] text-indigo-600 dark:text-indigo-300">Property plan</div><h4 class="mt-2 text-3xl font-black tracking-tight text-gray-950 dark:text-white">{{ $package->name }}</h4><div class="mt-2 flex items-center gap-2 text-sm font-semibold text-gray-500 dark:text-gray-300"><span class="rounded-md bg-indigo-50 px-2 py-1 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">{{ $package->size_marla }} marla</span><span>·</span><span>{{ $package->months }} months</span></div></div><span class="relative rounded-full px-2.5 py-1 text-xs font-black {{ $available?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-700' }}">{{ $available?'Available':'Full' }}</span></div>

                            <div class="my-6 rounded-2xl bg-gradient-to-br from-gray-50 to-indigo-50 p-4 dark:from-gray-900 dark:to-indigo-950"><div class="text-xs font-bold uppercase tracking-wide text-gray-400">Total property price</div><div class="mt-1 text-2xl font-black text-gray-950 dark:text-white">Rs {{ number_format($package->total_price, 2) }}</div></div>

                            <div class="grid grid-cols-2 gap-3"><div class="rounded-xl border border-gray-100 p-3 dark:border-gray-700"><div class="text-xs text-gray-500 dark:text-gray-400">First payment</div><div class="mt-1 text-sm font-black text-gray-900 dark:text-white">Rs {{ number_format($package->booking_amount, 2) }}</div></div><div class="rounded-xl border border-gray-100 p-3 dark:border-gray-700"><div class="text-xs text-gray-500 dark:text-gray-400">Monthly payment</div><div class="mt-1 text-sm font-black text-gray-900 dark:text-white">Rs {{ number_format($package->monthly_amount, 2) }}</div></div></div>

                            <div class="mt-5 flex items-center gap-2 rounded-xl bg-amber-50 p-3 text-xs leading-5 text-amber-800 dark:bg-amber-950/40 dark:text-amber-200"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-200 font-black text-amber-800">i</span>You can make the first payment after your booking is approved by the office.</div>
                            <form x-data="{ confirmOpen: false, submitting: false }" x-ref="bookingForm" method="POST" action="{{ route('customer.bookings.store') }}" @submit.prevent="confirmOpen = true" class="mt-5">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $package->id }}">
                                <button class="w-full rounded-xl px-5 py-3.5 font-black text-white shadow-lg transition {{ $canRequest?'bg-gradient-to-r from-indigo-600 to-violet-600 shadow-indigo-200 hover:from-indigo-700 hover:to-violet-700':'cursor-not-allowed bg-gray-300 shadow-none dark:bg-gray-700' }}" @disabled(!$canRequest)>{{ $pendingBooking ? 'Approval pending' : ($available ? 'Request '.$package->name : 'Currently unavailable') }}</button>

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
                                                        <div><div class="text-[10px] font-black uppercase tracking-[.2em] text-indigo-200">Confirm your selection</div><h3 class="mt-1 text-xl font-black">Submit booking request?</h3></div>
                                                    </div>
                                                    <button type="button" @click="confirmOpen=false" :disabled="submitting" class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-white/10 text-xl text-white/70 transition hover:bg-white/20 hover:text-white">×</button>
                                                </div>
                                                <div class="relative mt-6 flex items-end justify-between gap-4 rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur">
                                                    <div><div class="text-xs font-bold text-indigo-200">{{ $project->name }}</div><div class="mt-1 text-2xl font-black">{{ $package->name }}</div><div class="mt-1 text-xs text-indigo-100">{{ $package->size_marla }} marla · {{ $package->months }} month plan</div></div>
                                                    <div class="text-right"><div class="text-[9px] font-bold uppercase tracking-wider text-indigo-200">Total price</div><div class="mt-1 text-lg font-black">Rs {{ number_format($package->total_price) }}</div></div>
                                                </div>
                                            </div>

                                            <div class="p-6">
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div class="rounded-2xl border border-violet-100 bg-violet-50 p-4 dark:border-violet-900 dark:bg-violet-950/40"><div class="text-[10px] font-black uppercase tracking-wider text-violet-500">First payment</div><div class="mt-1 text-lg font-black text-violet-950 dark:text-violet-200">Rs {{ number_format($package->booking_amount) }}</div></div>
                                                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 dark:border-emerald-900 dark:bg-emerald-950/40"><div class="text-[10px] font-black uppercase tracking-wider text-emerald-600">Monthly payment</div><div class="mt-1 text-lg font-black text-emerald-950 dark:text-emerald-200">Rs {{ number_format($package->monthly_amount) }}</div></div>
                                                </div>
                                                <div class="mt-4 flex gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                                                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-amber-200 text-xs font-black text-amber-800">i</span>
                                                    <div><div class="text-sm font-black">No payment is charged now</div><p class="mt-0.5 text-xs leading-5 text-amber-700 dark:text-amber-300">The office will review your request first. Your first payment becomes available only after approval.</p></div>
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
