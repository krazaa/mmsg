<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-xs font-black uppercase tracking-[.18em] text-indigo-600 dark:text-indigo-300">Settings</div>
            <h2 class="mt-1 text-xl font-black text-gray-900 dark:text-white">Withdrawal settings</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Control customer withdrawal frequency and amount limits.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-emerald-50 py-8 dark:from-slate-950 dark:via-slate-950 dark:to-emerald-950/30">
        <div class="mx-auto max-w-4xl space-y-5 px-4 sm:px-6">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-800">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('withdrawal-settings.update') }}" class="overflow-hidden rounded-3xl border border-indigo-100 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                @csrf
                @method('PUT')
                <div class="border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-white p-6 dark:border-slate-800 dark:from-indigo-950/30 dark:to-slate-900">
                    <h3 class="font-black text-slate-950 dark:text-white">Customer withdrawal policy</h3>
                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-300">These limits are enforced when customers submit requests from their commission wallet.</p>
                </div>
                <div class="space-y-5 p-6">
                    <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-900 dark:bg-indigo-950/30">
                        <label class="block text-xs font-black uppercase tracking-wide text-indigo-800 dark:text-indigo-200">
                            Frequency for all customers
                            <select name="frequency" required class="mt-2 w-full rounded-xl border-indigo-200 bg-white py-3 text-sm font-bold normal-case text-slate-900 dark:border-indigo-800 dark:bg-slate-900 dark:text-white">
                                @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('frequency', $settings['frequency']) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <p class="mt-2 text-[11px] leading-5 text-indigo-700 dark:text-indigo-300">Weekly is the default. Saving a different policy immediately changes the withdrawal frequency for every customer.</p>
                    </div>
                    <div class="grid gap-4 lg:grid-cols-3">
                        @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $frequency => $label)
                            @php($policy = $settings['policies'][$frequency])
                            <section class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                                <div class="mb-4 flex items-center justify-between"><h4 class="font-black text-slate-950 dark:text-white">{{ $label }} limits</h4><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-black uppercase text-indigo-600 dark:bg-indigo-950 dark:text-indigo-300">{{ $frequency }}</span></div>
                                <div class="space-y-4">
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Requests allowed<input type="number" name="policies[{{ $frequency }}][request_limit]" value="{{ old("policies.$frequency.request_limit", $policy['request_limit']) }}" min="1" max="100" required class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm normal-case dark:border-slate-600 dark:bg-slate-800 dark:text-white"></label>
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Minimum amount<input type="number" name="policies[{{ $frequency }}][minimum_amount]" value="{{ old("policies.$frequency.minimum_amount", $policy['minimum_amount']) }}" min="1" step="0.01" required class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm normal-case dark:border-slate-600 dark:bg-slate-800 dark:text-white"></label>
                                    <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Maximum amount<input type="number" name="policies[{{ $frequency }}][maximum_amount]" value="{{ old("policies.$frequency.maximum_amount", $policy['maximum_amount']) }}" min="0" step="0.01" required class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm normal-case dark:border-slate-600 dark:bg-slate-800 dark:text-white"><span class="mt-1 block text-[10px] font-normal normal-case text-slate-400">Use 0 for unlimited.</span></label>
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 border-t border-slate-100 p-6 dark:border-slate-800">
                    <button class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-black text-white hover:bg-indigo-700">Save withdrawal settings</button>
                    <a href="{{ route('withdrawal-requests.index') }}" class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600 dark:border-slate-700 dark:text-slate-300">View withdrawal requests</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
