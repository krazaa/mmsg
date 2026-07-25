<section class="overflow-hidden rounded-3xl border border-indigo-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="bg-gradient-to-br from-indigo-700 via-violet-700 to-slate-950 p-5 text-white">
        <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-white/15">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0-1.1.9-2 2-2s2 .9 2 2v1m-4 3v2m-4-6a6 6 0 1 1 12 0v2a9 9 0 0 1-3 6.7M8 15v1a5 5 0 0 0 2 4m-6-7v-2a10 10 0 0 1 17.3-6.8"/></svg>
            </span>
            <div><h3 class="font-black">Passkeys</h3><p class="text-xs text-indigo-100">Passwordless, phishing-resistant sign in</p></div>
        </div>
    </div>

    <div class="space-y-4 p-5">
        @if(session('status') === 'passkey-registered')
            <div class="rounded-xl bg-emerald-50 p-3 text-xs font-bold text-emerald-700">Passkey created successfully.</div>
        @elseif(session('status') === 'passkey-deleted')
            <div class="rounded-xl bg-emerald-50 p-3 text-xs font-bold text-emerald-700">Passkey removed.</div>
        @endif

        <form data-passkey-register data-options-url="{{ route('passkey.registration-options') }}" data-submit-url="{{ route('passkey.store') }}" class="space-y-3">
            <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Device name
                <input name="passkey_name" maxlength="100" placeholder="e.g. My iPhone or Office laptop" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm font-normal normal-case dark:border-slate-600 dark:bg-slate-800 dark:text-white">
            </label>
            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-black text-white transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60">Add a passkey</button>
            <p data-passkey-message hidden class="text-xs font-bold"></p>
        </form>

        <div class="border-t border-slate-100 pt-4 dark:border-slate-800">
            <div class="mb-3 flex items-center justify-between"><b class="text-xs uppercase tracking-wide text-slate-500">Saved passkeys</b><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-black text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">{{ $user->passkeys->count() }}</span></div>
            <div class="space-y-2">
                @forelse($user->passkeys->sortByDesc('created_at') as $passkey)
                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-slate-800">⌁</span>
                        <div class="min-w-0 flex-1"><b class="block truncate text-sm text-slate-900 dark:text-white">{{ $passkey->name }}</b><span class="text-[10px] text-slate-400">{{ $passkey->authenticator ?: 'Passkey device' }} · Added {{ $passkey->created_at->format('d M Y') }}</span></div>
                        <form method="POST" action="{{ route('passkey.destroy', $passkey) }}" onsubmit="return confirm('Remove this passkey?')">@csrf @method('DELETE')<button class="rounded-lg px-2.5 py-2 text-xs font-black text-rose-600 hover:bg-rose-50">Remove</button></form>
                    </div>
                @empty
                    <div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500 dark:bg-slate-800">No passkeys added yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
