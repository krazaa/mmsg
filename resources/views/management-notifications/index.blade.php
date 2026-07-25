<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.2em] text-indigo-600 dark:text-indigo-400">Activity center</p>
                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950 dark:text-white">Alerts & notifications</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Stay on top of bookings, payments and customer activity.</p>
            </div>
            @if($unreadCount)
                <form method="POST" action="{{ route('management.notifications.read-all') }}">
                    @csrf
                    <button class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:border-indigo-200 hover:text-indigo-700 hover:shadow-md dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        Mark all read
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-cyan-50 py-7 dark:from-slate-950 dark:via-slate-950 dark:to-indigo-950/40">
        <div class="pointer-events-none absolute -left-24 top-24 h-72 w-72 rounded-full bg-violet-200/40 blur-3xl dark:bg-violet-900/20"></div>
        <div class="pointer-events-none absolute -right-24 top-1/3 h-80 w-80 rounded-full bg-cyan-200/40 blur-3xl dark:bg-cyan-900/20"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6">
            @if(session('success'))
                <div class="mb-5 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-300">
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-emerald-600 text-white">✓</span>
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid items-start gap-6 lg:grid-cols-[270px_minmax(0,1fr)]">
                <aside class="space-y-4 lg:sticky lg:top-24">
                    <div class="overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 p-6 text-white shadow-xl shadow-indigo-200/60 dark:shadow-none">
                        <div class="flex items-start justify-between">
                            <div class="grid h-12 w-12 place-items-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.85 23.85 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                            </div>
                            @if($unreadCount)
                                <span class="rounded-full bg-rose-500 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider shadow-lg">{{ $unreadCount }} new</span>
                            @endif
                        </div>
                        <p class="mt-8 text-xs font-bold uppercase tracking-[.18em] text-indigo-200">Total alerts</p>
                        <p class="mt-1 text-5xl font-black tracking-tight">{{ number_format($totalCount) }}</p>
                        <div class="mt-5 grid grid-cols-2 gap-2 border-t border-white/15 pt-4">
                            <div>
                                <p class="text-2xl font-black text-rose-300">{{ number_format($unreadCount) }}</p>
                                <p class="text-xs text-indigo-200">Unread</p>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-cyan-300">{{ number_format($todayCount) }}</p>
                                <p class="text-xs text-indigo-200">Today</p>
                            </div>
                        </div>
                    </div>

                    <nav class="rounded-2xl border border-slate-200 bg-white p-2 shadow-sm dark:border-slate-800 dark:bg-slate-900" aria-label="Alert filters">
                        @foreach([
                            'all' => ['label' => 'All notifications', 'count' => $totalCount, 'icon' => 'inbox'],
                            'unread' => ['label' => 'Unread', 'count' => $unreadCount, 'icon' => 'dot'],
                            'read' => ['label' => 'Read', 'count' => max(0, $totalCount - $unreadCount), 'icon' => 'check'],
                        ] as $value => $item)
                            <a href="{{ route('management.notifications.index', ['status' => $value]) }}"
                               class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition {{ $status === $value ? ($value === 'unread' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300' : ($value === 'read' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/70 dark:text-indigo-300')) : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                                <span class="grid h-8 w-8 place-items-center rounded-lg {{ $status === $value ? 'bg-white shadow-sm dark:bg-slate-900' : 'bg-slate-100 dark:bg-slate-800' }}">
                                    @if($item['icon'] === 'inbox')
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512A2.25 2.25 0 0 1 17.89 13.5h3.86m-19.5 0 2.31-8.082A2.25 2.25 0 0 1 6.723 3.75h10.554a2.25 2.25 0 0 1 2.163 1.668l2.31 8.082v4.75A2.25 2.25 0 0 1 19.5 20.5h-15a2.25 2.25 0 0 1-2.25-2.25V13.5Z"/></svg>
                                    @elseif($item['icon'] === 'dot')
                                        <span class="h-2.5 w-2.5 rounded-full bg-rose-500 ring-4 ring-rose-100 dark:ring-rose-950"></span>
                                    @else
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    @endif
                                </span>
                                <span class="flex-1">{{ $item['label'] }}</span>
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] dark:bg-slate-800">{{ number_format($item['count']) }}</span>
                            </a>
                        @endforeach
                    </nav>
                </aside>

                <main class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-slate-800">
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white">{{ $status === 'all' ? 'Recent activity' : ucfirst($status).' alerts' }}</h3>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $notifications->total() }} {{ Str::plural('notification', $notifications->total()) }}</p>
                        </div>
                        <span class="hidden items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-600 sm:flex dark:bg-emerald-950/50 dark:text-emerald-300">
                            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                            Live updates
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($notifications as $notification)
                            @php
                                $category = $notification->data['category'] ?? 'activity';
                                $style = match($category) {
                                    'booking' => ['label' => 'Booking', 'box' => 'bg-violet-100 text-violet-700 ring-violet-200 dark:bg-violet-950/60 dark:text-violet-300 dark:ring-violet-900', 'dot' => 'bg-violet-500', 'row' => 'hover:bg-violet-50/80 dark:hover:bg-violet-950/20', 'line' => 'bg-violet-500'],
                                    'payment' => ['label' => 'Payment', 'box' => 'bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:ring-emerald-900', 'dot' => 'bg-emerald-500', 'row' => 'hover:bg-emerald-50/80 dark:hover:bg-emerald-950/20', 'line' => 'bg-emerald-500'],
                                    default => ['label' => 'Activity', 'box' => 'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:ring-amber-900', 'dot' => 'bg-amber-500', 'row' => 'hover:bg-amber-50/80 dark:hover:bg-amber-950/20', 'line' => 'bg-amber-500'],
                                };
                            @endphp
                            <form method="POST" action="{{ route('management.notifications.read', $notification->id) }}">
                                @csrf
                                <button class="group relative flex w-full gap-4 px-5 py-5 text-left transition sm:px-6 {{ $style['row'] }} {{ $notification->read_at ? '' : 'bg-indigo-50/40 dark:bg-indigo-950/10' }}">
                                    @unless($notification->read_at)
                                        <span class="absolute inset-y-0 left-0 w-1 {{ $style['line'] }}"></span>
                                    @endunless
                                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl ring-1 {{ $style['box'] }}">
                                        @if($category === 'payment')
                                            <span class="text-xs font-black">Rs</span>
                                        @elseif($category === 'booking')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 9.75h16.5m-15-4.5h13.5A1.5 1.5 0 0 1 20.25 6.75v12a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Z"/></svg>
                                        @else
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25 12 10.5m0 0 .75.75M12 10.5v4.125m9-2.625a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        @endif
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                            <span class="flex min-w-0 items-center gap-2">
                                                <span class="truncate text-sm font-extrabold text-slate-900 dark:text-white">{{ $notification->data['title'] ?? 'Admin alert' }}</span>
                                                @unless($notification->read_at)<span class="h-2 w-2 shrink-0 rounded-full {{ $style['dot'] }} ring-4 ring-current/10"></span>@endunless
                                            </span>
                                            <time class="shrink-0 text-[11px] font-semibold text-slate-400" title="{{ $notification->created_at->format('d M Y, h:i A') }}">{{ $notification->created_at->diffForHumans() }}</time>
                                        </span>
                                        <span class="mt-1 block text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $notification->data['message'] ?? '' }}</span>
                                        <span class="mt-3 flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide ring-1 {{ $style['box'] }}"><span class="h-1.5 w-1.5 rounded-full {{ $style['dot'] }}"></span>{{ $style['label'] }}</span>
                                            @foreach(($notification->data['details'] ?? []) as $label => $value)
                                                <span class="text-xs text-slate-400"><b class="font-bold text-slate-600 dark:text-slate-300">{{ $label }}:</b> {{ $value }}</span>
                                            @endforeach
                                            <span class="ml-auto hidden items-center gap-1 text-xs font-bold text-indigo-600 transition group-hover:translate-x-1 sm:inline-flex dark:text-indigo-400">View details <span>→</span></span>
                                        </span>
                                    </span>
                                </button>
                            </form>
                        @empty
                            <div class="px-6 py-24 text-center">
                                <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-emerald-50 text-emerald-600 ring-8 ring-emerald-50/50 dark:bg-emerald-950 dark:text-emerald-300 dark:ring-emerald-950/30">
                                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                </div>
                                <h3 class="mt-6 text-lg font-black text-slate-900 dark:text-white">{{ $status === 'unread' ? 'Everything is up to date' : 'No alerts here yet' }}</h3>
                                <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-400">{{ $status === 'unread' ? 'You have reviewed every notification. New activity will appear here.' : 'Booking, payment and customer alerts will appear here as they happen.' }}</p>
                            </div>
                        @endforelse
                    </div>

                    @if($notifications->hasPages())
                        <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">{{ $notifications->links() }}</div>
                    @endif
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
