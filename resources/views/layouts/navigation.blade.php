<nav x-data="{ open: false }" class="{{ Auth::user()->role === 'customer' ? 'sticky top-0 z-50 border-b border-indigo-100/80 bg-white/90 shadow-lg shadow-indigo-100/40 backdrop-blur-xl dark:border-slate-700 dark:bg-slate-950/90' : 'bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700' }}">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                        @if(Auth::user()->role === 'customer')
                            <span class="relative grid h-11 w-18 flex-none place-items-center overflow-hidden  p-1"><img src="{{ asset('logo.svg') }}" alt="MMS Group logo" class="h-full w-full object-contain"></span>
                            <span class="hidden leading-tight lg:block">
                                <b class="block text-sm font-black tracking-tight text-slate-950 dark:text-white">MMS Group</b>
                                <span class="block text-[9px] font-bold uppercase tracking-[.18em] text-indigo-500">Customer portal</span>
                            </span>
                        @else
                            <x-application-logo class="block h-10 w-16" />
                        @endif
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden {{ Auth::user()->role === 'customer' ? 'items-center gap-1 sm:ms-6' : 'space-x-8 sm:-my-px sm:ms-10' }} sm:flex">
                    @if(Auth::user()->role === 'customer')
                    @php($customerNavClass = 'inline-flex items-center rounded-xl px-3 py-2 text-xs font-black transition')
                    <a href="{{ route('dashboard') }}" class="{{ $customerNavClass }} {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 dark:shadow-none' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">Overview</a>
                    <a href="{{ route('customer.bookings.create') }}" class="{{ $customerNavClass }} {{ request()->routeIs('customer.bookings.*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 dark:shadow-none' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">Book a plot</a>
                    <a href="{{ route('customer.installments') }}" class="{{ $customerNavClass }} {{ request()->routeIs('customer.installments') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 dark:shadow-none' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">Installments</a>
                    <a href="{{ route('dashboard').'#payments' }}" class="{{ $customerNavClass }} text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">Payments</a>
                    <a href="{{ route('customer.team') }}" class="{{ $customerNavClass }} {{ request()->routeIs('customer.team') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 dark:shadow-none' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">Team</a>
                    <a href="{{ route('customer.commissions') }}" class="{{ $customerNavClass }} {{ request()->routeIs('customer.commissions') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200 dark:shadow-none' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">Commissions</a>
                    @else
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(Auth::user()->role === 'agent')
                    <x-nav-link :href="route('dashboard').'#commissions'" :active="false">{{ __('My commissions') }}</x-nav-link>
                    <x-nav-link :href="route('dashboard').'#sales'" :active="false">{{ __('My sales') }}</x-nav-link>
                    @else
                    <x-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                        {{ __('Projects') }}
                    </x-nav-link>
                    <x-nav-link :href="route('packages.index')" :active="request()->routeIs('packages.*')">
                        {{ __('Packages') }}
                    </x-nav-link>
                    <x-nav-link :href="route('installments.index')" :active="request()->routeIs('installments.*')">
                        {{ __('Installments') }}
                    </x-nav-link>
                    <x-nav-link :href="route('bookings.index')" :active="request()->routeIs('bookings.*')">
                        {{ __('Bookings') }}
                    </x-nav-link>
                    {{-- <x-nav-link :href="route('allotments.index')" :active="request()->routeIs('allotments.*')">{{ __('Allotments') }}</x-nav-link> --}}
                    <x-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">{{ __('Customer') }}</x-nav-link>
                    @if(Auth::user()->role !== 'staff')
                        @can('manage staff')
                        <x-nav-link :href="route('staff.index')" :active="request()->routeIs('staff.*')">{{ __('Staff') }}</x-nav-link>
                        @endcan
                    @endif
                    <x-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')">{{ __('Payments') }}</x-nav-link>
                    @if(Auth::user()->role !== 'staff')
                        <x-nav-link :href="route('payment-methods.index')" :active="request()->routeIs('payment-methods.*')">{{ __('Payment settings') }}</x-nav-link>
                    @endif
                    <x-nav-link :href="route('commission-rules.index')" :active="request()->routeIs('commission-rules.*')">{{ __('Commissions') }}</x-nav-link>
                    <x-nav-link :href="route('management.notifications.index')" :active="request()->routeIs('management.notifications.*')">{{ __('Alerts') }} @if(Auth::user()->unreadNotifications()->count())<span class="ms-1 rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-black text-white">{{ Auth::user()->unreadNotifications()->count() }}</span>@endif</x-nav-link>
                    <x-nav-link :href="route('management.whatsapp.index')" :active="request()->routeIs('management.whatsapp.*')">{{ __('WhatsApp') }}</x-nav-link>
                    @if(Auth::user()->role !== 'staff')
                        <x-nav-link :href="route('management.activity-log.index')" :active="request()->routeIs('management.activity-log.*')">{{ __('Audit log') }}</x-nav-link>
                    @endif
                    @endif
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @if(Auth::user()->role === 'customer')
                    <a href="{{ route('customer.withdrawals.index') }}" class="me-2 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-3.5 py-2.5 text-xs font-black text-white shadow-md shadow-emerald-200 transition hover:-translate-y-0.5 dark:shadow-none"><span>↗</span> Withdraw</a>
                    <a href="{{ route('customer.notifications.index') }}" class="relative me-2 grid h-10 w-10 place-items-center rounded-xl border border-indigo-100 bg-indigo-50 text-indigo-600 transition hover:-translate-y-0.5 hover:bg-indigo-100 dark:border-slate-700 dark:bg-slate-800 dark:text-indigo-300" title="Notifications" aria-label="Notifications">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                        @if(Auth::user()->unreadNotifications()->count())<span class="absolute -right-1 -top-1 min-w-5 rounded-full bg-rose-500 px-1 text-center text-[9px] font-black leading-5 text-white ring-2 ring-white dark:ring-slate-950">{{ Auth::user()->unreadNotifications()->count() > 99 ? '99+' : Auth::user()->unreadNotifications()->count() }}</span>@endif
                    </a>
                @endif
                <x-dropdown align="right" width="64">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center {{ Auth::user()->role === 'customer' ? 'gap-2 rounded-xl border border-slate-200 bg-white py-1.5 ps-1.5 pe-3 shadow-sm hover:border-indigo-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-900' : 'px-3 py-2 border border-transparent rounded-md bg-white dark:bg-gray-800' }} text-sm leading-4 font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition">
                            @if(Auth::user()->role === 'customer')<span class="grid h-8 w-8 place-items-center rounded-lg bg-gradient-to-br from-fuchsia-500 to-indigo-700 text-xs font-black text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>@endif
                            <div class="{{ Auth::user()->role === 'customer' ? 'max-w-28 truncate font-bold text-slate-700 dark:text-slate-200' : '' }}">{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <form method="POST" action="{{ route('theme.update') }}" class="border-b border-gray-100 px-4 py-3 dark:border-gray-600">@csrf @method('PATCH')
                            <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Appearance
                                <select name="theme" onchange="this.form.submit()" class="mt-2 w-full rounded-md border-gray-300 py-1.5 text-sm dark:border-gray-500 dark:bg-gray-800 dark:text-white">
                                    <option value="light" @selected(Auth::user()->theme !== 'dark')>☀ Light mode</option>
                                    <option value="dark" @selected(Auth::user()->theme === 'dark')>☾ Dark mode</option>
                                </select>
                            </label>
                        </form>
                        @if(in_array(Auth::user()->role, ['super_admin', 'admin'], true))
                            <div class="border-t border-gray-100 px-4 pb-1 pt-3 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:border-gray-600">Management settings</div>
                            <x-dropdown-link :href="route('allotments.index')">
                                {{ __('Plot allotments & inventory') }}
                            </x-dropdown-link>
                        @endif
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                @if(Auth::user()->role === 'customer')
                    <a href="{{ route('customer.notifications.index') }}" class="relative me-2 grid h-10 w-10 place-items-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-slate-800 dark:text-indigo-300" aria-label="Notifications">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31"/></svg>
                        @if(Auth::user()->unreadNotifications()->count())<span class="absolute -right-1 -top-1 h-5 min-w-5 rounded-full bg-rose-500 px-1 text-center text-[9px] font-black leading-5 text-white">{{ Auth::user()->unreadNotifications()->count() > 99 ? '99+' : Auth::user()->unreadNotifications()->count() }}</span>@endif
                    </a>
                @endif
                <button @click="open = ! open" class="inline-flex h-10 w-10 items-center justify-center rounded-xl {{ Auth::user()->role === 'customer' ? 'bg-gradient-to-br from-indigo-600 to-violet-700 text-white shadow-md' : 'text-gray-400 hover:bg-gray-100 dark:text-gray-500 dark:hover:bg-gray-900' }} focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden {{ Auth::user()->role === 'customer' ? 'border-t border-indigo-100 bg-gradient-to-b from-indigo-50/80 to-white dark:border-slate-700 dark:from-slate-900 dark:to-slate-950' : '' }} sm:hidden">
        @if(Auth::user()->role === 'customer')
            <div class="mx-4 mt-4 rounded-2xl bg-gradient-to-r from-indigo-700 to-violet-700 p-4 text-white shadow-lg">
                <div class="text-[9px] font-black uppercase tracking-[.18em] text-indigo-200">Signed in as</div>
                <div class="mt-1 truncate text-sm font-black">{{ Auth::user()->name }}</div>
                <div class="mt-0.5 truncate text-xs text-indigo-200">{{ Auth::user()->email }}</div>
            </div>
        @endif
        <div class="space-y-1 px-2 pb-3 pt-3">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if(Auth::user()->role === 'customer')
            <x-responsive-nav-link :href="route('customer.bookings.create')" :active="request()->routeIs('customer.bookings.*')">{{ __('Book a plot') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('customer.installments')" :active="request()->routeIs('customer.installments')">{{ __('Plot installments') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dashboard').'#payments'" :active="false">{{ __('Payments') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('customer.team')" :active="request()->routeIs('customer.team')">{{ __('Team') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('customer.commissions')" :active="request()->routeIs('customer.commissions')">{{ __('Commissions') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('customer.withdrawals.index')" :active="request()->routeIs('customer.withdrawals.*')">{{ __('Withdraw commission') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('customer.notifications.index')" :active="request()->routeIs('customer.notifications.*')">{{ __('Notifications') }} @if(Auth::user()->unreadNotifications()->count())<span class="ms-1 rounded-full bg-rose-500 px-2 py-0.5 text-xs font-black text-white">{{ Auth::user()->unreadNotifications()->count() }}</span>@endif</x-responsive-nav-link>
            @elseif(Auth::user()->role === 'agent')
            <x-responsive-nav-link :href="route('dashboard').'#commissions'" :active="false">{{ __('My commissions') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dashboard').'#sales'" :active="false">{{ __('My sales') }}</x-responsive-nav-link>
            @else
            <x-responsive-nav-link :href="route('sales.create')" :active="request()->routeIs('sales.*')">{{ __('New Booking') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
                {{ __('Projects') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('packages.index')" :active="request()->routeIs('packages.*')">
                {{ __('Packages') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('installments.index')" :active="request()->routeIs('installments.*')">
                {{ __('Installments') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('bookings.index')" :active="request()->routeIs('bookings.*')">
                {{ __('Bookings') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')">{{ __('Customer management') }}</x-responsive-nav-link>
            @if(Auth::user()->role !== 'staff')
                @can('manage staff')
                <x-responsive-nav-link :href="route('staff.index')" :active="request()->routeIs('staff.*')">{{ __('Staff management') }}</x-responsive-nav-link>
                @endcan
            @endif
            <x-responsive-nav-link :href="route('payments.index')" :active="request()->routeIs('payments.*')">{{ __('Payments') }}</x-responsive-nav-link>
            @if(Auth::user()->role !== 'staff')
                <x-responsive-nav-link :href="route('payment-methods.index')" :active="request()->routeIs('payment-methods.*')">{{ __('Payment settings') }}</x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('commission-rules.index')" :active="request()->routeIs('commission-rules.*')">{{ __('Commissions') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('management.notifications.index')" :active="request()->routeIs('management.notifications.*')">{{ __('Alerts') }} @if(Auth::user()->unreadNotifications()->count())<span class="ms-1 rounded-full bg-rose-500 px-2 py-0.5 text-xs font-black text-white">{{ Auth::user()->unreadNotifications()->count() }}</span>@endif</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('management.whatsapp.index')" :active="request()->routeIs('management.whatsapp.*')">{{ __('WhatsApp') }}</x-responsive-nav-link>
            @if(Auth::user()->role !== 'staff')
                <x-responsive-nav-link :href="route('management.activity-log.index')" :active="request()->routeIs('management.activity-log.*')">{{ __('Audit log') }}</x-responsive-nav-link>
            @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <form method="POST" action="{{ route('theme.update') }}" class="px-4 pb-3">@csrf @method('PATCH')
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-300">Appearance
                        <select name="theme" onchange="this.form.submit()" class="mt-2 w-full rounded-md border-gray-300 text-sm dark:border-gray-500 dark:bg-gray-800 dark:text-white"><option value="light" @selected(Auth::user()->theme !== 'dark')>☀ Light mode</option><option value="dark" @selected(Auth::user()->theme === 'dark')>☾ Dark mode</option></select>
                    </label>
                </form>
                @if(in_array(Auth::user()->role, ['super_admin', 'admin'], true))
                    <div class="px-4 pb-1 pt-3 text-[10px] font-black uppercase tracking-widest text-gray-400">Management settings</div>
                    <x-responsive-nav-link :href="route('allotments.index')" :active="request()->routeIs('allotments.*') || request()->routeIs('inventory.*')">
                        {{ __('Plot allotments & inventory') }}
                    </x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
