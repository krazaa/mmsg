<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-xs font-black uppercase tracking-[.18em] text-indigo-600 dark:text-indigo-300">Settings</div>
            <h2 class="mt-1 text-xl font-black text-gray-900 dark:text-white">App settings</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage global customer portal and withdrawal security options.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-emerald-50 py-8 dark:from-slate-950 dark:via-slate-950 dark:to-indigo-950/30">
        <div class="mx-auto max-w-4xl space-y-5 px-4 sm:px-6">
            @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-800">{{ $errors->first() }}</div>@endif

            <form method="POST" action="{{ route('app-settings.update') }}" x-data="{ feeEnabled: @js((bool) old('fee_enabled', $fee['enabled'])), feeType: @js(old('fee_type', $fee['type'])), pinRecovery: @js((bool) old('pin_recovery_enabled', $pinRecoveryEnabled)), showReferral: @js((bool) old('customer_portal_show_referral_code', $showReferralCode)), maintenanceEnabled: @js((bool) old('maintenance_mode_enabled', $maintenanceEnabled)) }" class="overflow-hidden rounded-3xl border border-indigo-100 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                @csrf @method('PUT')
                <div class="border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-white p-6 dark:border-slate-800 dark:from-indigo-950/30 dark:to-slate-900"><h3 class="font-black text-slate-950 dark:text-white">Global application controls</h3><p class="mt-1 text-xs text-slate-500 dark:text-slate-300">These settings apply to every customer account.</p></div>
                <div class="space-y-5 p-6">
                    <section class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-white p-5 dark:border-amber-900 dark:from-amber-950/20 dark:to-slate-900">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div class="flex items-start gap-3"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-amber-100 font-black text-amber-700">%</span><div><h4 class="font-black text-slate-950 dark:text-white">Withdrawal fee</h4><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Deduct a fixed or percentage fee from customer withdrawal requests.</p></div></div><label class="inline-flex cursor-pointer items-center gap-3 rounded-xl border border-amber-200 bg-white px-4 py-3 text-xs font-black dark:border-slate-700 dark:bg-slate-800 dark:text-white"><input type="hidden" name="fee_enabled" value="0"><input type="checkbox" name="fee_enabled" value="1" x-model="feeEnabled" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500"> Enable withdrawal fee</label></div>
                        <div x-show="feeEnabled" x-cloak class="mt-4 grid gap-4 border-t border-amber-100 pt-4 sm:grid-cols-2 dark:border-amber-900/50">
                            <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Fee type<select name="fee_type" x-model="feeType" class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm normal-case dark:border-slate-600 dark:bg-slate-800 dark:text-white"><option value="fixed">Fixed amount</option><option value="percentage">Percentage</option></select></label>
                            <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Fee value<div class="relative mt-1.5"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-black text-amber-600" x-text="feeType === 'percentage' ? '%' : 'Rs'"></span><input type="number" name="fee_value" value="{{ old('fee_value', $fee['value']) }}" min="0" :max="feeType === 'percentage' ? 100 : null" step="0.01" class="w-full rounded-xl border-slate-300 py-2.5 pl-10 text-sm dark:border-slate-600 dark:bg-slate-800 dark:text-white"></div></label>
                        </div>
                        <template x-if="!feeEnabled"><input type="hidden" name="fee_type" value="fixed"></template><template x-if="!feeEnabled"><input type="hidden" name="fee_value" value="0"></template>
                    </section>

                    <section class="rounded-2xl border border-indigo-200 bg-indigo-50/60 p-5 dark:border-indigo-900 dark:bg-indigo-950/20">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div class="flex items-start gap-3"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-indigo-100 text-lg">🔐</span><div><h4 class="font-black text-slate-950 dark:text-white">Customer PIN recovery</h4><p class="mt-1 max-w-xl text-xs leading-5 text-slate-500 dark:text-slate-400">Allow customers to receive a temporary withdrawal PIN through email and configured WhatsApp.</p></div></div><label class="inline-flex cursor-pointer items-center gap-3 rounded-xl border border-indigo-200 bg-white px-4 py-3 text-xs font-black dark:border-slate-700 dark:bg-slate-800 dark:text-white"><input type="hidden" name="pin_recovery_enabled" value="0"><input type="checkbox" name="pin_recovery_enabled" value="1" x-model="pinRecovery" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"> Enable for customers</label></div>
                    </section>

                    <section class="rounded-2xl border border-violet-200 bg-violet-50/60 p-5 dark:border-violet-900 dark:bg-violet-950/20">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div class="flex items-start gap-3"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-violet-100 text-lg">↗</span><div><h4 class="font-black text-slate-950 dark:text-white">Customer portal</h4><p class="mt-1 max-w-xl text-xs leading-5 text-slate-500 dark:text-slate-400">Control whether customers can see referral codes and copy referral links in their portal.</p></div></div><label class="inline-flex cursor-pointer items-center gap-3 rounded-xl border border-violet-200 bg-white px-4 py-3 text-xs font-black dark:border-slate-700 dark:bg-slate-800 dark:text-white"><input type="hidden" name="customer_portal_show_referral_code" value="0"><input type="checkbox" name="customer_portal_show_referral_code" value="1" x-model="showReferral" class="rounded border-slate-300 text-violet-600 focus:ring-violet-500"> Show referral code</label></div>
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-rose-200 bg-rose-50/60 dark:border-rose-900 dark:bg-rose-950/20">
                        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-start gap-3"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-rose-100 text-lg text-rose-700">⚙</span><div><h4 class="font-black text-slate-950 dark:text-white">Maintenance mode</h4><p class="mt-1 max-w-xl text-xs leading-5 text-slate-500 dark:text-slate-400">Temporarily show a branded maintenance page to customers and public visitors. Admin access and sign-in remain available.</p></div></div>
                            <label class="inline-flex cursor-pointer items-center gap-3 rounded-xl border border-rose-200 bg-white px-4 py-3 text-xs font-black dark:border-slate-700 dark:bg-slate-800 dark:text-white"><input type="hidden" name="maintenance_mode_enabled" value="0"><input type="checkbox" name="maintenance_mode_enabled" value="1" x-model="maintenanceEnabled" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500"> Enable maintenance</label>
                        </div>
                        <div class="grid gap-4 border-t border-rose-100 p-5 sm:grid-cols-2 dark:border-rose-900/50">
                            <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Page title<input type="text" name="maintenance_page_title" value="{{ old('maintenance_page_title', $maintenancePage['title']) }}" maxlength="100" required class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm normal-case dark:border-slate-600 dark:bg-slate-800 dark:text-white"></label>
                            <label class="block text-xs font-black uppercase tracking-wide text-slate-500 sm:col-span-2">Customer message<textarea name="maintenance_page_message" maxlength="500" rows="3" required class="mt-1.5 w-full rounded-xl border-slate-300 text-sm font-normal normal-case leading-6 dark:border-slate-600 dark:bg-slate-800 dark:text-white">{{ old('maintenance_page_message', $maintenancePage['message']) }}</textarea></label>
                            <div class="flex flex-wrap items-center gap-3 sm:col-span-2">
                                <a href="{{ route('app-settings.maintenance-preview') }}" target="_blank" rel="noopener" class="rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-xs font-black text-rose-700 hover:bg-rose-100 dark:border-rose-900 dark:bg-slate-800 dark:text-rose-300">Preview maintenance page ↗</a>
                                <span x-show="maintenanceEnabled" x-cloak class="rounded-full bg-rose-600 px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-white">Maintenance will be active after saving</span>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="flex flex-wrap gap-3 border-t border-slate-100 p-6 dark:border-slate-800"><button class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-black text-white hover:bg-indigo-700">Save app settings</button><a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600 dark:border-slate-700 dark:text-slate-300">Back to dashboard</a></div>
            </form>
        </div>
    </div>
</x-app-layout>
