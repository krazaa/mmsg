<x-app-layout>
    <div class="customer-theme-page min-h-screen bg-gradient-to-b from-violet-50 via-slate-50 to-white py-6 sm:py-8">
        <div class="w-full px-3 sm:px-5 lg:px-6">
            @if($portalPreview ?? false)
                <div class="mb-5 flex flex-col gap-3 rounded-xl border border-violet-300 bg-violet-950 px-4 py-3 text-white shadow-xl sm:flex-row sm:items-center sm:justify-between">
                    <div><b class="text-sm">Admin team preview</b><p class="text-xs text-violet-200">Viewing {{ $customer->name }}'s MLM team map in read-only mode.</p></div>
                    <div class="flex gap-2"><a href="{{ route('customers.commissions', $customer) }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-center text-xs font-black text-white">Commissions</a><a href="{{ route('customers.portal', $customer) }}" class="rounded-lg bg-violet-800 px-4 py-2 text-center text-xs font-black text-white">View portal</a><a href="{{ route('customers.show', $customer) }}" class="rounded-lg bg-white px-4 py-2 text-center text-xs font-black text-violet-800">Exit preview</a></div>
                </div>
            @endif
            @include('customer-team-panel')
        </div>
    </div>
</x-app-layout>
