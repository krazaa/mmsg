<div x-data="{ expanded: true }" class="network-branch relative flex shrink-0 flex-col items-center px-1 pt-6">
    <div class="absolute left-1/2 top-0 h-6 w-0.5 -translate-x-1/2 bg-gradient-to-b from-indigo-400 to-indigo-200"></div>
    <a href="{{ route('customers.downline',$node['user']) }}" class="network-node group relative z-10 w-32 overflow-hidden rounded-lg border border-white bg-white/95 text-left shadow ring-1 ring-slate-200 backdrop-blur transition duration-200 hover:-translate-y-1 hover:ring-2 hover:ring-indigo-400 hover:shadow-lg">
        @php($initials = collect(preg_split('/\s+/', trim($node['user']->name)))->filter()->take(2)->map(fn($part)=>strtoupper(substr($part,0,1)))->join(''))
        <div class="h-1 {{ $node['level'] === 1 ? 'bg-violet-500' : ($node['level'] === 2 ? 'bg-sky-500' : 'bg-emerald-500') }}"></div>
        <div class="p-2">
            <div class="flex items-center gap-1.5">
                <div class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-gradient-to-br {{ $node['level'] === 1 ? 'from-indigo-500 to-violet-600' : ($node['level'] === 2 ? 'from-sky-500 to-indigo-500' : 'from-emerald-500 to-teal-600') }} text-[11px] font-black text-white shadow">{{ $initials }}<i class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-white {{ $node['user']->status ? 'bg-emerald-400' : 'bg-slate-300' }}"></i></div>
                <div class="min-w-0">
                    <div class="truncate text-[11px] font-black text-slate-900 group-hover:text-indigo-700">{{ $node['user']->name }}</div>
                    <div class="truncate font-mono text-[5px] font-bold text-violet-600">{{ $node['user']->referral_code }}</div>
                </div>
            </div>
            <div class="mt-1.5 rounded bg-slate-50 px-1.5 py-1 text-center"><div class="text-[8px] font-bold text-slate-400">L{{ $node['level'] }} · {{ $node['user']->bookings_count }} booking</div><div class="mt-0.5 text-[7px] font-bold uppercase tracking-wide text-amber-500">Payable commission</div><div class="text-[10px] font-black text-amber-700">Rs {{ number_format($node['user']->payable_commission ?? 0, 0) }}</div></div>
        </div>
    </a>

    @if($node['children'])
        <button type="button" @click.prevent="expanded=!expanded" class="relative z-20 mt-1 flex items-center gap-1 rounded-full border border-indigo-200 bg-white px-1.5 text-[8px] font-black text-indigo-700 shadow-sm hover:bg-indigo-50"><span x-text="expanded ? '−' : '+'"></span>{{ count($node['children']) }}</button>
        <div x-show="expanded" class="h-4 w-0.5 bg-indigo-300"></div>
        <div x-show="expanded" class="network-children relative flex items-start justify-center gap-2 border-t-2 border-indigo-200">
            @foreach($node['children'] as $child)
                @include('customers._downline_node',['node'=>$child])
            @endforeach
        </div>
    @endif
</div>
