<section class="overflow-hidden rounded-3xl border border-violet-100 bg-white shadow-xl shadow-violet-100/50">
    <div class="grid lg:grid-cols-[.8fr_1.2fr]">
        <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-indigo-700 to-indigo-950 p-6 text-white sm:p-8">
            <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-fuchsia-400/20 blur-2xl"></div>
            <div class="relative">
                <div class="text-xs font-black uppercase tracking-[.2em] text-violet-200">My referral network</div>
                <h3 class="mt-2 text-2xl font-black">Invite. Grow. Earn.</h3>
                <p class="mt-2 text-sm leading-6 text-indigo-100">Share your code with new customers. Their account will be connected to your referral network when they register.</p>
                <div x-data="{ copied:false }" class="mt-6 rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur">
                    <div class="text-[10px] font-bold uppercase tracking-widest text-indigo-200">Your referral code</div>
                    <div class="mt-2 flex items-center justify-between gap-3"><b class="font-mono text-xl">{{ $customer->referral_code }}</b><button type="button" @click="navigator.clipboard.writeText('{{ $customer->referral_code }}');copied=true;setTimeout(()=>copied=false,1500)" class="rounded-lg bg-white px-3 py-2 text-xs font-black text-indigo-700" x-text="copied?'Copied ✓':'Copy code'">Copy code</button></div>
                </div>
                <div class="mt-4 text-xs text-indigo-200">Sponsored by <b class="text-white">{{ $customer->referralAgent?->name ?? 'Direct Sales' }}</b></div>
            </div>
        </div>
        <div class="p-6 sm:p-8">
            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-xl bg-violet-50 p-3"><span class="text-[10px] font-bold uppercase text-violet-500">Direct referrals</span><b class="mt-1 block text-2xl text-violet-900">{{ $directReferrals->count() }}</b></div>
                <div class="rounded-xl bg-amber-50 p-3"><span class="text-[10px] font-bold uppercase text-amber-600">Payable</span><b class="mt-1 block text-lg text-amber-900">Rs {{ number_format($referralSummary?->payable ?? 0, 0) }}</b></div>
                <div class="rounded-xl bg-emerald-50 p-3"><span class="text-[10px] font-bold uppercase text-emerald-600">Paid</span><b class="mt-1 block text-lg text-emerald-900">Rs {{ number_format($referralSummary?->paid ?? 0, 0) }}</b></div>
            </div>
            <div class="mt-5 grid grid-cols-3 gap-3 border-t border-slate-100 pt-5">
                @foreach([1 => 'Direct', 2 => 'Generation 2', 3 => 'Generation 3'] as $level => $label)
                    <div class="rounded-xl border border-slate-100 p-3 text-center"><div class="text-[10px] font-bold uppercase text-slate-400">Level {{ $level }}</div><div class="mt-1 text-2xl font-black text-slate-900">{{ $downlineCounts->get($level, 0) }}</div><div class="text-[10px] text-slate-500">{{ $label }}</div></div>
                @endforeach
            </div>
        </div>
    </div>

    <div x-data="{ zoom: 1, fullscreen: false, center(){ this.$nextTick(() => this.$refs.network.scrollTo({ left: (this.$refs.network.scrollWidth-this.$refs.network.clientWidth)/2, behavior: 'smooth' })) } }" x-init="center()" :class="fullscreen ? 'fixed inset-0 z-[100] rounded-none' : ''" class="overflow-hidden border-t border-indigo-100 bg-white">
        <div class="flex flex-col gap-3 bg-gradient-to-r from-slate-950 via-indigo-950 to-violet-900 p-4 text-white sm:flex-row sm:items-center sm:justify-between">
            <div><h3 class="font-black">My team</h3><p class="text-xs text-indigo-200">Your referral network across three levels</p></div>
            <div class="flex items-center gap-2"><div class="flex rounded-lg bg-white/10 p-1"><button type="button" @click="zoom=Math.max(.6,zoom-.1)" class="px-2.5 py-1 font-black">−</button><button type="button" @click="zoom=1;center()" class="min-w-14 px-2 text-xs font-bold" x-text="Math.round(zoom*100)+'%'">100%</button><button type="button" @click="zoom=Math.min(1.4,zoom+.1)" class="px-2.5 py-1 font-black">+</button></div><button type="button" @click="$refs.network.scrollBy({left:-500,behavior:'smooth'})" class="rounded-lg bg-white/10 px-3 py-2 font-black">←</button><button type="button" @click="$refs.network.scrollBy({left:500,behavior:'smooth'})" class="rounded-lg bg-white/10 px-3 py-2 font-black">→</button><button type="button" @click="fullscreen=!fullscreen;center()" class="rounded-lg bg-white/10 px-3 py-2 text-xs font-black" x-text="fullscreen?'Exit':'Fullscreen'">Fullscreen</button></div>
        </div>
        <div x-ref="network" :class="fullscreen ? 'h-[calc(100vh-72px)]' : 'min-h-80'" class="network-canvas relative overflow-auto p-4">
            <div class="mx-auto flex min-w-max origin-top flex-col items-center pb-5 pt-4" :style="`zoom:${zoom}`">
                @php($rootInitials = collect(preg_split('/\s+/', trim($customer->name)))->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->join(''))
                <div class="network-owner group relative z-10 w-72 overflow-hidden rounded-2xl border border-indigo-100 bg-white text-left shadow-xl shadow-indigo-200/70 ring-2 ring-white transition duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="h-1 bg-gradient-to-r from-indigo-600 via-violet-500 to-cyan-400"></div>
                    <div class="relative overflow-hidden bg-gradient-to-r from-slate-950 via-indigo-950 to-violet-900 p-3.5">
                        <div class="absolute -right-7 -top-10 h-24 w-24 rounded-full bg-cyan-300/15 blur-2xl"></div>
                        <div class="relative flex items-center gap-3">
                            <div class="relative grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-cyan-400 via-indigo-500 to-violet-700 text-sm font-black text-white shadow-lg ring-2 ring-white/20">
                                {{ $rootInitials }}
                                <span class="absolute -bottom-1 -right-1 h-3.5 w-3.5 rounded-full border-2 border-indigo-950 bg-emerald-400"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-2 py-0.5 text-[8px] font-black uppercase tracking-[.14em] text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>Team owner</div>
                                <div class="mt-1 truncate text-sm font-black text-white">{{ $customer->name }}</div>
                                <div class="mt-0.5 font-mono text-[9px] font-bold tracking-wider text-indigo-200">{{ $customer->referral_code }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 divide-x divide-indigo-100 bg-gradient-to-b from-white to-indigo-50/50">
                        @foreach([1, 2, 3] as $level)
                            <div class="px-2 py-2.5 text-center">
                                <div class="text-[8px] font-black uppercase tracking-wider text-indigo-400">L{{ $level }} commission</div>
                                <div class="mt-1 truncate text-[11px] font-black text-slate-900">Rs {{ number_format((float) $levelCommissions->get($level, 0), 0) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @if($downlineTree)
                    <div class="h-5 w-px bg-indigo-300"></div>
                    <div class="relative flex items-start justify-center gap-1 border-t border-indigo-300">@foreach($downlineTree as $node) @include('customer-team-node', ['node' => $node]) @endforeach</div>
                @else
                    <div class="mt-8 rounded-xl border border-dashed border-slate-300 bg-white px-8 py-6 text-sm text-slate-500">No team members yet. Share your referral code to grow your map.</div>
                @endif
            </div>
        </div>
    </div>
</section>
