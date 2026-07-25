<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div><h2 class="text-xl font-bold text-gray-900 dark:text-white">Payment method settings</h2><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Control which methods customers can select in their portal.</p></div>
            <a href="{{ route('payments.index') }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">← Payment history</a>
        </div>
    </x-slot>
    <div class="py-8"><div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
        @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('payment-methods.store') }}" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">@csrf
            <div class="mb-4"><h3 class="font-bold text-gray-900 dark:text-white">Add payment method</h3><p class="text-xs text-gray-500 dark:text-gray-400">Use a lowercase code with underscores. Avoid changing it after transactions exist.</p></div>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-[1.2fr_1fr_100px_auto_auto_auto] lg:items-end">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Display name<input name="name" value="{{ old('name') }}" required placeholder="e.g. JazzCash" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900"></label>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Code<input name="code" value="{{ old('code') }}" required pattern="[a-z0-9_]+" placeholder="jazzcash" class="mt-1.5 w-full rounded-xl border-gray-300 font-mono lowercase dark:border-gray-600 dark:bg-gray-900"></label>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Order<input type="number" name="sort_order" value="{{ old('sort_order', 50) }}" min="0" max="999" required class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900"></label>
                <label class="flex items-center gap-2 pb-3 text-sm font-semibold text-gray-700 dark:text-gray-200"><input type="hidden" name="customer_portal" value="0"><input type="checkbox" name="customer_portal" value="1" @checked(old('customer_portal', true)) class="rounded border-gray-300 text-indigo-600"> Portal</label>
                <label class="flex items-center gap-2 pb-3 text-sm font-semibold text-gray-700 dark:text-gray-200"><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" @checked(old('status', true)) class="rounded border-gray-300 text-indigo-600"> Active</label>
                <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">Add</button>
            </div>
            <div class="mt-4 grid gap-4 border-t border-gray-100 pt-4 sm:grid-cols-2 lg:grid-cols-3 dark:border-gray-700">
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Bank / provider<input name="bank_name" value="{{ old('bank_name') }}" placeholder="Bank name or crypto provider" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900"></label>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Account title<input name="account_title" value="{{ old('account_title') }}" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900"></label>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Account number / IBAN<input name="account_number" value="{{ old('account_number') }}" class="mt-1.5 w-full rounded-xl border-gray-300 font-mono dark:border-gray-600 dark:bg-gray-900"></label>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Crypto network<input name="crypto_network" value="{{ old('crypto_network') }}" placeholder="e.g. TRC20" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900"></label>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 sm:col-span-2">Wallet address<input name="wallet_address" value="{{ old('wallet_address') }}" class="mt-1.5 w-full rounded-xl border-gray-300 font-mono dark:border-gray-600 dark:bg-gray-900"></label>
                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 sm:col-span-2 lg:col-span-3">Customer instructions<textarea name="instructions" rows="2" class="mt-1.5 w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900" placeholder="Any instructions shown with this method">{{ old('instructions') }}</textarea></label>
            </div>
        </form>

        <div class="space-y-3">
            @forelse($methods as $method)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <form method="POST" action="{{ route('payment-methods.update', $method) }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-[1.2fr_1fr_100px_auto_auto_auto] lg:items-end">@csrf @method('PUT')
                        <label class="text-xs font-bold uppercase tracking-wide text-gray-400">Display name<input name="name" value="{{ $method->name }}" required class="mt-1.5 w-full rounded-xl border-gray-300 text-sm font-semibold text-gray-900 dark:border-gray-600 dark:bg-gray-900 dark:text-white"></label>
                        <label class="text-xs font-bold uppercase tracking-wide text-gray-400">Code<input name="code" value="{{ $method->code }}" required pattern="[a-z0-9_]+" class="mt-1.5 w-full rounded-xl border-gray-300 font-mono text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></label>
                        <label class="text-xs font-bold uppercase tracking-wide text-gray-400">Order<input type="number" name="sort_order" value="{{ $method->sort_order }}" min="0" max="999" required class="mt-1.5 w-full rounded-xl border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white"></label>
                        <label class="flex items-center gap-2 pb-3 text-sm font-semibold text-gray-700 dark:text-gray-200"><input type="hidden" name="customer_portal" value="0"><input type="checkbox" name="customer_portal" value="1" @checked($method->customer_portal) class="rounded border-gray-300 text-indigo-600"> Portal</label>
                        <label class="flex items-center gap-2 pb-3 text-sm font-semibold text-gray-700 dark:text-gray-200"><input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" @checked($method->status) class="rounded border-gray-300 text-indigo-600"> Active</label>
                        <button class="rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-bold text-white hover:bg-gray-700 dark:bg-indigo-600">Save</button>
                        <div class="grid gap-3 border-t border-gray-100 pt-3 md:col-span-2 md:grid-cols-2 lg:col-span-6 lg:grid-cols-3 dark:border-gray-700">
                            <label class="text-xs font-bold uppercase tracking-wide text-gray-400">Bank / provider<input name="bank_name" value="{{ $method->bank_name }}" class="mt-1.5 w-full rounded-xl border-gray-300 text-sm normal-case dark:border-gray-600 dark:bg-gray-900 dark:text-white"></label>
                            <label class="text-xs font-bold uppercase tracking-wide text-gray-400">Account title<input name="account_title" value="{{ $method->account_title }}" class="mt-1.5 w-full rounded-xl border-gray-300 text-sm normal-case dark:border-gray-600 dark:bg-gray-900 dark:text-white"></label>
                            <label class="text-xs font-bold uppercase tracking-wide text-gray-400">Account number / IBAN<input name="account_number" value="{{ $method->account_number }}" class="mt-1.5 w-full rounded-xl border-gray-300 font-mono text-sm normal-case dark:border-gray-600 dark:bg-gray-900 dark:text-white"></label>
                            <label class="text-xs font-bold uppercase tracking-wide text-gray-400">Crypto network<input name="crypto_network" value="{{ $method->crypto_network }}" class="mt-1.5 w-full rounded-xl border-gray-300 text-sm normal-case dark:border-gray-600 dark:bg-gray-900 dark:text-white"></label>
                            <label class="text-xs font-bold uppercase tracking-wide text-gray-400 md:col-span-2">Wallet address<input name="wallet_address" value="{{ $method->wallet_address }}" class="mt-1.5 w-full rounded-xl border-gray-300 font-mono text-sm normal-case dark:border-gray-600 dark:bg-gray-900 dark:text-white"></label>
                            <label class="text-xs font-bold uppercase tracking-wide text-gray-400 md:col-span-2 lg:col-span-3">Customer instructions<textarea name="instructions" rows="2" class="mt-1.5 w-full rounded-xl border-gray-300 text-sm font-normal normal-case dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ $method->instructions }}</textarea></label>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('payment-methods.destroy', $method) }}" onsubmit="return confirm('Delete this payment method?')" class="mt-2 flex justify-end border-t border-gray-100 pt-2 dark:border-gray-700">@csrf @method('DELETE')<button class="rounded-lg px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50">Delete</button></form>
                </div>
            @empty
                <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-white p-12 text-center text-sm text-gray-500">No payment methods configured.</div>
            @endforelse
        </div>
    </div></div>
</x-app-layout>
