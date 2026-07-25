<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><div class="flex items-center gap-2"><h2 class="text-xl font-black text-slate-900">{{ $customer->name }} — Downline</h2><span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-bold text-violet-700">3 levels</span></div><div class="mt-1 font-mono text-xs font-bold text-violet-600">{{ $customer->referral_code }}</div></div>
            <div class="flex gap-3"><a href="{{ route('customers.show',$customer) }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">← Overview</a><a href="{{ route('customers.index') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700">All customers</a></div>
        </div>
    </x-slot>

    <div class="py-8"><div class="mx-auto max-w-[1600px] space-y-5 px-4 sm:px-6">
        <div class="flex flex-wrap items-center gap-x-7 gap-y-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
            <div><div class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total network</div><div class="text-2xl font-black text-slate-900">{{ $downline->count() }}</div></div>
            @foreach([1=>'Direct',2=>'Second generation',3=>'Third generation'] as $level=>$label)<div class="border-l border-slate-200 pl-6"><div class="text-[10px] font-bold uppercase tracking-widest {{ $level===1?'text-violet-600':($level===2?'text-sky-600':'text-emerald-600') }}">Level {{ $level }}</div><div class="flex items-baseline gap-2"><b class="text-2xl text-slate-900">{{ $downlineCounts->get($level,0) }}</b><span class="text-xs text-slate-400">{{ $label }}</span></div></div>@endforeach
            <div class="ms-auto border-l border-slate-200 pl-6"><div class="text-[10px] font-bold uppercase tracking-widest text-amber-600">Payable</div><div class="text-xl font-black text-amber-700">Rs {{ number_format($payableCommission,2) }}</div></div>
        </div>

        <div x-data="{ zoom: 1, fullscreen: false, center(){ this.$nextTick(()=>this.$refs.network.scrollTo({left:(this.$refs.network.scrollWidth-this.$refs.network.clientWidth)/2,behavior:'smooth'})) } }" x-init="center()" :class="fullscreen?'fixed inset-0 z-[100] rounded-none':'rounded-3xl'" class="overflow-hidden border border-indigo-100 bg-white shadow-xl">
            <div class="flex flex-col gap-3 bg-gradient-to-r from-slate-950 via-indigo-950 to-violet-900 p-4 text-white sm:flex-row sm:items-center sm:justify-between"><div><h3 class="font-black">Referral network</h3><p class="text-xs text-indigo-200">Click any customer to open their downline page</p></div><div class="flex items-center gap-2"><div class="flex rounded-lg bg-white/10 p-1"><button @click="zoom=Math.max(.6,zoom-.1)" class="px-2.5 py-1 font-black">−</button><button @click="zoom=1;center()" class="min-w-14 px-2 text-xs font-bold" x-text="Math.round(zoom*100)+'%'"></button><button @click="zoom=Math.min(1.4,zoom+.1)" class="px-2.5 py-1 font-black">+</button></div><button @click="$refs.network.scrollBy({left:-500,behavior:'smooth'})" class="rounded-lg bg-white/10 px-3 py-2 font-black">←</button><button @click="$refs.network.scrollBy({left:500,behavior:'smooth'})" class="rounded-lg bg-white/10 px-3 py-2 font-black">→</button><button @click="fullscreen=!fullscreen;center()" class="rounded-lg bg-white/10 px-3 py-2 text-xs font-black" x-text="fullscreen?'Exit':'Fullscreen'"></button></div></div>
            <div x-ref="network" :class="fullscreen?'h-[calc(100vh-72px)]':''" class="network-canvas relative overflow-auto p-4">
                <div class="mx-auto flex min-w-max origin-top flex-col items-center pb-3 pt-4" :style="`zoom:${zoom}`">
                    @php($rootInitials=collect(preg_split('/\s+/',trim($customer->name)))->filter()->take(2)->map(fn($part)=>strtoupper(substr($part,0,1)))->join(''))
                    <a href="{{ route('customers.show',$customer) }}" class="network-owner relative z-10 w-44 rounded-xl border-2 border-violet-300 bg-white/95 p-2.5 text-center shadow-lg ring-4 ring-violet-50"><div class="mx-auto flex h-11 w-11 items-center justify-center rounded-lg bg-gradient-to-br from-violet-600 to-indigo-800 text-sm font-black text-white">{{ $rootInitials }}</div><div class="mt-2 truncate font-black text-slate-900">{{ $customer->name }}</div><div class="font-mono text-[9px] font-bold text-violet-600">{{ $customer->referral_code }}</div></a>
                    @if($downlineTree)<div class="h-5 w-px bg-indigo-300"></div><div class="relative flex items-start justify-center gap-1 border-t border-indigo-300">@foreach($downlineTree as $node) @include('customers._downline_node',['node'=>$node]) @endforeach</div>@else<div class="mt-8 rounded-xl border border-dashed bg-white px-8 py-6 text-sm text-slate-500">No downline yet.</div>@endif
                </div>
            </div>
        </div>
    </div></div>
</x-app-layout>
