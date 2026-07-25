<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><div class="text-xs font-black uppercase tracking-[.18em] text-indigo-600 dark:text-indigo-300">Settings</div><h2 class="mt-1 text-xl font-black text-gray-900 dark:text-white">Payment Gateway</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Secure API configuration for JazzCash, Easypaisa and Binance Pay.</p></div>
            <a href="{{ route('payment-methods.index') }}" class="text-sm font-bold text-indigo-600 dark:text-indigo-300">Manual payment methods →</a>
        </div>
    </x-slot>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-orange-50 py-8 dark:from-slate-950 dark:via-slate-950 dark:to-indigo-950/30">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6">
            @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800">{{ $errors->first() }}</div>@endif
            @include('payment-methods._gateways')
        </div>
    </div>
</x-app-layout>
