<x-guest-layout>
    <div class="mb-7">
        <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1.5 text-[10px] font-black uppercase tracking-[.16em] text-indigo-700"><span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>Welcome back</span>
        <h2 class="mt-4 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Sign in to your account</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Manage your property account securely and effortlessly.</p>
    </div>

    <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 p-3 text-sm font-bold text-emerald-700" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <label class="block">
            <span class="text-xs font-black uppercase tracking-wide text-slate-600">Email address</span>
            <span class="relative mt-2 block">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.9 5.3a2 2 0 0 0 2.2 0L21 8m-16 9h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2Z"/></svg>
                <input id="email" class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 text-sm font-semibold placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500" type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus autocomplete="email webauthn">
            </span>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </label>

        <label class="block">
            <span class="flex items-center justify-between"><span class="text-xs font-black uppercase tracking-wide text-slate-600">Password</span>@if(Route::has('password.request'))<a class="text-xs font-bold text-indigo-600 hover:text-indigo-800" href="{{ route('password.request') }}">Forgot password?</a>@endif</span>
            <span class="relative mt-2 block">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 10V7a5 5 0 0 1 10 0v3m-11 0h12a2 2 0 0 1 2 2v7H4v-7a2 2 0 0 1 2-2Z"/></svg>
                <input id="password" class="w-full rounded-2xl border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 text-sm font-semibold placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500" type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            </span>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </label>

        <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2.5 text-sm font-semibold text-slate-600">
            <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
            Keep me signed in
        </label>

        <button class="group flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 py-4 text-sm font-black text-white shadow-xl shadow-slate-200 transition hover:-translate-y-0.5 hover:bg-indigo-700 hover:shadow-indigo-200">
            Sign in securely <span class="transition group-hover:translate-x-1">→</span>
        </button>
    </form>

    <div class="my-6 flex items-center gap-3"><span class="h-px flex-1 bg-slate-200"></span><span class="text-[9px] font-black uppercase tracking-[.18em] text-slate-400">faster secure access</span><span class="h-px flex-1 bg-slate-200"></span></div>
    <button type="button" data-passkey-login data-options-url="{{ route('passkey.login-options') }}" data-submit-url="{{ route('passkey.login') }}" data-fallback-url="{{ route('dashboard') }}" data-message-target="#passkey-login-message" class="group flex w-full items-center justify-center gap-3 rounded-2xl border border-indigo-200 bg-gradient-to-r from-indigo-50 to-violet-50 px-5 py-3.5 text-sm font-black text-indigo-700 transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-100 disabled:cursor-wait disabled:opacity-70">
        <span class="grid h-8 w-8 place-items-center rounded-xl bg-gradient-to-br from-indigo-600 to-violet-700 text-white shadow-md"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0-1.1.9-2 2-2s2 .9 2 2v1m-4 3v2m-4-6a6 6 0 1 1 12 0v2a9 9 0 0 1-3 6.7M8 15v1a5 5 0 0 0 2 4m-6-7v-2a10 10 0 0 1 17.3-6.8"/></svg></span>
        Sign in with a passkey
    </button>
    <p id="passkey-login-message" hidden class="mt-3 text-center text-xs font-bold"></p>
    <p class="mt-3 text-center text-[10px] leading-5 text-slate-400">Face ID · Fingerprint · Windows Hello · Security key</p>
</x-guest-layout>
