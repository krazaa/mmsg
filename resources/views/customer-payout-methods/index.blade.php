<x-app-layout>
    <div class="customer-theme-page min-h-screen bg-slate-50 py-6 dark:bg-slate-950 sm:py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6">
            @if(session('success'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
            <section class="customer-theme-account-hero overflow-hidden rounded-3xl bg-gradient-to-r from-slate-950 via-indigo-950 to-violet-800 p-5 text-white shadow-xl sm:p-8">
                <div class="text-[10px] font-black uppercase tracking-[.2em] text-indigo-300">My account</div>
                <h1 class="mt-2 text-3xl font-black">Payout methods</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-indigo-100">Save multiple bank accounts or wallets and choose one default method for withdrawals.</p>
            </section>
            @if($errors->any())<div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700">{{ $errors->first() }}</div>@endif

            <div class="grid items-start gap-6 lg:grid-cols-[380px_minmax(0,1fr)]">
                <section class="overflow-hidden rounded-3xl border border-indigo-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-100 p-5 dark:border-slate-800"><h2 class="font-black text-slate-950 dark:text-white">Add payout method</h2><p class="mt-1 text-xs text-slate-500">Your first method becomes the default automatically.</p></div>
                    <form method="POST" action="{{ route('customer.payout-methods.store') }}" class="space-y-4 p-5">@csrf
                        <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Method name<input name="label" value="{{ old('label') }}" required maxlength="60" class="mt-1.5 w-full rounded-xl border-slate-300 py-3 text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white" placeholder="My main bank account"></label>
                        <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Method<select name="payment_method" required class="mt-1.5 w-full rounded-xl border-slate-300 py-3 text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white"><option value="">Select method</option>@foreach(['bank_transfer'=>'Bank transfer','raast'=>'Raast','easypaisa'=>'Easypaisa','jazzcash'=>'JazzCash','crypto'=>'Crypto wallet'] as $value=>$label)<option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>@endforeach</select></label>
                        <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Account title<input name="account_title" value="{{ old('account_title') }}" required maxlength="100" class="mt-1.5 w-full rounded-xl border-slate-300 py-3 text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white" placeholder="Name on account"></label>
                        <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Account / wallet number<input name="account_number" value="{{ old('account_number') }}" required maxlength="150" class="mt-1.5 w-full rounded-xl border-slate-300 py-3 font-mono text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white" placeholder="IBAN, mobile number or address"></label>
                        <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Bank / provider <span class="font-normal normal-case">(optional)</span><input name="bank_name" value="{{ old('bank_name') }}" maxlength="100" class="mt-1.5 w-full rounded-xl border-slate-300 py-3 text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white"></label>
                        <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Network <span class="font-normal normal-case">(crypto only)</span><input name="network" value="{{ old('network') }}" maxlength="100" class="mt-1.5 w-full rounded-xl border-slate-300 py-3 text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white"></label>
                        <label class="flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200"><input type="hidden" name="is_default" value="0"><input type="checkbox" name="is_default" value="1" @checked(old('is_default')) class="rounded border-slate-300 text-indigo-600"> Make this my default method</label>
                        <button class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white hover:bg-indigo-700">Add payout method</button>
                    </form>
                </section>

                <section class="space-y-4">
                    <div><h2 class="text-xl font-black text-slate-950 dark:text-white">Saved methods</h2><p class="mt-1 text-xs text-slate-500">{{ $methods->count() }} method{{ $methods->count() === 1 ? '' : 's' }} saved</p></div>
                    @forelse($methods as $method)
                        <article class="overflow-hidden rounded-2xl border {{ $method->is_default ? 'border-emerald-300 ring-2 ring-emerald-100' : 'border-slate-200' }} bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 p-5 dark:border-slate-800">
                                <div><div class="flex items-center gap-2"><h3 class="font-black text-slate-950 dark:text-white">{{ $method->label }}</h3>@if($method->is_default)<span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[9px] font-black uppercase text-emerald-700">Default</span>@endif</div><p class="mt-1 text-xs text-slate-500">{{ ucwords(str_replace('_', ' ', $method->payment_method)) }}</p></div>
                                <div class="flex flex-wrap justify-end gap-2">
                                    @unless($method->is_default)<form method="POST" action="{{ route('customer.payout-methods.default', $method) }}">@csrf @method('PATCH')<button class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700">Set default</button></form>@endunless
                                    <form method="POST" action="{{ route('customer.payout-methods.destroy', $method) }}" onsubmit="return confirm('Remove this payout method?')">@csrf @method('DELETE')<button class="rounded-lg bg-rose-50 px-3 py-2 text-xs font-black text-rose-700">Remove</button></form>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('customer.payout-methods.update', $method) }}" class="grid gap-3 p-5 sm:grid-cols-2">@csrf @method('PUT')
                                <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Method name<input name="label" value="{{ $method->label }}" required maxlength="60" class="mt-1 w-full rounded-xl border-slate-300 text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white"></label>
                                <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Method<select name="payment_method" required class="mt-1 w-full rounded-xl border-slate-300 text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white">@foreach(['bank_transfer'=>'Bank transfer','raast'=>'Raast','easypaisa'=>'Easypaisa','jazzcash'=>'JazzCash','crypto'=>'Crypto wallet'] as $value=>$label)<option value="{{ $value }}" @selected($method->payment_method === $value)>{{ $label }}</option>@endforeach</select></label>
                                <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Account title<input name="account_title" value="{{ $method->account_title }}" required maxlength="100" class="mt-1 w-full rounded-xl border-slate-300 text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white"></label>
                                <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Account / wallet number<input name="account_number" value="{{ $method->account_number }}" required maxlength="150" class="mt-1 w-full rounded-xl border-slate-300 font-mono text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white"></label>
                                <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Bank / provider<input name="bank_name" value="{{ $method->bank_name }}" maxlength="100" class="mt-1 w-full rounded-xl border-slate-300 text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white"></label>
                                <label class="text-[10px] font-black uppercase tracking-wide text-slate-500">Network<input name="network" value="{{ $method->network }}" maxlength="100" class="mt-1 w-full rounded-xl border-slate-300 text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white"></label>
                                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-black text-white dark:bg-indigo-600 sm:col-span-2">Save changes</button>
                            </form>
                        </article>
                    @empty
                        <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white p-12 text-center dark:border-slate-700 dark:bg-slate-900"><div class="text-3xl">◇</div><h3 class="mt-3 font-black text-slate-900 dark:text-white">No payout method yet</h3><p class="mt-1 text-sm text-slate-500">Add your bank account or wallet using the form.</p></div>
                    @endforelse
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
