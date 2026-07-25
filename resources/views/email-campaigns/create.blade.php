<x-app-layout>
    <x-slot name="header"><div><p class="text-xs font-black uppercase tracking-[.18em] text-indigo-600">Email composer</p><h2 class="mt-1 text-2xl font-black text-slate-950 dark:text-white">Create campaign</h2><p class="mt-1 text-sm text-slate-500">Target the right customers and test before sending.</p></div></x-slot>
    <div class="min-h-screen bg-slate-50 py-8 dark:bg-slate-950">
        <div class="mx-auto max-w-6xl space-y-5 px-4 sm:px-6">
            @if(session('success'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-800">{{ $errors->first() }}</div>@endif

            <form method="GET" class="rounded-3xl border border-indigo-100 bg-gradient-to-r from-indigo-700 to-violet-700 p-5 text-white shadow-xl">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
                    <div class="flex-1"><h3 class="font-black">Choose audience</h3><p class="text-xs text-indigo-200">Only active, subscribed customers with an email address are included.</p></div>
                    <label class="text-xs font-bold">Project<select name="project_id" class="mt-1 block w-full min-w-44 rounded-xl border-white/20 bg-white/10 text-sm text-white"><option value="" class="text-slate-900">All projects</option>@foreach($projects as $project)<option class="text-slate-900" value="{{ $project->id }}" @selected(request('project_id') == $project->id)>{{ $project->name }}</option>@endforeach</select></label>
                    <label class="text-xs font-bold">Package<select name="package_id" class="mt-1 block w-full min-w-44 rounded-xl border-white/20 bg-white/10 text-sm text-white"><option value="" class="text-slate-900">All packages</option>@foreach($packages as $package)<option class="text-slate-900" value="{{ $package->id }}" @selected(request('package_id') == $package->id)>{{ $package->project->name }} · {{ $package->name }}</option>@endforeach</select></label>
                    <label class="text-xs font-bold">Booking status<select name="booking_status" class="mt-1 block w-full min-w-40 rounded-xl border-white/20 bg-white/10 text-sm text-white"><option value="" class="text-slate-900">Any status</option>@foreach(['pending','approved','active','completed','cancelled','defaulted'] as $status)<option class="text-slate-900" value="{{ $status }}" @selected(request('booking_status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
                    <button class="rounded-xl bg-white px-5 py-2.5 text-sm font-black text-indigo-700">Apply filters</button>
                </div>
                <div class="mt-4 flex items-center gap-2 border-t border-white/15 pt-4"><span class="grid h-9 w-9 place-items-center rounded-xl bg-white/15 text-lg font-black">{{ $recipientCount }}</span><span class="text-sm font-bold">matching {{ Str::plural('customer', $recipientCount) }}</span></div>
            </form>

            <form method="POST" action="{{ route('email-campaigns.store') }}" enctype="multipart/form-data" class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
                @csrf
                <input type="hidden" name="project_id" value="{{ request('project_id') }}">
                <input type="hidden" name="package_id" value="{{ request('package_id') }}">
                <input type="hidden" name="booking_status" value="{{ request('booking_status') }}">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="grid gap-5">
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Internal campaign name<input name="name" value="{{ old('name') }}" required placeholder="e.g. July project update" class="mt-1.5 w-full rounded-xl border-slate-300 dark:bg-slate-950"></label>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Email subject<input name="subject" value="{{ old('subject') }}" required placeholder="Important update from MMS Group" class="mt-1.5 w-full rounded-xl border-slate-300 dark:bg-slate-950"></label>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Message body <span class="font-normal text-slate-400">(HTML supported)</span><textarea name="body" rows="14" required placeholder="<p>Write your message here...</p>" class="mt-1.5 w-full rounded-xl border-slate-300 font-mono text-sm leading-6 dark:bg-slate-950">{{ old('body') }}</textarea></label>
                        <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Attachment <span class="font-normal text-slate-400">(optional, maximum 5 MB)</span><input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" class="mt-2 block w-full text-sm text-slate-500"></label>
                    </div>
                </section>
                <aside class="space-y-4 lg:sticky lg:top-24">
                    <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <h3 class="font-black text-slate-900 dark:text-white">Delivery summary</h3>
                        <dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between"><dt class="text-slate-500">Recipients</dt><dd class="font-black">{{ $recipientCount }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Daily limit</dt><dd class="font-black">{{ config('mail.bulk_daily_limit', 300) }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">Estimated days</dt><dd class="font-black">{{ $recipientCount ? ceil($recipientCount / config('mail.bulk_daily_limit', 300)) : 0 }}</dd></div></dl>
                        <button @disabled(!$recipientCount) class="mt-5 w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 font-black text-white shadow-lg disabled:cursor-not-allowed disabled:opacity-40" onclick="return confirm('Queue this campaign for {{ $recipientCount }} customers?')">Queue campaign</button>
                    </div>
                    <div class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5">
                        <h3 class="font-black text-emerald-900">Send a test first</h3><p class="mt-1 text-xs leading-5 text-emerald-700">Uses the current subject and message without creating a campaign.</p>
                        <input type="email" name="test_email" value="{{ old('test_email', auth()->user()->email) }}" class="mt-3 w-full rounded-xl border-emerald-200 bg-white text-sm">
                        <button type="submit" formaction="{{ route('email-campaigns.test') }}" formenctype="application/x-www-form-urlencoded" class="mt-2 w-full rounded-xl border border-emerald-300 bg-white px-4 py-2.5 text-sm font-black text-emerald-700">Send test email</button>
                    </div>
                    <a href="{{ route('email-campaigns.index') }}" class="block text-center text-sm font-bold text-slate-500">← Campaign history</a>
                </aside>
            </form>
        </div>
    </div>
</x-app-layout>
