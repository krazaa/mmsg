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
    <body class="font-sans antialiased">
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
