<x-app-layout>
    <div class="customer-theme-page relative min-h-screen overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-cyan-50 py-6 dark:from-slate-950 dark:via-slate-950 dark:to-indigo-950/40 sm:py-8">
        <div class="pointer-events-none absolute -left-32 top-20 h-80 w-80 rounded-full bg-violet-200/40 blur-3xl dark:bg-violet-900/20"></div>
        <div class="pointer-events-none absolute -right-32 top-96 h-96 w-96 rounded-full bg-cyan-200/40 blur-3xl dark:bg-cyan-900/20"></div>
        <div class="relative mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="customer-theme-marketplace-hero relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-indigo-900 to-violet-700 text-white shadow-2xl shadow-indigo-200/70 dark:shadow-none">
                <div class="customer-theme-blur absolute -right-16 -top-20 -z-10 h-72 w-72 rounded-full bg-fuchsia-400/30 blur-3xl"></div>
                <div class="absolute inset-0 -z-10 opacity-[.07]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px"></div>
                <div class="grid lg:grid-cols-2">
                    <header class="flex flex-col justify-center p-6 sm:p-7 lg:p-8">
                        <div class="customer-theme-hero-copy inline-flex w-fit items-center gap-2 rounded-full border border-indigo-300/20 bg-white/10 px-3 py-1 text-[10px] font-black uppercase tracking-[.16em] text-indigo-100"><span class="h-2 w-2 rounded-full bg-cyan-300"></span>Account activity</div>
                        <h1 class="customer-theme-hero-heading mt-4 text-3xl font-black tracking-tight sm:text-4xl">Your notifications</h1>
                        <p class="customer-theme-hero-copy mt-2 max-w-xl text-sm leading-6 text-indigo-100">Stay updated on booking approvals, payment verification, installment reminders and important account activity.</p>
                        @if($unreadCount)
                            <form method="POST" action="{{ route('customer.notifications.read-all') }}" class="mt-5">@csrf
                                <button class="customer-light-action inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-black text-indigo-800 shadow-xl transition hover:-translate-y-0.5 hover:bg-indigo-50 sm:w-auto"><span>✓</span> Mark all as read</button>
                            </form>
                        @endif
                    </header>
                    <aside class="customer-theme-hero-panel flex flex-col justify-center border-t border-white/10 bg-white/[.08] p-6 backdrop-blur-md lg:border-l lg:border-t-0 sm:p-7 lg:p-8">
                        <div class="flex items-center justify-between gap-4">
                            <div><div class="customer-theme-hero-copy text-[10px] font-black uppercase tracking-[.18em] text-indigo-200">Activity overview</div><div class="mt-1 text-sm font-bold">Your account updates</div></div>
                            <span class="grid h-10 w-10 place-items-center rounded-xl border border-white/15 bg-white/10 text-lg">✉</span>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4"><div class="text-[9px] font-black uppercase tracking-wider text-white/60">All updates</div><div class="mt-1 text-2xl font-black">{{ $totalCount }}</div></div>
                            <div class="rounded-2xl border border-rose-200/15 bg-rose-300/10 p-4"><div class="text-[9px] font-black uppercase tracking-wider text-rose-200">Unread</div><div class="mt-1 text-2xl font-black text-rose-200">{{ $unreadCount }}</div></div>
                        </div>
                        <div class="mt-3 rounded-2xl border border-cyan-200/15 bg-cyan-300/10 p-4">
                            <div class="text-[9px] font-black uppercase tracking-wider text-cyan-200">Received today</div>
                            <div class="mt-1 text-2xl font-black text-cyan-200">{{ $todayCount }}</div>
                        </div>
                    </aside>
                </div>
            </section>

            @if(session('success'))<div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800 shadow-sm"><span class="grid h-7 w-7 place-items-center rounded-full bg-emerald-500 text-white">✓</span>{{ session('success') }}</div>@endif

            <section class="customer-notification-inbox customer-theme-card overflow-hidden rounded-3xl border border-indigo-100 bg-white/90 shadow-xl shadow-slate-200/70 backdrop-blur dark:border-slate-700 dark:bg-slate-900/90 dark:shadow-none">
                <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5 dark:border-slate-800">
                    <div><h2 class="font-black text-slate-950 dark:text-white">Activity inbox</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Select an update to view its related booking or payment.</p></div>
                    <div class="flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
                        @foreach(['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $value => $label)
                            <a href="{{ route('customer.notifications.index', ['status' => $value]) }}" class="flex-1 rounded-lg px-4 py-2 text-center text-xs font-black transition {{ $status === $value ? 'customer-notification-filter-active bg-white text-indigo-700 shadow-sm dark:bg-slate-700 dark:text-indigo-300' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800 md:hidden">
                    @forelse($notifications as $notification)
                        @php($category = $notification->data['category'] ?? 'account')
                        @php($theme = match($category) {'booking' => ['from-violet-500 to-fuchsia-600','⌂','Booking','bg-violet-50 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300'], 'payment' => ['from-emerald-500 to-teal-600','Rs','Payment','bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'], 'reminder' => ['from-amber-400 to-orange-600','!','Reminder','bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'], default => ['from-indigo-500 to-blue-600','#','Account','bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300']})
                        @php($notificationDetails = collect($notification->data['details'] ?? [])->except('Package')->union($bookingDetails->get($notification->data['details']['Booking'] ?? '', [])))
                        <article class="p-4 {{ $notification->read_at ? 'bg-white dark:bg-slate-900' : 'bg-indigo-50/40 dark:bg-indigo-950/20' }}">
                            <div class="flex items-start justify-between gap-3">
                                <span class="customer-notification-type inline-flex min-w-0 items-center gap-2 rounded-xl px-2.5 py-2 text-[10px] font-black {{ $theme[3] }}">
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-gradient-to-br {{ $theme[0] }} text-[10px] text-white shadow-sm">{{ $theme[1] }}</span>
                                    <span class="truncate">{{ $theme[2] }}<small class="mt-0.5 block text-[8px] font-bold opacity-65">{{ $notification->read_at ? 'Read' : 'Unread' }}</small></span>
                                </span>
                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full text-white shadow-sm {{ $notification->read_at ? 'bg-black dark:bg-white dark:text-black' : 'bg-rose-500' }}">
                                    @if($notification->read_at)
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 10 8.1-5.4a1.6 1.6 0 0 1 1.8 0L21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-9Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m3 10 7.7 5.1a2.3 2.3 0 0 0 2.6 0L21 10"/></svg>
                                    @else
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m3 7 7.7 5.1a2.3 2.3 0 0 0 2.6 0L21 7"/></svg>
                                    @endif
                                </span>
                            </div>
                            <h3 class="customer-notification-title mt-4 text-base font-black text-slate-950 dark:text-white">{{ $notification->data['title'] ?? 'Account update' }}</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-300">{{ $notification->data['message'] ?? '' }}</p>
                            @if($notificationDetails->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-1.5">@foreach($notificationDetails as $label => $value)<span class="customer-notification-detail rounded-md border border-slate-200 bg-white px-2 py-1.5 text-[10px] text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"><b>{{ $label }}:</b> {{ $value }}</span>@endforeach</div>
                            @endif
                            <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                                <div class="min-w-0 text-[10px] text-slate-400"><b class="block text-xs text-slate-600 dark:text-slate-300">{{ $notification->created_at->format('d M Y · h:i A') }}</b><span>{{ $notification->created_at->diffForHumans() }}</span></div>
                                <form method="POST" action="{{ route('customer.notifications.read', $notification->id) }}" class="shrink-0">@csrf<button class="rounded-lg bg-indigo-600 px-3 py-2.5 text-[10px] font-black text-white shadow-sm">{{ $notification->read_at ? 'Open' : 'Read & open' }} →</button></form>
                            </div>
                        </article>
                    @empty
                        <div class="p-10 text-center"><span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-indigo-100 text-xl text-indigo-600 dark:bg-slate-800">✓</span><h3 class="mt-3 font-black text-slate-900 dark:text-white">{{ $status === 'unread' ? 'You are all caught up' : 'No notifications found' }}</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $status === 'unread' ? 'There are no unread messages in your inbox.' : 'New account activity will appear here.' }}</p></div>
                    @endforelse
                </div>
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full min-w-[740px] text-left">
                        <thead class="bg-slate-50/90 text-[10px] font-black uppercase tracking-[.12em] text-slate-500 dark:bg-slate-800/80 dark:text-slate-300">
                            <tr><th class="px-5 py-3.5">Type</th><th class="px-3 py-3.5">Notification</th><th class="px-3 py-3.5">Details</th><th class="whitespace-nowrap px-3 py-3.5">Received</th><th class="px-5 py-3.5 text-right">Action</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($notifications as $notification)
                                @php($category = $notification->data['category'] ?? 'account')
                                @php($theme = match($category) {'booking' => ['from-violet-500 to-fuchsia-600','⌂','Booking','bg-violet-50 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300'], 'payment' => ['from-emerald-500 to-teal-600','Rs','Payment','bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'], 'reminder' => ['from-amber-400 to-orange-600','!','Reminder','bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'], default => ['from-indigo-500 to-blue-600','#','Account','bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300']})
                                @php($notificationDetails = collect($notification->data['details'] ?? [])->except('Package')->union($bookingDetails->get($notification->data['details']['Booking'] ?? '', [])))
                                <tr class="group transition hover:bg-indigo-50/60 dark:hover:bg-slate-800/60 {{ $notification->read_at ? 'bg-white dark:bg-slate-900' : 'bg-indigo-50/30 dark:bg-indigo-950/20' }}">
                                    <td class="px-5 py-4">
                                        <span class="customer-notification-type inline-flex items-center gap-2 rounded-xl px-2.5 py-2 text-[10px] font-black {{ $theme[3] }}">
                                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-gradient-to-br {{ $theme[0] }} text-[10px] text-white shadow-sm">{{ $theme[1] }}</span>
                                            <span>{{ $theme[2] }}<small class="mt-0.5 block text-[8px] font-bold opacity-65">{{ $notification->read_at ? 'Read' : 'Unread' }}</small></span>
                                            <span class="ms-1 grid h-6 w-6 shrink-0 place-items-center rounded-full text-white shadow-sm ring-2 ring-white dark:ring-slate-700 {{ $notification->read_at ? 'bg-black dark:bg-white dark:text-black' : 'bg-rose-500' }}" title="{{ $notification->read_at ? 'Read · opened' : 'Unread · closed' }}">
                                                @if($notification->read_at)
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 10 8.1-5.4a1.6 1.6 0 0 1 1.8 0L21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-9Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m3 10 7.7 5.1a2.3 2.3 0 0 0 2.6 0L21 10"/></svg>
                                                @else
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m3 7 7.7 5.1a2.3 2.3 0 0 0 2.6 0L21 7"/></svg>
                                                @endif
                                            </span>
                                        </span>
                                    </td>
                                    <td class="max-w-sm px-3 py-4"><b class="customer-notification-title block text-sm text-slate-950 dark:text-white">{{ $notification->data['title'] ?? 'Account update' }}</b><span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-300">{{ $notification->data['message'] ?? '' }}</span></td>
                                    <td class="px-3 py-4">@if($notificationDetails->isNotEmpty())<div class="flex max-w-sm flex-wrap gap-1.5">@foreach($notificationDetails as $label => $value)<span class="customer-notification-detail rounded-md border border-slate-200 bg-white px-2 py-1 text-[9px] text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"><b>{{ $label }}:</b> {{ $value }}</span>@endforeach</div>@else<span class="text-xs text-slate-300 dark:text-slate-600">—</span>@endif</td>
                                    <td class="whitespace-nowrap px-3 py-4"><span class="block text-xs font-bold text-slate-600 dark:text-slate-300">{{ $notification->created_at->format('d M Y') }}</span><span class="mt-1 block text-[10px] text-slate-400">{{ $notification->created_at->format('h:i A') }} · {{ $notification->created_at->diffForHumans() }}</span></td>
                                    <td class="px-5 py-4 text-right"><form method="POST" action="{{ route('customer.notifications.read', $notification->id) }}">@csrf<button class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-[10px] font-black text-white shadow-sm transition hover:bg-indigo-700">{{ $notification->read_at ? 'Open' : 'Read & open' }} <span class="ms-1">→</span></button></form></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="p-12 text-center sm:p-16"><span class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-gradient-to-br from-indigo-100 to-violet-100 text-2xl text-indigo-600 dark:from-slate-800 dark:to-slate-700">✓</span><h3 class="mt-4 font-black text-slate-900 dark:text-white">{{ $status === 'unread' ? 'You are all caught up' : 'No notifications found' }}</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $status === 'unread' ? 'There are no unread messages in your inbox.' : 'New account activity will appear here.' }}</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            @if($notifications->hasPages())<div>{{ $notifications->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
