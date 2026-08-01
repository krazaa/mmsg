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

        <style>
            body.app-loading > :not(#app-preloader) { visibility: hidden !important; }
            #app-preloader { position: fixed; inset: 0; z-index: 9999; display: grid; place-items: center; padding: 1rem; background: rgb(2 6 23 / .98); opacity: 1; visibility: visible; transition: opacity 180ms ease, visibility 180ms ease; }
            #app-preloader.is-hidden { pointer-events: none; opacity: 0; visibility: hidden; }
            #app-preloader .app-preloader__panel { display: grid; min-width: 10rem; place-items: center; gap: .8rem; padding: 1.25rem 1.5rem; border: 1px solid rgb(255 255 255 / .12); border-radius: 1.25rem; background: rgb(15 23 42 / .9); box-shadow: 0 24px 70px rgb(0 0 0 / .35); }
            #app-preloader .app-preloader__logo { width: 5.5rem; height: 3.5rem; object-fit: contain; }
            #app-preloader .app-preloader__spinner { width: 1.75rem; height: 1.75rem; border: 3px solid rgb(255 255 255 / .18); border-top-color: #818cf8; border-radius: 9999px; animation: critical-preloader-spin 700ms linear infinite; }
            #app-preloader .app-preloader__text { font-family: ui-sans-serif, system-ui, sans-serif; font-size: .65rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #cbd5e1; }
            @keyframes critical-preloader-spin { to { transform: rotate(360deg); } }
        </style>
        <noscript><style>body.app-loading > :not(#app-preloader) { visibility: visible !important; } #app-preloader { display: none !important; }</style></noscript>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @if(auth()->check() && auth()->user()->role !== 'customer')
            @php($adminCardAppearance = \App\Models\SiteSetting::adminCardAppearance())
            @php($adminCommandBackground = match($adminCardAppearance['admin_card_background_mode']) {
                'transparent' => 'transparent',
                'gradient' => 'linear-gradient(135deg, '.$adminCardAppearance['admin_card_gradient_start'].', '.$adminCardAppearance['admin_card_background'].')',
                default => $adminCardAppearance['admin_card_background'],
            })
            @php($adminPageBackground = $adminCardAppearance['admin_page_background_mode'] === 'gradient'
                ? 'linear-gradient(135deg, '.$adminCardAppearance['admin_page_gradient_start'].', '.$adminCardAppearance['admin_page_gradient_end'].')'
                : $adminCardAppearance['admin_page_background'])
            <style>
                :root {
                    --admin-page-background: {{ $adminPageBackground }};
                    --admin-card-background: {{ $adminCardAppearance['admin_card_background'] }};
                    --admin-command-card-background: {{ $adminCommandBackground }};
                    --admin-card-pattern: {{ $adminCardAppearance['admin_card_pattern'] }};
                    --admin-command-accent: {{ $adminCardAppearance['admin_card_accent_start'] }};
                    --admin-command-accent-end: {{ $adminCardAppearance['admin_card_accent_end'] }};
                    --admin-card-badge-background: {{ $adminCardAppearance['admin_card_badge_background'] }};
                    --admin-card-badge-text: {{ $adminCardAppearance['admin_card_badge_text'] }};
                    --admin-card-action-background: {{ $adminCardAppearance['admin_card_action_background'] }};
                    --admin-card-action-text: {{ $adminCardAppearance['admin_card_action_text'] }};
                }
            </style>
        @endif
    </head>
    <body class="app-loading font-sans antialiased">
        <div id="app-preloader" class="app-preloader" role="status" aria-live="polite" aria-label="Loading application">
            <div class="app-preloader__panel">
                <img src="{{ asset('logo.svg') }}" alt="" class="app-preloader__logo">
                <span class="app-preloader__spinner" aria-hidden="true"></span>
                <span class="app-preloader__text">{{ auth()->user()?->role === 'customer' ? 'Loading customer portal' : 'Loading management portal' }}</span>
            </div>
        </div>
        @if(auth()->check() && auth()->user()->role !== 'customer')
            @include('layouts.admin-portal')
        @else
            @include('layouts.customer-portal')
        @endif
    </body>
</html>
