<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="text-xs font-black uppercase tracking-[.18em] text-indigo-600 dark:text-indigo-300">Settings</div>
            <h2 class="mt-1 text-xl font-black text-gray-900 dark:text-white">Welcome page theme</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Customize the public welcome page.</p>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-violet-50 py-8 dark:from-slate-950 dark:via-slate-950 dark:to-indigo-950/30">
        <div class="mx-auto max-w-3xl space-y-5 px-4 sm:px-6">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('site-appearance.update') }}" class="overflow-hidden rounded-3xl border border-indigo-100 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-900">
                @csrf
                @method('PUT')
                <div class="space-y-5 p-6">
                    @php
                        $appearanceValues = array_merge($pageAppearance, [
                            'welcome_background_color' => $backgroundColor,
                            'welcome_hero_grid_background_color' => $heroGridBackgroundColor,
                            'welcome_hero_heading_color' => $heroHeadingColor,
                            'welcome_hero_stat_value_color' => $heroStatValueColor,
                            'welcome_hero_stat_label_color' => $heroStatLabelColor,
                        ]);
                        $fullPageFields = [
                            'Page & hero colors' => [
                                ['welcome_background_color', 'Main page background', 'color', ''],
                                ['welcome_hero_grid_background_color', 'Hero grid background', 'color', ''],
                                ['welcome_hero_heading_color', 'Hero heading color', 'color', ''],
                                ['welcome_hero_stat_value_color', 'Hero stat value color', 'color', ''],
                                ['welcome_hero_stat_label_color', 'Hero stat label color', 'color', ''],
                            ],
                            'Global typography' => [
                                ['welcome_section_heading_font_size', 'Section heading size', 'number', 'px'],
                                ['welcome_body_font_size', 'Body text size', 'number', 'px'],
                            ],
                            'Header & hero' => [
                                ['welcome_header_background_color', 'Header background', 'color', ''],
                                ['welcome_hero_body_color', 'Hero description color', 'color', ''],
                                ['welcome_hero_heading_font_size', 'Hero heading maximum size', 'number', 'px'],
                                ['welcome_hero_body_font_size', 'Hero description size', 'number', 'px'],
                                ['welcome_hero_primary_button_background_color', 'Primary button background', 'color', ''],
                                ['welcome_hero_primary_button_text_color', 'Primary button text', 'color', ''],
                                ['welcome_hero_primary_button_hover_color', 'Primary button hover', 'color', ''],
                                ['welcome_hero_secondary_button_background_color', 'Register button background', 'color', ''],
                                ['welcome_hero_secondary_button_text_color', 'Register button text', 'color', ''],
                                ['welcome_hero_secondary_button_hover_color', 'Register button hover', 'color', ''],
                                ['welcome_hero_explore_button_background_color', 'Explore button background', 'color', ''],
                                ['welcome_hero_explore_button_text_color', 'Explore button text', 'color', ''],
                                ['welcome_hero_explore_button_hover_color', 'Explore button hover', 'color', ''],
                                ['welcome_hero_blur_primary_color', 'Gradient color 1', 'color', ''],
                                ['welcome_hero_blur_secondary_color', 'Gradient color 2', 'color', ''],
                                ['welcome_hero_badge_background_color', 'Secure badge background', 'color', ''],
                                ['welcome_hero_badge_text_color', 'Secure badge text', 'color', ''],
                                ['welcome_hero_badge_border_color', 'Secure badge border', 'color', ''],
                                ['welcome_hero_image_border_color', 'Hero image border color', 'color', ''],
                                ['welcome_hero_image_gradient_start_color', 'Image-border gradient start', 'color', ''],
                                ['welcome_hero_image_gradient_end_color', 'Image-border gradient end', 'color', ''],
                            ],
                            'Projects section' => [
                                ['welcome_projects_background_color', 'Background', 'color', ''],
                                ['welcome_projects_text_color', 'Text color', 'color', ''],
                                ['welcome_projects_heading_color', 'H2 heading color', 'color', ''],
                                ['welcome_projects_heading_font_size', 'H2 heading size', 'number', 'px'],
                                ['welcome_projects_card_heading_color', 'Project name color', 'color', ''],
                                ['welcome_projects_card_heading_font_size', 'H3 card-title size', 'number', 'px'],
                                ['welcome_projects_stat_background_color', 'Info tag background', 'color', ''],
                                ['welcome_projects_stat_value_color', 'Info tag number color', 'color', ''],
                                ['welcome_projects_stat_label_color', 'Info tag text color', 'color', ''],
                                ['welcome_projects_stat_font_size', 'Land-stat font size', 'number', 'px'],
                                ['welcome_projects_button_background_color', 'Masterplan background', 'color', ''],
                                ['welcome_projects_button_text_color', 'Masterplan text color', 'color', ''],
                                ['welcome_projects_button_font_size', 'Button font size', 'number', 'px'],
                                ['welcome_projects_blur_color', 'Section blur/glow color', 'color', ''],
                                ['welcome_projects_eyebrow_color', '"Our developments" color', 'color', ''],
                                ['welcome_projects_location_color', 'Project location color', 'color', ''],
                                ['welcome_projects_initials_background_color', 'Initials badge background', 'color', ''],
                                ['welcome_projects_initials_text_color', 'Initials badge text', 'color', ''],
                                ['welcome_projects_status_background_color', '"Open for interest" background', 'color', ''],
                                ['welcome_projects_status_text_color', '"Open for interest" text', 'color', ''],
                                ['welcome_projects_cta_background_color', 'Button background color', 'color', ''],
                                ['welcome_projects_cta_text_color', 'Button text color', 'color', ''],
                                ['welcome_projects_cta_hover_color', 'Button hover color', 'color', ''],
                            ],
                            'Platform section' => [
                                ['welcome_platform_background_color', 'Background', 'color', ''],
                                ['welcome_platform_text_color', 'Text color', 'color', ''],
                            ],
                            'Journey section' => [
                                ['welcome_journey_background_color', 'Background', 'color', ''],
                                ['welcome_journey_text_color', 'Text color', 'color', ''],
                            ],
                            'Call to action' => [
                                ['welcome_cta_background_color', 'Background', 'color', ''],
                                ['welcome_cta_text_color', 'Text color', 'color', ''],
                            ],
                            'Footer' => [
                                ['welcome_footer_background_color', 'Background', 'color', ''],
                                ['welcome_footer_text_color', 'Text color', 'color', ''],
                            ],
                        ];
                        $fieldLookup = collect($fullPageFields)->flatten(1)->keyBy(fn ($field) => $field[0]);
                        $simpleGroupKeys = [
                            'Top navigation' => [
                                'welcome_header_background_color',
                            ],
                            'Hero area' => [
                                'welcome_hero_grid_background_color',
                                'welcome_hero_heading_color',
                                'welcome_hero_body_color',
                                'welcome_hero_primary_button_background_color',
                                'welcome_hero_blur_primary_color',
                                'welcome_hero_blur_secondary_color',
                                'welcome_hero_heading_font_size',
                                'welcome_hero_body_font_size',
                            ],
                            'Projects area' => [
                                'welcome_projects_background_color',
                                'welcome_projects_eyebrow_color',
                                'welcome_projects_heading_color',
                                'welcome_projects_card_heading_color',
                                'welcome_projects_location_color',
                                'welcome_projects_stat_background_color',
                                'welcome_projects_stat_value_color',
                                'welcome_projects_stat_label_color',
                                'welcome_projects_button_background_color',
                                'welcome_projects_button_text_color',
                                'welcome_projects_cta_background_color',
                                'welcome_projects_cta_text_color',
                                'welcome_projects_cta_hover_color',
                                'welcome_projects_heading_font_size',
                            ],
                            'Information sections' => [
                                'welcome_platform_background_color',
                                'welcome_platform_text_color',
                                'welcome_journey_background_color',
                                'welcome_journey_text_color',
                            ],
                            'Final call to action' => [
                                'welcome_cta_background_color',
                                'welcome_cta_text_color',
                            ],
                            'Footer' => [
                                'welcome_footer_background_color',
                                'welcome_footer_text_color',
                            ],
                            'General text sizes' => [
                                'welcome_section_heading_font_size',
                                'welcome_body_font_size',
                            ],
                        ];
                        $simpleGroups = collect($simpleGroupKeys)->map(
                            fn ($keys) => collect($keys)->map(fn ($key) => $fieldLookup[$key])->all()
                        )->all();
                        $visibleKeys = collect($simpleGroupKeys)->flatten()->all();
                        $hiddenKeys = array_values(array_diff(array_keys($appearanceValues), $visibleKeys));
                    @endphp

                    <div class="space-y-4">
                        <div><h3 class="text-lg font-black text-slate-950 dark:text-white">Customize by page section</h3><p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Open a section, change what you need, then save.</p></div>
                        @foreach($simpleGroups as $group => $fields)
                            <details class="group/section rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900" @if($loop->first) open @endif>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4">
                                    <span>
                                        <b class="block text-sm text-slate-900 dark:text-white">{{ $group }}</b>
                                        <span class="mt-0.5 block text-[11px] text-slate-400">{{ count($fields) === 1 ? '1 simple setting' : count($fields).' simple settings' }}</span>
                                    </span>
                                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-50 text-indigo-600 transition group-open/section:rotate-180 dark:bg-slate-800 dark:text-indigo-300">⌄</span>
                                </summary>
                                <div class="grid gap-4 border-t border-slate-100 p-5 dark:border-slate-800 sm:grid-cols-2">
                                    @foreach($fields as [$key, $label, $type, $suffix])
                                        <label class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ $label }}
                                            @if($type === 'color')
                                                <div class="mt-2 flex items-center gap-3" x-data="{ fieldColor: @js(old($key, $appearanceValues[$key])) }">
                                                    <input type="color" name="{{ $key }}" x-model="fieldColor" value="{{ old($key, $appearanceValues[$key]) }}" class="h-11 w-14 cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                                                    <input type="text" x-model="fieldColor" maxlength="7" pattern="#[0-9A-Fa-f]{6}" class="min-w-0 w-32 rounded-xl border-slate-300 font-mono uppercase dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                                </div>
                                            @else
                                                <div class="relative mt-2">
                                                    <input type="number" name="{{ $key }}" value="{{ old($key, $appearanceValues[$key]) }}" min="10" max="120" class="w-full rounded-xl border-slate-300 pe-10 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                                    <span class="absolute end-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">{{ $suffix }}</span>
                                                </div>
                                            @endif
                                            <x-input-error :messages="$errors->get($key)" class="mt-2" />
                                        </label>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                        @foreach($hiddenKeys as $key)
                            <input type="hidden" name="{{ $key }}" value="{{ old($key, $appearanceValues[$key]) }}">
                        @endforeach
                    </div>
                    <section class="rounded-2xl border border-slate-200 p-5 dark:border-slate-700">
                        <h3 class="font-black text-slate-950 dark:text-white">Footer social media</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Add a full profile URL. Leave a field blank to hide that link.</p>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach([
                                'welcome_social_facebook_url' => 'Facebook',
                                'welcome_social_instagram_url' => 'Instagram',
                                'welcome_social_youtube_url' => 'YouTube',
                                'welcome_social_linkedin_url' => 'LinkedIn',
                                'welcome_social_x_url' => 'X',
                            ] as $key => $label)
                                <label class="block text-xs font-black uppercase tracking-wide text-slate-500">{{ $label }}
                                    <input type="url" name="{{ $key }}" value="{{ old($key, $socialLinks[$key]) }}" placeholder="https://" class="mt-1.5 w-full rounded-xl border-slate-300 py-2.5 text-sm normal-case dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                                    <x-input-error :messages="$errors->get($key)" class="mt-2" />
                                </label>
                            @endforeach
                        </div>
                    </section>
                    <div class="flex flex-wrap gap-3 border-t border-slate-100 pt-5 dark:border-slate-800">
                        <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-indigo-200 hover:bg-indigo-700 dark:shadow-none">Save changes</button>
                        <a href="{{ route('home') }}" target="_blank" class="rounded-xl border border-indigo-200 bg-indigo-50 px-5 py-3 text-sm font-bold text-indigo-700">Preview welcome page ↗</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
