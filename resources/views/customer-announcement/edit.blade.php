<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-xs font-black uppercase tracking-[.18em] text-orange-600 dark:text-orange-300">Customer portal</div>
            <h2 class="mt-1 text-xl font-black text-gray-900 dark:text-white">Customer announcement popup</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Publish an announcement customers will see when they open the portal.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-amber-50 py-8 dark:from-slate-950 dark:via-slate-950 dark:to-orange-950/20">
        <div class="mx-auto max-w-3xl space-y-5 px-4 sm:px-6">
            @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-800">{{ $errors->first() }}</div>@endif

            <form method="POST" action="{{ route('customer-announcement.update') }}" x-data="{ enabled: @js((bool) old('enabled', $announcement['enabled'])) }" class="overflow-hidden rounded-3xl border border-orange-200 bg-white shadow-xl dark:border-orange-900 dark:bg-slate-900">
                @csrf @method('PUT')
                <div class="flex flex-col gap-4 border-b border-orange-100 bg-orange-50/70 p-6 dark:border-orange-900/60 dark:bg-orange-950/20 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-orange-100 text-xl">🔔</span><div><h3 class="font-black text-slate-950 dark:text-white">Portal announcement</h3><p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Customers will see the popup again whenever you save a new version.</p></div></div>
                    <label class="inline-flex cursor-pointer items-center gap-3 rounded-xl border border-orange-200 bg-white px-4 py-3 text-xs font-black dark:border-slate-700 dark:bg-slate-800 dark:text-white"><input type="hidden" name="enabled" value="0"><input type="checkbox" name="enabled" value="1" x-model="enabled" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500"> Show announcement</label>
                </div>
                <div class="grid gap-5 p-6">
                    <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Popup title<input type="text" name="title" value="{{ old('title', $announcement['title']) }}" maxlength="100" :required="enabled" class="mt-1.5 w-full rounded-xl border-slate-300 py-3 text-sm font-normal normal-case dark:border-slate-600 dark:bg-slate-800 dark:text-white"></label>
                    <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Announcement message<textarea name="message" maxlength="1000" rows="6" :required="enabled" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm font-normal normal-case leading-6 dark:border-slate-600 dark:bg-slate-800 dark:text-white">{{ old('message', $announcement['message']) }}</textarea><span class="mt-1.5 block text-[10px] font-normal normal-case text-slate-400">Maximum 1,000 characters.</span></label>
                </div>
                <div class="flex flex-wrap gap-3 border-t border-slate-100 p-6 dark:border-slate-800"><button class="rounded-xl bg-orange-500 px-6 py-3 text-sm font-black text-white hover:bg-orange-600">Save announcement</button><a href="{{ route('dashboard') }}" class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600 dark:border-slate-700 dark:text-slate-300">Back to dashboard</a></div>
            </form>
        </div>
    </div>
</x-app-layout>
