<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ auth()->user()?->theme === 'dark' ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="data-version" content="{{ \App\Support\DataVersion::current() }}">
        <meta name="data-version-url" content="{{ route('data-version') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" href="{{ asset('logo.svg') }}" type="image/svg+xml">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @if(auth()->user()?->role === 'customer')
            @php($customerTheme = \App\Models\SiteSetting::customerPortalTheme())
            <style>
                :root {
                    --customer-page-base: {{ $customerTheme['customer_theme_page_background'] }};
                    --customer-nav-base: {{ $customerTheme['customer_theme_nav_background'] }};
                    --customer-nav-text-base: {{ $customerTheme['customer_theme_nav_text'] }};
                    --customer-card-base: {{ $customerTheme['customer_theme_card_background'] }};
                    --customer-surface-base: {{ $customerTheme['customer_theme_surface_background'] }};
                    --customer-text-base: {{ $customerTheme['customer_theme_text'] }};
                    --customer-muted-base: {{ $customerTheme['customer_theme_muted_text'] }};
                    --customer-border-base: {{ $customerTheme['customer_theme_border'] }};
                    --customer-page: var(--customer-page-base);
                    --customer-nav: var(--customer-nav-base);
                    --customer-nav-text: var(--customer-nav-text-base);
                    --customer-primary: {{ $customerTheme['customer_theme_primary'] }};
                    --customer-accent: {{ $customerTheme['customer_theme_accent'] }};
                    --customer-hero-start: {{ $customerTheme['customer_theme_hero_start'] }};
                    --customer-hero-middle: {{ $customerTheme['customer_theme_hero_middle'] }};
                    --customer-hero-end: {{ $customerTheme['customer_theme_hero_end'] }};
                    --customer-hero-text: {{ $customerTheme['customer_theme_hero_text'] }};
                    --customer-blur: {{ $customerTheme['customer_theme_blur_color'] }};
                    --customer-active-badge: {{ $customerTheme['customer_theme_active_badge_background'] }};
                    --customer-active-badge-text: {{ $customerTheme['customer_theme_active_badge_text'] }};
                    --customer-card: var(--customer-card-base);
                    --customer-surface: var(--customer-surface-base);
                    --customer-text: var(--customer-text-base);
                    --customer-muted: var(--customer-muted-base);
                    --customer-border: var(--customer-border-base);
                    --customer-button-text: {{ $customerTheme['customer_theme_button_text'] }};
                }
                .dark {
                    --customer-page: color-mix(in srgb, #020617 90%, var(--customer-page-base));
                    --customer-nav: color-mix(in srgb, #020617 88%, var(--customer-nav-base));
                    --customer-nav-text: #cbd5e1;
                    --customer-card: color-mix(in srgb, #0f172a 90%, var(--customer-card-base));
                    --customer-surface: color-mix(in srgb, #1e293b 90%, var(--customer-surface-base));
                    --customer-text: #f8fafc;
                    --customer-muted: #94a3b8;
                    --customer-border: color-mix(in srgb, #475569 72%, var(--customer-border-base));
                }
                .customer-portal-shell { background-color: var(--customer-page) !important; color: var(--customer-text); }
                .customer-portal-shell .customer-theme-page { background: var(--customer-page) !important; }
                .customer-portal-nav,
                .customer-portal-mobile-menu { background: color-mix(in srgb, var(--customer-nav) 94%, transparent) !important; }
                .customer-portal-nav .customer-nav-link { color: var(--customer-nav-text) !important; }
                .customer-portal-nav .customer-nav-active { background-color: var(--customer-primary) !important; color: #fff !important; }
                .customer-portal-shell .customer-theme-card { background-color: var(--customer-card) !important; color: var(--customer-text); }
                .customer-portal-shell > header,
                .customer-portal-shell main section.bg-white,
                .customer-portal-shell main article.bg-white,
                .customer-portal-shell main div.bg-white { background-color: var(--customer-card) !important; color: var(--customer-text); }
                .customer-portal-shell main .bg-gray-50,
                .customer-portal-shell main .bg-gray-100,
                .customer-portal-shell main .bg-slate-50,
                .customer-portal-shell main .bg-slate-100 { background-color: var(--customer-surface) !important; }
                .customer-portal-shell main :is(input:not([type="checkbox"]):not([type="radio"]):not([type="color"]), select, textarea) {
                    background-color: var(--customer-surface) !important;
                    border-color: var(--customer-border) !important;
                    color: var(--customer-text) !important;
                }
                .customer-portal-shell main :is(input, select, textarea)::placeholder { color: var(--customer-muted) !important; opacity: .75; }
                .customer-portal-shell main :is(.border-gray-100, .border-gray-200, .border-gray-300, .border-slate-100, .border-slate-200, .border-slate-300) { border-color: var(--customer-border) !important; }
                .customer-portal-shell main :is(.text-gray-900, .text-slate-900, .text-slate-950) { color: var(--customer-text) !important; }
                .customer-portal-shell main :is(.text-gray-400, .text-gray-500, .text-gray-600, .text-gray-700, .text-slate-400, .text-slate-500, .text-slate-600, .text-slate-700) { color: var(--customer-muted) !important; }
                .customer-portal-shell .bg-indigo-600 { background-color: var(--customer-primary) !important; }
                .customer-portal-shell .text-indigo-600, .customer-portal-shell .text-indigo-700 { color: var(--customer-primary) !important; }
                .customer-portal-shell .bg-violet-600, .customer-portal-shell .bg-violet-700 { background-color: var(--customer-accent) !important; }
                .customer-portal-shell .text-violet-600, .customer-portal-shell .text-violet-700 { color: var(--customer-accent) !important; }
                .customer-portal-shell main :is(button, a).bg-indigo-600,
                .customer-portal-shell main :is(button, a).bg-indigo-700,
                .customer-portal-shell main :is(button, a).bg-violet-600,
                .customer-portal-shell main :is(button, a).bg-violet-700 { color: var(--customer-button-text) !important; }
                .customer-theme-account-hero,
                .customer-theme-marketplace-hero,
                .customer-theme-team-hero,
                .customer-theme-team-toolbar,
                .customer-theme-team-owner-header {
                    background: linear-gradient(115deg, var(--customer-hero-start) 0%, var(--customer-hero-middle) 58%, var(--customer-hero-end) 100%) !important;
                    color: var(--customer-hero-text) !important;
                }
                .customer-theme-marketplace-hero .customer-theme-hero-heading { color: var(--customer-hero-text) !important; }
                .customer-theme-marketplace-hero .customer-theme-hero-copy,
                .customer-theme-account-hero .customer-theme-hero-copy { color: color-mix(in srgb, var(--customer-hero-text) 78%, transparent) !important; }
                .customer-theme-hero-panel { background: color-mix(in srgb, var(--customer-card) 13%, transparent) !important; }
                .customer-theme-blur { background-color: color-mix(in srgb, var(--customer-blur) 38%, transparent) !important; }
                .customer-theme-active-badge {
                    background-color: var(--customer-active-badge) !important;
                    border-color: color-mix(in srgb, var(--customer-active-badge-text) 30%, transparent) !important;
                    color: var(--customer-active-badge-text) !important;
                }
                .customer-theme-active-dot { background-color: var(--customer-active-badge-text) !important; }
                .customer-theme-team-hero :is(.text-indigo-100, .text-indigo-200, .text-violet-200),
                .customer-theme-team-toolbar .text-indigo-200,
                .customer-theme-team-owner-header .text-indigo-200 { color: color-mix(in srgb, var(--customer-hero-text) 78%, transparent) !important; }
                .customer-theme-team-summary { background-color: var(--customer-card) !important; color: var(--customer-text) !important; }
                .customer-theme-team-canvas { background-color: var(--customer-surface) !important; }
                .customer-theme-team-canvas :is(.network-owner, .network-node) { background-color: var(--customer-card) !important; border-color: var(--customer-border) !important; }
                .customer-theme-team-canvas :is(.border-indigo-100, .border-indigo-200, .border-indigo-300) { border-color: color-mix(in srgb, var(--customer-primary) 35%, transparent) !important; }
                .customer-theme-team-canvas :is(.bg-indigo-200, .bg-indigo-300, .bg-indigo-400) { background-color: color-mix(in srgb, var(--customer-primary) 45%, transparent) !important; }
                .customer-theme-installment-receipt {
                    background: linear-gradient(100deg, var(--customer-hero-start), var(--customer-hero-middle)) !important;
                    color: var(--customer-hero-text) !important;
                }
                .customer-theme-installment-receipt .text-indigo-200 { color: color-mix(in srgb, var(--customer-hero-text) 75%, transparent) !important; }
                .customer-theme-installment-summary { background: linear-gradient(110deg, var(--customer-surface), color-mix(in srgb, var(--customer-primary) 10%, var(--customer-surface))) !important; }
                .dark .customer-notification-type {
                    background-color: var(--customer-surface) !important;
                    border: 1px solid var(--customer-border);
                    color: var(--customer-text) !important;
                }
                .dark .customer-notification-type small { color: var(--customer-muted) !important; opacity: 1 !important; }
                .dark .customer-notification-inbox {
                    color: var(--customer-text) !important;
                }
                .dark .customer-notification-inbox :is(.text-slate-300, .text-slate-400, .text-slate-500, .text-slate-600) {
                    color: #cbd5e1 !important;
                }
                .dark .customer-notification-inbox thead {
                    color: #cbd5e1 !important;
                }
                .dark .customer-notification-inbox .customer-notification-detail {
                    background-color: #1e293b !important;
                    border-color: #64748b !important;
                    color: #cbd5e1 !important;
                }
                .dark .customer-notification-inbox .customer-notification-detail b,
                .dark .customer-notification-inbox .customer-notification-title {
                    color: #f8fafc !important;
                }
                .dark .customer-notification-filter-active {
                    background-color: var(--customer-primary) !important;
                    color: var(--customer-button-text) !important;
                }
                .customer-theme-avatar { background: linear-gradient(135deg, var(--customer-accent), var(--customer-primary)) !important; }
                .customer-portal-shell .from-slate-950 { --tw-gradient-from: var(--customer-hero-start) var(--tw-gradient-from-position) !important; --tw-gradient-to: color-mix(in srgb, var(--customer-hero-start) 0%, transparent) var(--tw-gradient-to-position) !important; }
                .customer-portal-shell .via-indigo-950 { --tw-gradient-to: color-mix(in srgb, var(--customer-hero-middle) 0%, transparent) var(--tw-gradient-to-position) !important; --tw-gradient-stops: var(--tw-gradient-from), var(--customer-hero-middle) var(--tw-gradient-via-position), var(--tw-gradient-to) !important; }
                .customer-portal-shell .to-indigo-800, .customer-portal-shell .to-violet-800, .customer-portal-shell .to-violet-900 { --tw-gradient-to: var(--customer-hero-end) var(--tw-gradient-to-position) !important; }
                .customer-portal-shell :is(.from-indigo-500, .from-indigo-600, .from-indigo-700) {
                    --tw-gradient-from: var(--customer-primary) var(--tw-gradient-from-position) !important;
                    --tw-gradient-to: color-mix(in srgb, var(--customer-primary) 0%, transparent) var(--tw-gradient-to-position) !important;
                }
                .customer-portal-shell :is(.via-violet-500, .via-violet-600, .via-violet-700) {
                    --tw-gradient-to: color-mix(in srgb, var(--customer-accent) 0%, transparent) var(--tw-gradient-to-position) !important;
                    --tw-gradient-stops: var(--tw-gradient-from), var(--customer-accent) var(--tw-gradient-via-position), var(--tw-gradient-to) !important;
                }
                .customer-portal-shell :is(.to-violet-500, .to-violet-600, .to-violet-700, .to-violet-800) {
                    --tw-gradient-to: var(--customer-accent) var(--tw-gradient-to-position) !important;
                }
                @media (max-width: 639px) {
                    .customer-portal-shell { overflow-x: clip; }
                    .customer-portal-shell main { min-width: 0; }
                    .customer-portal-shell :is(button, a, input, select, textarea) { max-width: 100%; }
                }
                @media (prefers-reduced-motion: reduce) {
                    .customer-portal-shell *, .customer-portal-shell *::before, .customer-portal-shell *::after {
                        scroll-behavior: auto !important;
                        animation-duration: .01ms !important;
                        animation-iteration-count: 1 !important;
                        transition-duration: .01ms !important;
                    }
                }
            </style>
        @endif
    </head>
    <body class="font-sans antialiased">
        @if(auth()->user()?->role && auth()->user()->role !== 'customer')
            <div
                x-data="{ sidebarOpen: false, sidebarHover: false, sidebarExpanded: false, toggleSidebar(){ this.sidebarExpanded = !this.sidebarExpanded } }"
                x-effect="document.documentElement.classList.toggle('overflow-hidden', sidebarOpen); document.body.classList.toggle('overflow-hidden', sidebarOpen)"
                @keydown.escape.window="sidebarOpen = false"
                @resize.window="if (window.innerWidth >= 1024) sidebarOpen = false"
                class="min-h-screen bg-gray-100 dark:bg-gray-900"
            >
                @include('layouts.admin-sidebar')
                <div :class="sidebarExpanded ? 'lg:pl-64' : 'lg:pl-20'" class="min-h-screen transition-[padding] duration-300">
                    <div class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white/90 px-4 shadow-sm backdrop-blur dark:border-gray-700 dark:bg-gray-800/90 sm:px-6">
                        <button type="button" @click="sidebarOpen = true" class="grid h-10 w-10 place-items-center rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 lg:hidden dark:border-gray-600 dark:text-gray-200">☰</button>
                        <div class="hidden text-xs font-bold uppercase tracking-widest text-gray-400 lg:block">Management portal</div>
                        <div class="flex items-center gap-2">
                            @can('manage notifications')
                                @php($unreadNotificationCount = auth()->user()->unreadNotifications()->count())
                                <a href="{{ route('management.notifications.index') }}" class="relative grid h-10 w-10 place-items-center rounded-xl text-gray-500 transition hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white" title="Notifications" aria-label="Notifications">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                                    @if($unreadNotificationCount)
                                        <span class="absolute right-0.5 top-0.5 min-w-4 rounded-full bg-rose-500 px-1 text-center text-[9px] font-black leading-4 text-white ring-2 ring-white dark:ring-gray-800">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                                    @endif
                                </a>
                            @endcan
                            <form method="POST" action="{{ route('theme.update') }}">@csrf @method('PATCH')<input type="hidden" name="theme" value="{{ auth()->user()->theme === 'dark' ? 'light' : 'dark' }}"><button class="grid h-9 w-9 place-items-center rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700" title="Toggle appearance">{{ auth()->user()->theme === 'dark' ? '☀' : '☾' }}</button></form>
                            <a href="{{ route('profile.edit') }}" class="hidden rounded-lg px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 sm:block dark:text-gray-200 dark:hover:bg-gray-700">{{ auth()->user()->name }}</a>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-lg px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950">Log out</button></form>
                        </div>
                    </div>

                    @isset($header)
                        <header class="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{{ $header }}</div>
                        </header>
                    @endisset
                    <main>{{ $slot }}</main>
                </div>
            </div>
        @else
            <div class="customer-portal-shell min-h-screen bg-gray-100 dark:bg-gray-900">
                @include('layouts.navigation')
                @isset($header)
                    <header class="bg-white shadow dark:bg-gray-800"><div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{{ $header }}</div></header>
                @endisset
                <main>{{ $slot }}</main>
            </div>
        @endif
    </body>
</html>
