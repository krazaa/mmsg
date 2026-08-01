<x-app-layout>
    <div class="customer-theme-page min-h-screen bg-slate-50 py-6 dark:bg-slate-950 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6">
            @if(session('success'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800 shadow-sm"><span class="grid h-7 w-7 place-items-center rounded-full bg-emerald-500 text-white">✓</span>{{ session('success') }}</div>
            @endif

            @if($user->role === 'customer')
                @php
                    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn($part) => strtoupper(substr($part, 0, 1)))->join('');
                    $withdrawalFrequency = $user->withdrawal_frequency ?: \App\Models\WithdrawalSetting::settings()['frequency'];
                    $withdrawalPolicy = \App\Models\WithdrawalSetting::policy($withdrawalFrequency);
                    $withdrawalDays = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
                    $withdrawalDay = $withdrawalDays[$withdrawalPolicy['withdrawal_day'] ?? 0] ?? 'Any day';
                @endphp
                <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="customer-theme-account-hero relative bg-gradient-to-r from-slate-950 via-indigo-950 to-indigo-800 px-5 py-6 text-white sm:px-8 sm:py-7">
                        <div class="absolute right-0 top-0 h-full w-1/2 bg-[radial-gradient(circle_at_top_right,rgba(129,140,248,.28),transparent_65%)]"></div>
                        <div class="relative grid grid-cols-[64px_minmax(0,1fr)] items-start gap-4 lg:grid-cols-[80px_minmax(0,1fr)_auto] lg:items-center lg:gap-5">
                            <div class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl border border-white/20 bg-white/10 text-xl font-black shadow-xl backdrop-blur lg:h-20 lg:w-20 lg:text-2xl">{{ $initials ?: 'CU' }}</div>
                            <div class="min-w-0 flex-1">
                                <p class="mb-1.5 text-[8px] font-black uppercase tracking-[.14em] text-indigo-300 sm:text-[10px] sm:tracking-[.2em]">My account · Profile & security</p>
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3"><h1 class="min-w-0 truncate text-xl font-black tracking-tight sm:text-3xl">{{ $user->name }}</h1><span class="shrink-0 rounded-full px-2 py-1 text-[8px] font-black uppercase tracking-wider sm:px-2.5 sm:text-[10px] {{ $user->status ? 'bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-300/25' : 'bg-rose-400/15 text-rose-200 ring-1 ring-rose-300/25' }}">{{ $user->status ? 'Active account' : 'Inactive account' }}</span></div>
                                <p class="mt-1 truncate text-xs text-indigo-200 sm:text-sm">{{ $user->email }}</p>
                                <p class="mt-2 text-[10px] leading-4 text-indigo-200/80 sm:text-xs">Manage your personal details, preferences and account security.</p>
                            </div>
                            <div class="col-span-2 row-start-3 grid grid-cols-2 gap-2 lg:col-span-1 lg:col-start-3 lg:row-start-1 lg:w-auto">
                                <div class="rounded-xl border border-white/10 bg-white/[.07] px-4 py-3"><div class="text-[9px] font-black uppercase tracking-wider text-indigo-300">File number</div><div class="mt-1 font-mono text-sm font-black">{{ $user->file_no ?: 'Not assigned' }}</div></div>
                                <div class="rounded-xl border border-white/10 bg-white/[.07] px-4 py-3"><div class="text-[9px] font-black uppercase tracking-wider text-indigo-300">Member since</div><div class="mt-1 text-sm font-black">{{ $user->created_at->format('M Y') }}</div></div>
                            </div>
                            @if($showReferralCode && $user->referral_code)
                                @php($referralLink = route('register', ['ref' => $user->referral_code]))
                                <div class="col-span-2 row-start-2 rounded-xl border border-white/10 bg-white/[.07] p-3 lg:col-start-2 lg:row-start-2 lg:max-w-xl" x-data="{ copied: false }">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="text-[9px] font-black uppercase tracking-wider text-indigo-300">Your referral code</div>
                                            <div class="mt-1 break-all font-mono text-lg font-black tracking-wider">{{ $user->referral_code }}</div>
                                            <a href="{{ $referralLink }}" target="_blank" rel="noopener" class="mt-1 block truncate text-xs text-indigo-200 underline decoration-indigo-300/50 underline-offset-2" title="{{ $referralLink }}">{{ $referralLink }}</a>
                                        </div>
                                        <button type="button" @click="navigator.clipboard.writeText(@js($referralLink)); copied = true; setTimeout(() => copied = false, 1500)" class="w-full shrink-0 rounded-lg bg-white px-3 py-2 text-xs font-black text-indigo-800 transition hover:bg-indigo-50 sm:w-auto" x-text="copied ? 'Link copied ✓' : 'Copy referral link'">Copy referral link</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            <div class="grid items-start gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
                <aside class="space-y-5 lg:sticky lg:top-24">
                    @if($user->role === 'customer')
                        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800"><h3 class="font-black text-slate-900 dark:text-white">Account details</h3><p class="mt-0.5 text-xs text-slate-500">Managed information</p></div>
                            <dl class="divide-y divide-slate-100 px-5 dark:divide-slate-800">
                                @foreach([
                                    ['Sponsor', $user->customer?->referralAgent?->name ?? 'Direct Sales'],
                                    ['Email', $user->hasVerifiedEmail() ? 'Verified' : 'Not verified'],
                                    ['Joined', $user->created_at->format('d M Y')],
                                ] as [$label, $value])
                                    <div class="flex items-center justify-between gap-4 py-3.5"><dt class="text-xs font-semibold text-slate-500">{{ $label }}</dt><dd class="max-w-40 truncate text-right text-sm font-black text-slate-900 dark:text-white" title="{{ $value }}">{{ $value }}</dd></div>
                                @endforeach
                            </dl>
                        </section>

                        <section class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="border-b border-emerald-100 bg-emerald-50/70 px-5 py-4 dark:border-slate-800 dark:bg-emerald-950/20"><h3 class="font-black text-slate-900 dark:text-white">Withdrawal frequency</h3><p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Current policy set by the office.</p></div>
                            <div class="grid gap-3 p-5">
                                <div class="flex min-w-0 items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800">
                                    <div class="min-w-0 text-[10px] font-black uppercase tracking-wider text-slate-400">Frequency</div>
                                    <span class="max-w-[60%] rounded-full bg-emerald-100 px-3 py-1.5 text-right text-xs font-black uppercase tracking-wider text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">{{ ucfirst($withdrawalFrequency) }}</span>
                                </div>
                                <div class="flex min-w-0 items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800">
                                    <div class="min-w-0 text-[10px] font-black uppercase tracking-wider text-slate-400">Withdrawal day</div>
                                    <span class="max-w-[60%] break-words rounded-full bg-indigo-100 px-3 py-1.5 text-right text-xs font-black tracking-wider text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">{{ $withdrawalDay }}</span>
                                </div>
                            </div>
                        </section>

                        <section x-data="{ recoverConfirm: false }" class="overflow-hidden rounded-2xl border border-indigo-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="border-b border-indigo-100 bg-indigo-50/70 px-4 py-3.5 dark:border-slate-800 dark:bg-indigo-950/20 sm:px-5 sm:py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0"><h3 class="text-base font-black text-slate-900 dark:text-white">Withdrawal PIN</h3><p class="mt-0.5 text-[11px] leading-4 text-slate-500 dark:text-slate-400 sm:text-xs">Required for every withdrawal request.</p></div>
                                    <span class="shrink-0 whitespace-nowrap rounded-full px-2.5 py-1 text-[9px] font-black uppercase {{ filled($user->withdrawal_pin) ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">{{ filled($user->withdrawal_pin) ? 'Protected' : 'Not set' }}</span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('profile.withdrawal-pin.update') }}" class="space-y-3 p-4 sm:p-5">@csrf @method('PATCH')
                                <label class="block text-[11px] font-black uppercase tracking-wide text-slate-500 sm:text-xs">Account password<input type="password" name="current_password" required autocomplete="current-password" class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm normal-case dark:border-slate-700 dark:bg-slate-800 dark:text-white"></label>
                                <x-input-error :messages="$errors->get('current_password')" />
                                <label class="block text-[11px] font-black uppercase tracking-wide text-slate-500 sm:text-xs">New withdrawal PIN<input type="password" name="withdrawal_pin" required inputmode="numeric" pattern="[0-9]{4,6}" minlength="4" maxlength="6" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 font-mono text-base tracking-[.35em] dark:border-slate-700 dark:bg-slate-800 dark:text-white sm:text-lg" placeholder="••••"></label>
                                <x-input-error :messages="$errors->get('withdrawal_pin')" />
                                <label class="block text-[11px] font-black uppercase tracking-wide text-slate-500 sm:text-xs">Confirm PIN<input type="password" name="withdrawal_pin_confirmation" required inputmode="numeric" pattern="[0-9]{4,6}" minlength="4" maxlength="6" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 font-mono text-base tracking-[.35em] dark:border-slate-700 dark:bg-slate-800 dark:text-white sm:text-lg" placeholder="••••"></label>
                                <p class="rounded-lg bg-slate-50 p-2.5 text-[10px] leading-4 text-slate-500 dark:bg-slate-800">Use 4–6 digits. Your PIN is encrypted and cannot be viewed by staff.</p>
                                <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-black text-white transition hover:bg-indigo-700">{{ filled($user->withdrawal_pin) ? 'Change withdrawal PIN' : 'Set withdrawal PIN' }}</button>
                            </form>
                            @if(filled($user->withdrawal_pin) && $pinRecoveryEnabled)
                                <div class="border-t border-indigo-100 bg-indigo-50/50 p-5 text-center dark:border-slate-800 dark:bg-indigo-950/20">
                                    <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">Forgot your PIN? Receive a temporary PIN on your registered email{{ config('services.whatsapp.enabled') && filled($user->phone) ? ' and WhatsApp' : '' }}.</p>
                                    <button type="button" @click="recoverConfirm = true" class="mt-2 text-xs font-black text-indigo-600 hover:text-indigo-800 dark:text-indigo-300">Send me a new PIN →</button>
                                </div>
                                <form id="withdrawal-pin-recovery" method="POST" action="{{ route('customer.withdrawal-pin.recover') }}" class="hidden">@csrf</form>
                                <template x-teleport="body">
                                    <div x-show="recoverConfirm" x-cloak @keydown.escape.window="recoverConfirm = false" class="fixed inset-0 z-[120] flex items-center justify-center p-4">
                                        <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" @click="recoverConfirm = false"></div>
                                        <section role="dialog" aria-modal="true" aria-labelledby="pin-recovery-title" class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white text-left shadow-2xl dark:bg-slate-900">
                                            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-violet-700 to-slate-950 p-6 text-white"><div class="absolute -right-10 -top-12 h-36 w-36 rounded-full bg-fuchsia-400/20 blur-2xl"></div><button type="button" @click="recoverConfirm = false" class="absolute right-4 top-4 grid h-9 w-9 place-items-center rounded-full bg-white/10 text-lg text-white/80 transition hover:bg-white/20 hover:text-white">×</button><span class="grid h-12 w-12 place-items-center rounded-2xl border border-white/20 bg-white/10 text-2xl shadow-lg">🔐</span><div class="mt-4 text-[10px] font-black uppercase tracking-[.18em] text-indigo-200">Withdrawal security</div><h3 id="pin-recovery-title" class="mt-1 text-xl font-black">Send a new temporary PIN?</h3><p class="mt-2 text-xs leading-5 text-indigo-100">A secure six-digit PIN will be generated for your account.</p></div>
                                            <div class="space-y-4 p-5">
                                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-800"><b class="block">Your current PIN will stop working.</b>Only the new temporary PIN can be used after you continue.</div>
                                                <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700"><div class="text-[10px] font-black uppercase tracking-wide text-slate-400">Delivery</div><div class="mt-2 space-y-2 text-xs font-bold text-slate-700 dark:text-slate-200"><div class="flex items-center gap-2"><span class="grid h-7 w-7 place-items-center rounded-lg bg-indigo-50 text-indigo-600">✉</span><span class="min-w-0 truncate">{{ $user->email }}</span></div>@if(config('services.whatsapp.enabled') && filled($user->phone))<div class="flex items-center gap-2"><span class="grid h-7 w-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600">◉</span><span>{{ $user->phone }} · WhatsApp</span></div>@endif</div></div>
                                                <p class="text-[10px] leading-4 text-slate-400">For security, you can request another temporary PIN after 10 minutes.</p>
                                                <div class="grid grid-cols-2 gap-3"><button type="button" @click="recoverConfirm = false" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancel</button><button type="submit" form="withdrawal-pin-recovery" class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-3 text-sm font-black text-white shadow-lg shadow-indigo-200 transition hover:from-indigo-700 hover:to-violet-700 dark:shadow-none">Send new PIN</button></div>
                                            </div>
                                        </section>
                                    </div>
                                </template>
                            @elseif(filled($user->withdrawal_pin))
                                <div class="border-t border-slate-100 bg-slate-50 p-4 text-center text-[10px] text-slate-500 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-400">Forgot your PIN? Temporary PIN recovery is disabled. Please contact the office.</div>
                            @endif
                        </section>
                    @endif

                    @include('profile.partials.manage-passkeys')
                </aside>

                <div class="min-w-0 space-y-6">
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-7">@include('profile.partials.update-profile-information-form')</section>
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-7">@include('profile.partials.update-notification-preferences-form')</section>
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-7">@include('profile.partials.update-password-form')</section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
