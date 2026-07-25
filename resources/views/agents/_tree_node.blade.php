<div class="{{ $node['level'] > 1 ? 'ms-8 border-s-2 border-gray-200 ps-4' : '' }} mb-3">
    <div class="rounded-lg border p-4">
        <div class="flex items-start justify-between gap-3"><div><div class="text-xs font-semibold uppercase text-gray-500">Level {{ $node['level'] }}</div><a href="{{ route('agents.show',$node['user']) }}" class="font-bold text-indigo-600">{{ $node['user']->name }}</a><div class="text-xs font-semibold text-gray-600">{{ $node['user']->referral_code }}</div><div class="text-xs text-gray-500">{{ $node['user']->email }} · {{ $node['user']->status?'Active':'Inactive' }}</div></div><div class="text-right text-sm"><div class="text-gray-500">Payable</div><div class="font-semibold text-green-700">Rs {{ number_format($node['user']->payable_commission??0,2) }}</div></div></div>
    </div>
    @foreach($node['children'] as $child) @include('agents._tree_node',['node'=>$child]) @endforeach
</div>
