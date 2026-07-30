@php
    $items = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'permission' => 'view dashboard', 'icon' => '⌂'],
        ['label' => 'Bookings', 'route' => 'bookings.index', 'active' => 'bookings.*', 'permission' => 'manage bookings', 'icon' => '▤'],
        ['label' => 'Installments', 'route' => 'installments.index', 'active' => 'installments.*', 'permission' => 'manage installments', 'icon' => '◫'],
        ['label' => 'Customers', 'route' => 'customers.index', 'active' => 'customers.*', 'permission' => 'manage customers', 'icon' => '♙'],
        ['label' => 'Staff', 'route' => 'staff.index', 'active' => 'staff.*', 'permission' => 'manage staff', 'icon' => '♟', 'hide_from_staff' => true],
        ['label' => 'Roles & permissions', 'route' => 'role-permissions.edit', 'active' => 'role-permissions.*', 'permission' => 'manage staff', 'icon' => '⚿', 'super_admin_only' => true],
        ['label' => 'Payments', 'route' => 'payments.index', 'active' => 'payments.*', 'permission' => 'manage payments', 'icon' => '₨'],
        ['label' => 'Withdrawals', 'route' => 'withdrawal-requests.index', 'active' => 'withdrawal-requests.*', 'permission' => 'manage withdrawals', 'icon' => '↗'],
        ['label' => 'Alerts', 'route' => 'management.notifications.index', 'active' => 'management.notifications.*', 'permission' => 'manage notifications', 'icon' => '◉'],
        ['label' => 'Audit log', 'route' => 'management.activity-log.index', 'active' => 'management.activity-log.*', 'permission' => 'view activity log', 'icon' => '≡', 'hide_from_staff' => true],
    ];
    $settingsItems = [
        ['label' => 'Projects', 'route' => 'projects.index', 'active' => 'projects.*', 'permission' => 'manage projects', 'color' => 'bg-cyan-400'],
        ['label' => 'Blocks', 'route' => 'blocks.index', 'active' => 'blocks.*', 'permission' => 'manage allotments', 'color' => 'bg-sky-400'],
        ['label' => 'Plots', 'route' => 'plots.index', 'active' => 'plots.*', 'permission' => 'manage allotments', 'color' => 'bg-indigo-500'],
        ['label' => 'Plot allotments & inventory', 'route' => 'allotments.index', 'active' => 'allotments.*', 'permission' => 'manage allotments', 'color' => 'bg-lime-400'],
        ['label' => 'Packages', 'route' => 'packages.index', 'active' => 'packages.*', 'permission' => 'manage packages', 'color' => 'bg-violet-400'],
        ['label' => 'Commissions', 'route' => 'commission-rules.index', 'active' => 'commission-rules.*', 'permission' => 'manage commissions', 'color' => 'bg-amber-400'],
        ['label' => 'Withdrawal settings', 'route' => 'withdrawal-settings.edit', 'active' => 'withdrawal-settings.*', 'permission' => 'manage withdrawals', 'color' => 'bg-teal-400'],
        ['label' => 'App settings', 'route' => 'app-settings.edit', 'active' => 'app-settings.*', 'permission' => 'manage commissions', 'color' => 'bg-indigo-400'],
        ['label' => 'Payment settings', 'route' => 'payment-methods.index', 'active' => 'payment-methods.*', 'permission' => 'manage payments', 'color' => 'bg-emerald-400'],
        ['label' => 'Payment Gateway', 'route' => 'payment-gateways.index', 'active' => 'payment-gateways.*', 'permission' => 'manage payments', 'color' => 'bg-orange-400', 'super_admin_only' => true],
        ['label' => 'WhatsApp', 'route' => 'management.whatsapp.index', 'active' => 'management.whatsapp.*', 'permission' => 'manage notifications', 'color' => 'bg-green-400', 'super_admin_only' => true],
        ['label' => 'Notification templates', 'route' => 'management.whatsapp.index', 'fragment' => 'notification-templates', 'active' => 'notification-templates.*', 'permission' => 'manage notifications', 'color' => 'bg-indigo-400', 'super_admin_only' => true],
        ['label' => 'Email settings', 'route' => 'email-campaigns.index', 'active' => 'email-campaigns.*', 'permission' => 'manage notifications', 'color' => 'bg-rose-400', 'super_admin_only' => true],
        ['label' => 'Welcome page theme', 'route' => 'site-appearance.edit', 'active' => 'site-appearance.*', 'permission' => 'manage projects', 'color' => 'bg-fuchsia-400'],
        ['label' => 'Customer portal theme', 'route' => 'customer-portal-theme.edit', 'active' => 'customer-portal-theme.*', 'permission' => 'manage projects', 'color' => 'bg-blue-400'],
    ];
    $settingsActive = collect($settingsItems)->contains(fn ($item) => request()->routeIs($item['active']));
@endphp

<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" x-cloak></div>
<aside
    :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', (sidebarExpanded || sidebarHover) ? 'lg:w-64' : 'lg:w-20']"
    @mouseenter="sidebarHover = true"
    @mouseleave="sidebarHover = false"
    class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col overflow-hidden bg-gradient-to-b from-slate-950 via-indigo-950 to-slate-950 text-white shadow-2xl transition-[width,transform] duration-300 lg:translate-x-0"
>
    <div class="flex h-20 items-center justify-between border-b border-white/10 px-2">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
            <span class="grid h-11 w-16 shrink-0 flex-none place-items-center overflow-hidden rounded-xl p-1"><img src="{{ asset('logo.svg') }}" alt="MMS Group logo" class="h-full w-full object-contain"></span>
            <span x-show="sidebarOpen || sidebarExpanded || sidebarHover" x-transition.opacity class="min-w-0 whitespace-nowrap"><b class="block truncate text-sm">{{ config('app.name', 'Laravel') }}</b><span class="text-[10px] font-bold uppercase tracking-[.18em] text-indigo-300">Management</span></span>
        </a>
        <button type="button" @click="sidebarOpen = false" class="rounded-lg p-2 text-white/60 hover:bg-white/10 hover:text-white lg:hidden">✕</button>
        <button type="button" x-show="sidebarExpanded || sidebarHover" @click="toggleSidebar()" class="hidden h-8 w-8 shrink-0 place-items-center rounded-lg bg-white/10 text-xs text-white/70 transition hover:bg-white/20 hover:text-white lg:grid" :title="sidebarExpanded ? 'Collapse sidebar' : 'Keep sidebar open'">
            <span x-text="sidebarExpanded ? '‹' : '›'">›</span>
        </button>
    </div>

    <nav class="admin-sidebar-nav flex-1 space-y-1 overflow-y-auto px-3 py-5">
        <div x-show="sidebarOpen || sidebarExpanded || sidebarHover" x-transition.opacity class="mb-3 whitespace-nowrap px-3 text-[9px] font-black uppercase tracking-[.2em] text-indigo-400">Workspace</div>
        @foreach($items as $item)
            @if((!($item['hide_from_staff'] ?? false) || Auth::user()->role !== 'staff') && (!($item['super_admin_only'] ?? false) || Auth::user()->role === 'super_admin'))
            @can($item['permission'])
                <a href="{{ route($item['route']) }}" @click="sidebarOpen = false" :title="(sidebarExpanded || sidebarHover) ? '' : @js($item['label'])" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ request()->routeIs($item['active']) ? 'bg-white text-indigo-950 shadow-lg shadow-black/20' : 'text-indigo-100/75 hover:bg-white/10 hover:text-white' }}">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-base {{ request()->routeIs($item['active']) ? 'bg-indigo-100 text-indigo-700' : 'bg-white/5 text-indigo-300 group-hover:bg-white/10' }}">{{ $item['icon'] }}</span>
                    <span x-show="sidebarOpen || sidebarExpanded || sidebarHover" x-transition.opacity class="flex-1 whitespace-nowrap">{{ $item['label'] }}</span>
                    @if($item['route'] === 'management.notifications.index' && Auth::user()->unreadNotifications()->count())
                        <span x-show="sidebarOpen || sidebarExpanded || sidebarHover" class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-black text-white">{{ Auth::user()->unreadNotifications()->count() }}</span>
                    @endif
                </a>
            @endcan
            @endif
        @endforeach

        @canany(['manage commissions', 'manage withdrawals', 'manage packages', 'manage projects', 'manage allotments', 'manage payments', 'manage notifications'])
            <div x-data="{ settingsOpen: @js($settingsActive) }" class="pt-1">
                <button type="button" @click="settingsOpen = !settingsOpen" :title="(sidebarExpanded || sidebarHover) ? '' : 'Settings'" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition {{ $settingsActive ? 'bg-white/15 text-white' : 'text-indigo-100/75 hover:bg-white/10 hover:text-white' }}">
                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-base {{ $settingsActive ? 'bg-indigo-400/30 text-white' : 'bg-white/5 text-indigo-300 group-hover:bg-white/10' }}">
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.245a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a6.759 6.759 0 0 1 0 .255c-.008.378.137.75.43.992l1.003.827c.424.35.534.954.26 1.43l-1.296 2.247a1.125 1.125 0 0 1-1.37.489l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.592c-.55 0-1.02-.397-1.11-.94l-.213-1.281c-.063-.374-.313-.686-.645-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.075-.124l-1.217.456a1.125 1.125 0 0 1-1.37-.49l-1.296-2.245a1.125 1.125 0 0 1 .26-1.431l1.003-.827c.293-.242.438-.614.43-.992a6.79 6.79 0 0 1 0-.255c.008-.379-.137-.75-.43-.992l-1.003-.827a1.125 1.125 0 0 1-.26-1.43l1.296-2.247a1.125 1.125 0 0 1 1.37-.489l1.217.456c.355.133.75.072 1.076-.124.072-.044.146-.087.22-.128.331-.183.581-.495.644-.869l.213-1.281Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    </span>
                    <span x-show="sidebarOpen || sidebarExpanded || sidebarHover" x-transition.opacity class="flex-1 whitespace-nowrap text-left">Settings</span>
                    <svg x-show="sidebarOpen || sidebarExpanded || sidebarHover" :class="settingsOpen ? 'rotate-180' : ''" class="h-3.5 w-3.5 shrink-0 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>

                <div x-show="settingsOpen && (sidebarOpen || sidebarExpanded || sidebarHover)" x-transition class="ml-7 mt-1 space-y-1 border-l border-indigo-400/25 pl-3">
                    @foreach($settingsItems as $setting)
                        @if(!($setting['super_admin_only'] ?? false) || Auth::user()->role === 'super_admin')
                            @can($setting['permission'])
                            <a href="{{ route($setting['route']).(isset($setting['fragment']) ? '#'.$setting['fragment'] : '') }}" @click="sidebarOpen = false" class="group flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-semibold transition {{ request()->routeIs($setting['active']) ? 'bg-white text-indigo-950 shadow-md' : 'text-indigo-200/70 hover:bg-white/10 hover:text-white' }}">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full {{ $setting['color'] }} {{ request()->routeIs($setting['active']) ? 'ring-4 ring-indigo-100' : '' }}"></span>
                                <span class="whitespace-nowrap">{{ $setting['label'] }}</span>
                            </a>
                            @endcan
                        @endif
                    @endforeach
                </div>
            </div>
        @endcanany
    </nav>

    <div class="border-t border-white/10 p-3">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl p-3 transition hover:bg-white/10">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-indigo-500 text-xs font-black">{{ collect(explode(' ', Auth::user()->name))->take(2)->map(fn($word) => strtoupper(substr($word, 0, 1)))->join('') }}</span>
            <span x-show="sidebarOpen || sidebarExpanded || sidebarHover" x-transition.opacity class="min-w-0 flex-1 whitespace-nowrap"><b class="block truncate text-xs">{{ Auth::user()->name }}</b><span class="block truncate text-[10px] text-indigo-300">{{ ucfirst(Auth::user()->role) }}</span></span>
            <span x-show="sidebarOpen || sidebarExpanded || sidebarHover" class="text-white/40">›</span>
        </a>
    </div>
</aside>
