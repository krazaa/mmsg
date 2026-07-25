<x-app-layout>
    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-cyan-50 py-6 dark:from-slate-950 dark:via-slate-950 dark:to-indigo-950/40 sm:py-8">
        <div class="pointer-events-none absolute -left-32 top-20 h-80 w-80 rounded-full bg-violet-200/40 blur-3xl dark:bg-violet-900/20"></div>
        <div class="pointer-events-none absolute -right-32 top-96 h-96 w-96 rounded-full bg-cyan-200/40 blur-3xl dark:bg-cyan-900/20"></div>
        <div class="relative mx-auto max-w-5xl space-y-6 px-4 sm:px-6">
            <section class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-slate-950 via-indigo-900 to-violet-700 p-6 text-white shadow-2xl shadow-indigo-200/70 dark:shadow-none sm:p-8">
                <div class="absolute -right-16 -top-20 -z-10 h-72 w-72 rounded-full bg-fuchsia-400/30 blur-3xl"></div>
                <div class="absolute inset-0 -z-10 opacity-[.07]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px"></div>
                <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-indigo-300/20 bg-white/10 px-3 py-1 text-[10px] font-black uppercase tracking-[.16em] text-indigo-100"><span class="h-2 w-2 rounded-full bg-cyan-300"></span>Account activity</div>
                        <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Your notifications</h1>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-indigo-100">Stay updated on booking approvals, payment verification, installment reminders and important account activity.</p>
                    </div>
                    @if($unreadCount)
                        <form method="POST" action="{{ route('customer.notifications.read-all') }}">@csrf
                            <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-black text-indigo-800 shadow-xl transition hover:-translate-y-0.5 hover:bg-indigo-50 sm:w-auto"><span>✓</span> Mark all as read</button>
                        </form>
                    @endif
                </div>
            </section>

            @if(session('success'))<div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800 shadow-sm"><span class="grid h-7 w-7 place-items-center rounded-full bg-emerald-500 text-white">✓</span>{{ session('success') }}</div>@endif

            <div class="grid grid-cols-3 gap-3">
                <div class="rounded-2xl border border-indigo-100 bg-white p-4 shadow-lg shadow-indigo-100/50 dark:border-slate-700 dark:bg-slate-900"><div class="text-[9px] font-black uppercase tracking-wider text-slate-400">All updates</div><div class="mt-2 text-2xl font-black text-indigo-700 dark:text-indigo-300">{{ $totalCount }}</div></div>
                <div class="rounded-2xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white p-4 shadow-lg shadow-rose-100/50 dark:border-slate-700 dark:from-slate-900 dark:to-slate-900"><div class="text-[9px] font-black uppercase tracking-wider text-rose-500">Unread</div><div class="mt-2 text-2xl font-black text-rose-600">{{ $unreadCount }}</div></div>
                <div class="rounded-2xl border border-cyan-100 bg-gradient-to-br from-cyan-50 to-white p-4 shadow-lg shadow-cyan-100/50 dark:border-slate-700 dark:from-slate-900 dark:to-slate-900"><div class="text-[9px] font-black uppercase tracking-wider text-cyan-600">Received today</div><div class="mt-2 text-2xl font-black text-cyan-700 dark:text-cyan-300">{{ $todayCount }}</div></div>
            </div>

            <section class="overflow-hidden rounded-3xl border border-indigo-100 bg-white/90 shadow-xl shadow-slate-200/70 backdrop-blur dark:border-slate-700 dark:bg-slate-900/90 dark:shadow-none">
                <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5 dark:border-slate-800">
                    <div><h2 class="font-black text-slate-950 dark:text-white">Activity inbox</h2><p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Select an update to view its related booking or payment.</p></div>
                    <div class="flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
                        @foreach(['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $value => $label)
                            <a href="{{ route('customer.notifications.index', ['status' => $value]) }}" class="flex-1 rounded-lg px-4 py-2 text-center text-xs font-black transition {{ $status === $value ? 'bg-white text-indigo-700 shadow-sm dark:bg-slate-700 dark:text-indigo-300' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left">
                        <thead class="bg-slate-50/90 text-[10px] font-black uppercase tracking-[.12em] text-slate-500 dark:bg-slate-800/80 dark:text-slate-300">
                            <tr><th class="w-12 px-5 py-3.5">Status</th><th class="px-3 py-3.5">Type</th><th class="px-3 py-3.5">Notification</th><th class="px-3 py-3.5">Details</th><th class="whitespace-nowrap px-3 py-3.5">Received</th><th class="px-5 py-3.5 text-right">Action</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($notifications as $notification)
                                @php($category = $notification->data['category'] ?? 'account')
                                @php($theme = match($category) {'booking' => ['from-violet-500 to-fuchsia-600','⌂','Booking','bg-violet-50 text-violet-700 dark:bg-violet-950/40 dark:text-violet-300'], 'payment' => ['from-emerald-500 to-teal-600','Rs','Payment','bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'], 'reminder' => ['from-amber-400 to-orange-600','!','Reminder','bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'], default => ['from-indigo-500 to-blue-600','#','Account','bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300']})
                                <tr class="group transition hover:bg-indigo-50/60 dark:hover:bg-slate-800/60 {{ $notification->read_at ? 'bg-white dark:bg-slate-900' : 'bg-indigo-50/30 dark:bg-indigo-950/20' }}">
                                    <td class="px-5 py-4">@if($notification->read_at)<span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-[9px] font-black text-slate-500 dark:bg-slate-800 dark:text-slate-300">Read</span>@else<span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2 py-1 text-[9px] font-black text-rose-600 dark:bg-rose-950/40 dark:text-rose-300"><span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>New</span>@endif</td>
                                    <td class="px-3 py-4"><span class="inline-flex items-center gap-2 rounded-xl px-2.5 py-2 text-[10px] font-black {{ $theme[3] }}"><span class="grid h-7 w-7 place-items-center rounded-lg bg-gradient-to-br {{ $theme[0] }} text-[10px] text-white shadow-sm">{{ $theme[1] }}</span>{{ $theme[2] }}</span></td>
                                    <td class="max-w-sm px-3 py-4"><b class="block text-sm text-slate-950 dark:text-white">{{ $notification->data['title'] ?? 'Account update' }}</b><span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-300">{{ $notification->data['message'] ?? '' }}</span></td>
                                    <td class="px-3 py-4">@if($notification->data['details'] ?? [])<div class="flex max-w-xs flex-wrap gap-1.5">@foreach($notification->data['details'] as $label => $value)<span class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[9px] text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"><b>{{ $label }}:</b> {{ $value }}</span>@endforeach</div>@else<span class="text-xs text-slate-300 dark:text-slate-600">—</span>@endif</td>
                                    <td class="whitespace-nowrap px-3 py-4"><span class="block text-xs font-bold text-slate-600 dark:text-slate-300">{{ $notification->created_at->format('d M Y') }}</span><span class="mt-1 block text-[10px] text-slate-400">{{ $notification->created_at->format('h:i A') }} · {{ $notification->created_at->diffForHumans() }}</span></td>
                                    <td class="px-5 py-4 text-right"><form method="POST" action="{{ route('customer.notifications.read', $notification->id) }}">@csrf<button class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-[10px] font-black text-white shadow-sm transition hover:bg-indigo-700">{{ $notification->read_at ? 'Open' : 'Read & open' }} <span class="ms-1">→</span></button></form></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-12 text-center sm:p-16"><span class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-gradient-to-br from-indigo-100 to-violet-100 text-2xl text-indigo-600 dark:from-slate-800 dark:to-slate-700">✓</span><h3 class="mt-4 font-black text-slate-900 dark:text-white">{{ $status === 'unread' ? 'You are all caught up' : 'No notifications found' }}</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $status === 'unread' ? 'There are no unread messages in your inbox.' : 'New account activity will appear here.' }}</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            @if($notifications->hasPages())<div>{{ $notifications->links() }}</div>@endif
        </div>
    </div>
</x-app-layout>
