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
                            @if(in_array(auth()->user()->role, ['super_admin', 'admin'], true))
                                <a href="{{ route('site-appearance.edit') }}" class="grid h-9 w-9 place-items-center rounded-lg text-fuchsia-600 transition hover:bg-fuchsia-50 dark:text-fuchsia-300 dark:hover:bg-gray-700" title="Welcome page theme settings" aria-label="Welcome page theme settings">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.53 4.52a8.25 8.25 0 1 0 9.95 9.95c.43-1.52-1.03-2.87-2.5-2.3l-1.19.46a2 2 0 0 1-2.58-2.58l.46-1.19c.57-1.47-.78-2.93-2.3-2.5-.6.17-1.22.1-1.84.16Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 10.5h.01M9 15h.01M13.5 16.5h.01"/></svg>
                                </a>
                            @endif
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
            <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
                @include('layouts.navigation')
                @isset($header)
                    <header class="bg-white shadow dark:bg-gray-800"><div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{{ $header }}</div></header>
                @endisset
                <main>{{ $slot }}</main>
            </div>
        @endif
    </body>
</html>
