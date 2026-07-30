<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-xs font-black uppercase tracking-[.18em] text-blue-600 dark:text-blue-300">Settings</div>
            <h2 class="mt-1 text-xl font-black text-slate-950 dark:text-white">Customer portal theme</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">One shared visual theme for every customer account.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-8 dark:from-slate-950 dark:via-slate-950 dark:to-indigo-950/30">
        <div class="mx-auto max-w-4xl px-4 sm:px-6">
            @if(session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>@endif
            <form method="POST" action="{{ route('customer-portal-theme.update') }}" class="overflow-hidden rounded-3xl border border-blue-100 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">@csrf @method('PUT')
                <div class="border-b border-slate-100 p-6 dark:border-slate-800">
                    <h3 class="font-black text-slate-950 dark:text-white">Portal colors</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Changes apply to all customers immediately after saving.</p>
                </div>
                <div class="grid gap-5 p-6 sm:grid-cols-2">
                    @foreach([
                        'customer_theme_page_background' => 'Page background',
                        'customer_theme_nav_background' => 'Top navigation background',
                        'customer_theme_nav_text' => 'Navigation text',
                        'customer_theme_primary' => 'Primary · indigo cards & buttons',
                        'customer_theme_accent' => 'Accent · gradient end color',
                        'customer_theme_hero_start' => 'Dashboard card · left color',
                        'customer_theme_hero_middle' => 'Dashboard card · center color',
                        'customer_theme_hero_end' => 'Purple gradient · right side',
                        'customer_theme_hero_text' => 'Dashboard & booking header text',
                        'customer_theme_blur_color' => 'Background blur & glow color',
                        'customer_theme_active_badge_background' => 'Active badge background',
                        'customer_theme_active_badge_text' => 'Active badge text & dot',
                        'customer_theme_card_background' => 'Card background',
                        'customer_theme_surface_background' => 'Inputs & soft sections',
                        'customer_theme_text' => 'Main text',
                        'customer_theme_muted_text' => 'Secondary text',
                        'customer_theme_border' => 'Borders',
                        'customer_theme_button_text' => 'Button text',
                    ] as $key => $label)
                        <label class="block text-xs font-black uppercase tracking-wide text-slate-500">{{ $label }}
                            <span class="mt-1.5 flex items-center gap-3 rounded-xl border border-slate-200 p-2 dark:border-slate-700">
                                <input type="color" name="{{ $key }}" value="{{ old($key, $theme[$key]) }}" class="h-10 w-12 cursor-pointer rounded-lg border-0 bg-transparent p-0">
                                <span class="font-mono text-xs normal-case text-slate-600 dark:text-slate-300">{{ old($key, $theme[$key]) }}</span>
                            </span>
                            <x-input-error :messages="$errors->get($key)" class="mt-2" />
                        </label>
                    @endforeach
                </div>
                <div class="flex flex-wrap gap-3 border-t border-slate-100 p-6 dark:border-slate-800">
                    <button class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white hover:bg-blue-700">Save customer theme</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
