<x-app-layout>
    <x-slot name="header"><div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-xs font-black uppercase tracking-[.18em] text-indigo-600">Campaign details</p><h2 class="mt-1 text-2xl font-black text-slate-950 dark:text-white">{{ $emailCampaign->name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $emailCampaign->subject }}</p></div><a href="{{ route('email-campaigns.index') }}" class="text-sm font-bold text-indigo-600">← All campaigns</a></div></x-slot>
    <div class="min-h-screen bg-slate-50 py-8 dark:bg-slate-950"><div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6">
        @if(session('success'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
        <section class="grid gap-4 sm:grid-cols-4">
            @foreach([
                ['Recipients',$emailCampaign->recipients_count,'border-indigo-100 text-indigo-600'],
                ['Sent',$emailCampaign->sent_count,'border-emerald-100 text-emerald-600'],
                ['Pending',$emailCampaign->pending_count,'border-amber-100 text-amber-600'],
                ['Failed',$emailCampaign->failed_count,'border-rose-100 text-rose-600'],
            ] as [$label,$value,$styles])
                <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 {{ explode(' ', $styles)[0] }}"><div class="text-[10px] font-black uppercase tracking-widest {{ explode(' ', $styles)[1] }}">{{ $label }}</div><div class="mt-2 text-3xl font-black text-slate-950 dark:text-white">{{ number_format($value) }}</div></div>
            @endforeach
        </section>
        <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-100 px-6 py-5 dark:border-slate-800"><h3 class="font-black dark:text-white">Recipient delivery log</h3></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[700px] text-sm"><thead class="bg-slate-50 text-left text-[10px] font-black uppercase tracking-wider text-slate-400 dark:bg-slate-950"><tr><th class="px-6 py-3">Customer</th><th>Email</th><th>Status</th><th>Sent / failed</th></tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-800">@foreach($recipients as $recipient)<tr><td class="px-6 py-4 font-bold dark:text-white">{{ $recipient->name }}</td><td class="text-slate-500">{{ $recipient->email }}</td><td><span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase {{ $recipient->status === 'sent' ? 'bg-emerald-100 text-emerald-700' : ($recipient->status === 'failed' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">{{ $recipient->status }}</span></td><td class="max-w-xs text-xs text-slate-400">{{ $recipient->sent_at?->format('d M Y, h:i A') ?? $recipient->failure_reason ?? 'Waiting for queue' }}</td></tr>@endforeach</tbody></table></div>
                @if($recipients->hasPages())<div class="border-t border-slate-100 p-5 dark:border-slate-800">{{ $recipients->links() }}</div>@endif
            </div>
            <aside class="space-y-4">
                <div class="rounded-3xl bg-gradient-to-br from-indigo-700 to-violet-800 p-5 text-white shadow-xl"><div class="text-[10px] font-black uppercase tracking-widest text-indigo-200">Campaign status</div><div class="mt-2 text-2xl font-black">{{ ucfirst($emailCampaign->status) }}</div><div class="mt-4 space-y-2 border-t border-white/15 pt-4 text-xs text-indigo-100"><div>Created by <b class="text-white">{{ $emailCampaign->creator->name }}</b></div><div>{{ $emailCampaign->created_at->format('d M Y, h:i A') }}</div>@if($emailCampaign->attachment_name)<div>Attachment: <b class="text-white">{{ $emailCampaign->attachment_name }}</b></div>@endif</div></div>
                @if($emailCampaign->failed_count)<form method="POST" action="{{ route('email-campaigns.retry',$emailCampaign) }}">@csrf<button class="w-full rounded-xl bg-rose-600 px-5 py-3 font-black text-white">Retry {{ $emailCampaign->failed_count }} failed emails</button></form>@endif
                <div class="rounded-3xl border border-slate-200 bg-white p-5 text-sm leading-6 text-slate-500 shadow-sm dark:border-slate-800 dark:bg-slate-900"><b class="block text-slate-900 dark:text-white">Audience filters</b>@forelse(($emailCampaign->filters ?? []) as $key=>$value)<div class="mt-1">{{ ucwords(str_replace('_',' ',$key)) }}: <b>{{ $value }}</b></div>@empty<div class="mt-1">All active subscribed customers</div>@endforelse</div>
            </aside>
        </section>
    </div></div>
</x-app-layout>
