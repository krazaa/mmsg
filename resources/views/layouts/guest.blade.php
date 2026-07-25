<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'MMS Group') }}</title>
        <link rel="icon" href="{{ asset('logo.svg') }}" type="image/svg+xml">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <main class="relative isolate min-h-screen overflow-hidden bg-slate-950 px-4 py-6 sm:px-6 sm:py-10">
            <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,.22),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(99,102,241,.28),_transparent_35%)]"></div>
            <div class="absolute -left-28 top-1/3 -z-10 h-80 w-80 rounded-full bg-emerald-400/15 blur-3xl"></div>
            <div class="absolute -right-28 top-8 -z-10 h-96 w-96 rounded-full bg-violet-500/20 blur-3xl"></div>
            <div class="absolute inset-0 -z-10 opacity-[.05]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:24px 24px"></div>

            <div class="mx-auto grid min-h-[calc(100vh-3rem)] w-full max-w-6xl overflow-hidden rounded-[2rem] border border-white/10 bg-white shadow-2xl shadow-black/40 lg:grid-cols-[1.05fr_.95fr]">
                <section class="relative hidden overflow-hidden bg-gradient-to-br from-emerald-700 via-teal-800 to-slate-950 p-10 text-white lg:flex lg:flex-col lg:justify-between">
                    <div class="absolute -right-24 -top-20 h-72 w-72 rounded-full bg-cyan-300/20 blur-3xl"></div>
                    <div class="absolute -bottom-28 -left-20 h-80 w-80 rounded-full bg-indigo-500/25 blur-3xl"></div>
                    <div class="relative">
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                            <span class="grid h-11 w-18 flex-none place-items-center overflow-hidden rounded-2xl p-1.5"><img src="{{ asset('logo.svg') }}" alt="MMS Group logo" class="h-full w-full object-contain"></span>
                            <span><b class="block text-xl font-black">MMS Group</b><span class="text-[10px] font-bold uppercase tracking-[.22em] text-emerald-200">Property management</span></span>
                        </a>
                        <div class="mt-16 max-w-md">
                            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200/20 bg-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[.18em] text-emerald-100"><span class="h-2 w-2 rounded-full bg-emerald-300"></span>Secure customer portal</span>
                            <h1 class="mt-5 text-4xl font-black leading-tight tracking-tight xl:text-5xl">Your property journey, all in one place.</h1>
                            <p class="mt-5 max-w-sm text-sm leading-7 text-emerald-100/80">Access bookings, installments, commissions, your referral network and account activity through one secure dashboard.</p>
                        </div>
                    </div>
                    <div class="relative grid grid-cols-3 gap-3">
                        @foreach([['✓','Secure access'],['⌁','Passkey ready'],['↗','Live records']] as [$icon,$label])
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur"><span class="text-xl">{{ $icon }}</span><b class="mt-2 block text-xs">{{ $label }}</b></div>
                        @endforeach
                    </div>
                </section>

                <section class="flex flex-col bg-gradient-to-br from-white via-white to-indigo-50/70">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 lg:hidden">
                        <a href="{{ url('/') }}" class="flex items-center gap-2.5"><span class="grid h-11 w-18 flex-none place-items-center overflow-hidden rounded-xl p-1"><img src="{{ asset('logo.svg') }}" alt="MMS Group logo" class="h-full w-full object-contain"></span><span><b class="block text-sm font-black">MMS Group</b><span class="block text-[8px] font-bold uppercase tracking-widest text-indigo-500">Customer portal</span></span></a>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-[9px] font-black uppercase tracking-wide text-emerald-700">Secure</span>
                    </div>
                    <div class="flex flex-1 items-center justify-center p-5 sm:p-10 lg:p-12">
                        <div class="w-full max-w-md">{{ $slot }}</div>
                    </div>
                    <p class="px-5 pb-5 text-center text-[10px] text-slate-400">© {{ now()->year }} MMS Group · Protected customer access</p>
                </section>
            </div>
        </main>
    </body>
</html>
