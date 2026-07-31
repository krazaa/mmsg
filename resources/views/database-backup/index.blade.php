<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-black uppercase tracking-[.2em] text-indigo-500">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_0_5px_rgba(52,211,153,.12)]"></span>
                    System protection
                </div>
                <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-950 dark:text-white">Database backups</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">Create, download, and restore complete MySQL snapshots from one secure workspace.</p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-wider text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-300">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Native SQL enabled
            </span>
        </div>
    </x-slot>

    @php
        $latest = $files->first();
        $totalSize = $files->sum('size');
    @endphp

    <div class="px-4 py-6 sm:px-6">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-400">Saved backups</p>
                <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $files->count() }}</p>
                <p class="mt-1 text-xs text-slate-500">Available on this server</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-400">Latest backup</p>
                <p class="mt-2 truncate text-lg font-black text-slate-950 dark:text-white">{{ $latest ? \Illuminate\Support\Carbon::createFromTimestamp($latest['created_at'])->diffForHumans() : 'Not created' }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $latest ? \Illuminate\Support\Carbon::createFromTimestamp($latest['created_at'])->format('M j, Y · g:i A') : 'Create your first snapshot' }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-slate-400">Storage used</p>
                <p class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ Number::fileSize($totalSize) }}</p>
                <p class="mt-1 text-xs text-slate-500">Private backup storage</p>
            </div>
        </section>

        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950 via-indigo-900 to-violet-900 p-6 text-white shadow-xl shadow-indigo-200/50 dark:shadow-none sm:p-7">
            <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-violet-400/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-28 left-1/3 h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex max-w-3xl items-start gap-4">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-white/15 bg-white/10 text-lg shadow-inner">⇩</span>
                    <div>
                        <h2 class="text-xl font-black tracking-tight sm:text-2xl">Create a fresh database snapshot</h2>
                        <p class="mt-1.5 text-sm leading-6 text-indigo-100/70">The compressed MySQL dump includes schema, records, indexes, triggers, routines, and events. It is stored privately and can be downloaded at any time.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('database-backup.store') }}" class="shrink-0">
                    @csrf
                    <button class="group inline-flex w-full items-center justify-center gap-3 rounded-xl bg-white px-6 py-3.5 text-sm font-black text-indigo-950 shadow-xl shadow-black/20 transition hover:-translate-y-0.5 hover:bg-indigo-50 lg:w-auto">
                        <span class="text-lg transition group-hover:rotate-12">＋</span>
                        Make backup now
                    </button>
                </form>
            </div>
            <x-input-error :messages="$errors->get('backup_create')" class="relative mt-5 rounded-xl bg-rose-500/15 px-4 py-3 text-rose-100" />
        </section>

        <div class="grid items-stretch gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(360px,1fr)]">
            <section class="flex h-full min-h-[34rem] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40 dark:border-slate-700 dark:bg-slate-900 dark:shadow-none">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5 dark:border-slate-700 sm:px-7">
                    <div>
                        <h2 class="text-lg font-black text-slate-950 dark:text-white">Saved backup files</h2>
                        <p class="mt-1 text-xs text-slate-500">Newest snapshots appear first</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $files->count() }}</span>
                </div>

                @forelse($files as $file)
                    <div class="group border-b border-slate-100 px-5 py-5 last:border-0 hover:bg-slate-50/80 dark:border-slate-800 dark:hover:bg-slate-800/40 sm:px-7">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-indigo-50 text-xs font-black text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300">SQL</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-black text-slate-800 dark:text-white">{{ $file['name'] }}</p>
                                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                    <span>{{ \Illuminate\Support\Carbon::createFromTimestamp($file['created_at'])->format('M j, Y · g:i A') }}</span>
                                    <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:block"></span>
                                    <span>{{ Number::fileSize($file['size']) }}</span>
                                    @if($loop->first)<span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Latest</span>@endif
                                </div>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <a href="{{ route('database-backup.download', $file['name']) }}" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-50 px-4 py-2.5 text-xs font-black text-indigo-700 transition hover:bg-indigo-100 dark:bg-indigo-950 dark:text-indigo-300 sm:flex-none">↓ Download</a>
                                <form method="POST" action="{{ route('database-backup.destroy', $file['name']) }}" onsubmit="return confirm('Permanently delete this saved backup file?');">
                                    @csrf
                                    @method('DELETE')
                                    <button aria-label="Delete {{ $file['name'] }}" title="Delete backup" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950">✕</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="grid flex-1 place-content-center px-6 py-16 text-center">
                        <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 text-2xl text-slate-400 dark:bg-slate-800">□</span>
                        <h3 class="mt-4 text-sm font-black text-slate-700 dark:text-slate-200">No backups yet</h3>
                        <p class="mx-auto mt-1 max-w-xs text-xs leading-5 text-slate-500">Create your first snapshot using the button above. It will appear here when ready.</p>
                    </div>
                @endforelse
            </section>

            <section x-data="{ acknowledged: false }" class="h-full min-h-[34rem] overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-xl shadow-rose-100/50 dark:border-rose-950 dark:bg-slate-900 dark:shadow-none">
                <div class="border-b border-rose-100 bg-rose-50/70 px-6 py-5 dark:border-rose-950 dark:bg-rose-950/30">
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-xl bg-rose-100 font-black text-rose-700 dark:bg-rose-950 dark:text-rose-300">↺</span>
                        <div><h2 class="text-lg font-black text-slate-950 dark:text-white">Restore database</h2><p class="text-xs text-rose-600 dark:text-rose-300">Destructive action</p></div>
                    </div>
                </div>
                <form method="POST" action="{{ route('database-backup.restore') }}" enctype="multipart/form-data" class="space-y-5 p-6" onsubmit="return confirm('Restore this dump and replace the current database?');">
                    @csrf
                    <p class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">Restoring replaces the current database. Download the latest backup before continuing.</p>
                    <label class="block text-[10px] font-black uppercase tracking-[.16em] text-slate-500">SQL dump file
                        <input type="file" name="backup" accept=".sql,.sql.gz,application/sql,application/gzip" required class="mt-2 block w-full rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3 text-xs font-normal normal-case tracking-normal text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-100 file:px-3 file:py-2 file:text-xs file:font-black file:text-indigo-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        <x-input-error :messages="$errors->get('backup')" class="mt-2" />
                    </label>
                    <label class="block text-[10px] font-black uppercase tracking-[.16em] text-slate-500">Current password
                        <input type="password" name="current_password" required autocomplete="current-password" placeholder="Confirm your identity" class="mt-2 block w-full rounded-xl border-slate-300 text-sm font-normal normal-case tracking-normal dark:border-slate-600 dark:bg-slate-800">
                        <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 text-xs leading-5 text-slate-600 dark:border-slate-700 dark:text-slate-300">
                        <input x-model="acknowledged" type="checkbox" name="confirm_restore" value="1" required class="mt-0.5 rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                        <span>I understand this will replace all current database records.</span>
                    </label>
                    <x-input-error :messages="$errors->get('confirm_restore')" />
                    <button :class="acknowledged ? 'bg-rose-600 hover:bg-rose-700' : 'cursor-not-allowed bg-slate-300 dark:bg-slate-700'" :disabled="!acknowledged" class="w-full rounded-xl px-5 py-3 text-sm font-black text-white transition">Restore database</button>
                </form>
            </section>
        </div>
    </div>
    </div>
</x-app-layout>
