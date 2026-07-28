<section>
    <header>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-slate-900 dark:text-white">Notification preferences</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Choose how you receive booking, payment, verification and withdrawal updates.</p>
            </div>
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-lg text-blue-800 dark:bg-blue-950 dark:text-blue-300">◉</span>
        </div>
    </header>

    <form method="POST" action="{{ route('profile.notifications.update') }}" class="mt-6 space-y-3">
        @csrf
        @method('PATCH')

        <div class="flex items-center gap-4 rounded-2xl border border-blue-200 bg-blue-50/60 p-4 dark:border-blue-900 dark:bg-blue-950/40">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-blue-800 text-xl text-white dark:bg-blue-700">◉</span>
            <span class="min-w-0 flex-1">
                <b class="block text-sm text-slate-900 dark:text-white">Portal notifications</b>
                <span class="mt-0.5 block text-xs text-slate-500 dark:text-slate-400">Database alerts in your customer portal</span>
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-800 px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-white">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                Always active
            </span>
        </div>

        <label class="flex cursor-pointer items-center gap-4 rounded-2xl border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50/40 dark:border-slate-700 dark:hover:bg-slate-800">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-xl text-blue-800 dark:bg-blue-950 dark:text-blue-300">✉</span>
            <span class="min-w-0 flex-1">
                <b class="block text-sm text-slate-900 dark:text-white">Email notifications</b>
                <span class="mt-0.5 block truncate text-xs text-slate-500">{{ $user->email }}</span>
            </span>
            <input type="hidden" name="email_notifications_enabled" value="0">
            <input type="checkbox" name="email_notifications_enabled" value="1" @checked(old('email_notifications_enabled', $user->email_notifications_enabled)) class="h-5 w-5 rounded border-slate-300 text-blue-800 focus:ring-blue-700">
        </label>

        <label class="flex cursor-pointer items-center gap-4 rounded-2xl border border-slate-200 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/40 dark:border-slate-700 dark:hover:bg-slate-800">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-xl text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">◉</span>
            <span class="min-w-0 flex-1">
                <b class="block text-sm text-slate-900 dark:text-white">WhatsApp notifications</b>
                <span class="mt-0.5 block text-xs text-slate-500">{{ $user->phone ?: 'No phone number registered' }}</span>
            </span>
            <input type="hidden" name="whatsapp_notifications_enabled" value="0">
            <input type="checkbox" name="whatsapp_notifications_enabled" value="1" @checked(old('whatsapp_notifications_enabled', $user->whatsapp_notifications_enabled)) @disabled(blank($user->phone)) class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-40">
        </label>

        <x-input-error :messages="$errors->get('email_notifications_enabled')" />
        <x-input-error :messages="$errors->get('whatsapp_notifications_enabled')" />

        <div class="flex items-center justify-between gap-4 pt-2">
            <p class="text-[10px] leading-4 text-slate-400">Security and password recovery messages may still be delivered when required to protect your account.</p>
            <button class="shrink-0 rounded-xl bg-slate-950 px-5 py-3 text-sm font-black text-white transition hover:bg-blue-950 dark:bg-blue-800 dark:hover:bg-blue-700">Save preferences</button>
        </div>
    </form>
</section>
