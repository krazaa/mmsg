<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div><p class="text-xs font-black uppercase tracking-[.18em] text-rose-600">Communication settings</p><h2 class="mt-1 text-2xl font-black text-slate-950 dark:text-white">Bulk email campaigns</h2><p class="mt-1 text-sm text-slate-500">Create customer campaigns and monitor every delivery.</p></div>
            <a href="{{ route('email-campaigns.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-indigo-200">＋ New campaign</a>
        </div>
    </x-slot>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50/60 via-slate-50 to-rose-50/50 py-8 dark:from-slate-950 dark:via-slate-950 dark:to-indigo-950/30">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6">
            @if(session('success'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
            <section class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-gradient-to-br from-indigo-700 to-violet-800 p-5 text-white shadow-xl"><div class="text-[10px] font-black uppercase tracking-widest text-indigo-200">Sent today</div><div class="mt-2 text-4xl font-black">{{ number_format($sentToday) }}</div><div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/15"><div class="h-full rounded-full bg-cyan-300" style="width:{{ min(100, $dailyLimit ? ($sentToday / $dailyLimit * 100) : 0) }}%"></div></div></div>
                <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><div class="text-[10px] font-black uppercase tracking-widest text-amber-600">Daily allowance</div><div class="mt-2 text-4xl font-black text-slate-950 dark:text-white">{{ number_format($dailyLimit) }}</div><p class="mt-1 text-xs text-slate-400">Configured provider safety limit</p></div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900"><div class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Available today</div><div class="mt-2 text-4xl font-black text-emerald-700">{{ number_format(max(0, $dailyLimit - $sentToday)) }}</div><p class="mt-1 text-xs text-slate-400">Remaining delivery capacity</p></div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-800"><h3 class="font-black text-slate-900 dark:text-white">Campaign history</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-sm">
                        <thead class="bg-slate-50 text-left text-[10px] font-black uppercase tracking-wider text-slate-400 dark:bg-slate-950"><tr><th class="px-6 py-3">Campaign</th><th>Progress</th><th>Sent</th><th>Failed</th><th>Status</th><th>Created</th><th></th></tr></thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($campaigns as $campaign)
                                @php($progress = $campaign->recipients_count ? round((($campaign->sent_count + $campaign->failed_count) / $campaign->recipients_count) * 100) : 0)
                                <tr class="hover:bg-indigo-50/30 dark:hover:bg-slate-800/50">
                                    <td class="px-6 py-4"><div class="font-black text-slate-900 dark:text-white">{{ $campaign->name }}</div><div class="mt-0.5 max-w-xs truncate text-xs text-slate-400">{{ $campaign->subject }}</div></td>
                                    <td class="w-48"><div class="flex items-center gap-2"><div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"><div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500" style="width:{{ $progress }}%"></div></div><b class="text-xs">{{ $progress }}%</b></div><div class="mt-1 text-[10px] text-slate-400">{{ $campaign->pending_count }} pending of {{ $campaign->recipients_count }}</div></td>
                                    <td class="font-black text-emerald-600">{{ $campaign->sent_count }}</td><td class="font-black {{ $campaign->failed_count ? 'text-rose-600' : 'text-slate-300' }}">{{ $campaign->failed_count }}</td>
                                    <td><span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $campaign->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($campaign->status === 'sending' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700') }}">{{ $campaign->status }}</span></td>
                                    <td class="text-xs text-slate-400">{{ $campaign->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="pr-6 text-right"><a href="{{ route('email-campaigns.show', $campaign) }}" class="font-bold text-indigo-600">Details →</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-20 text-center"><div class="text-4xl">✉</div><h3 class="mt-3 font-black text-slate-900 dark:text-white">No campaigns yet</h3><p class="mt-1 text-sm text-slate-400">Create your first customer email campaign.</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($campaigns->hasPages())<div class="border-t border-slate-100 p-5 dark:border-slate-800">{{ $campaigns->links() }}</div>@endif
            </section>
        </div>
    </div>
</x-app-layout>
