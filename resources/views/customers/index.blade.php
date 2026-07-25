<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold">Customers</h2>
                <p class="mt-1 text-sm text-gray-500">Customer accounts, referrals and commission partners in one place.</p>
            </div>
            <a href="{{ route('customers.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Add customer</a>
        </div>
    </x-slot>

    <div class="py-8"><div class="mx-auto max-w-7xl space-y-5 px-4">
        @if(session('success'))<div class="rounded-lg bg-green-100 p-4 text-green-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-lg bg-red-100 p-4 text-red-800">{{ $errors->first() }}</div>@endif

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('customers.index', array_filter(['search' => request('search'), 'referral_code' => request('referral_code')])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ request('type') !== 'agent' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 shadow-sm' }}">All customers</a>
            <a href="{{ route('customers.index', array_filter(['type' => 'agent', 'search' => request('search'), 'referral_code' => request('referral_code')])) }}" class="rounded-full px-4 py-2 text-sm font-semibold {{ request('type') === 'agent' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 shadow-sm' }}">Commission customers</a>
        </div>

        <form class="rounded-xl bg-white p-4 shadow-sm">
            @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_240px_auto_auto]"><input name="search" value="{{ request('search') }}" placeholder="Name, file no, CNIC, phone or email" class="min-w-0 rounded-lg border-gray-300"><input name="referral_code" value="{{ request('referral_code') }}" placeholder="Referral code" class="rounded-lg border-gray-300 font-mono uppercase"><button class="rounded-lg bg-indigo-600 px-5 py-2 font-semibold text-white">Filter</button>@if(request()->filled('search') || request()->filled('referral_code'))<a href="{{ route('customers.index',array_filter(['type'=>request('type')])) }}" class="rounded-lg border border-gray-300 px-5 py-2 text-center font-semibold text-gray-600">Clear</a>@endif</div>
        </form>

        <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
            <table class="w-full min-w-[1050px] text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500"><tr><th class="p-4">Customer</th><th>Contact</th><th>Referral / sponsor</th><th class="px-4 text-center">Bookings</th><th>Payments</th><th class="px-4 text-right">Commission payable</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($customers as $customer)
                    @php($isAgent = (bool) $customer->referral_agent_id)
                    <tr class="border-t align-top hover:bg-gray-50/70">
                        <td class="p-4"><a href="{{ route('customers.show',$customer) }}" class="font-semibold text-indigo-600">{{ $customer->name }}</a>@if($customer->file_no)<div class="mt-1 inline-flex rounded bg-slate-100 px-2 py-0.5 font-mono text-[11px] font-bold text-slate-700">File: {{ $customer->file_no }}</div>@endif<div class="mt-1 text-xs text-gray-500">{{ $customer->cnic ?: 'CNIC not provided' }}</div><div class="mt-1 font-mono text-xs font-semibold text-violet-600">{{ $customer->referral_code }}</div></td>
                        <td class="py-4"><div>{{ $customer->phone }}</div><div class="mt-1 text-xs text-gray-500">{{ $customer->email ?: 'No email' }}</div></td>
                        <td class="py-4">@if($isAgent)<div class="font-medium">{{ $customer->user->referral?->sponsor?->name ?? 'Top level' }}</div><div class="text-xs text-gray-500">Commission sponsor</div>@elseif($customer->referralAgent)<div class="font-medium">{{ $customer->referralAgent->name }}</div>@else Direct @endif</td>
                        <td class="px-4 py-4 text-center font-semibold tabular-nums">{{ $customer->bookings_count }}</td>
                        <td class="py-4"><a href="{{ route('customers.show',$customer) }}" class="font-semibold text-green-700">{{ $customer->payments_count }} · History</a></td>
                        <td class="px-4 py-4 text-right font-semibold tabular-nums {{ $isAgent && $customer->payable_commission > 0 ? 'text-amber-700' : 'text-gray-400' }}">{{ $isAgent ? 'Rs '.number_format($customer->payable_commission, 2) : '—' }}</td>
                        <td class="py-4"><span class="rounded-full px-2 py-1 text-xs {{ $customer->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $customer->status?'Active':'Inactive' }}</span></td>
                        <td class="py-4 pr-4"><div class="flex gap-3"><a href="{{ route('customers.show',$customer) }}" class="font-medium text-indigo-600">Manage</a><a href="{{ route('customers.edit',$customer) }}" class="text-amber-700">Edit</a>
                            {{-- <form method="POST" action="{{ route('customers.destroy',$customer) }}" onsubmit="return confirm('Delete customer?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button>
                            </form> --}}
                        </div></td>
                    </tr>
                @empty<tr><td colspan="9" class="p-10 text-center text-gray-500">No customers found.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        {{ $customers->links() }}
    </div></div>
</x-app-layout>
