<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteAppearanceController extends Controller
{
    public function edit(): View
    {
        return view('site-appearance.edit', [
            'backgroundColor' => SiteSetting::valueFor('welcome_background_color', '#020617'),
            'heroGridBackgroundColor' => SiteSetting::valueFor('welcome_hero_grid_background_color', '#020617'),
            'heroHeadingColor' => SiteSetting::valueFor('welcome_hero_heading_color', '#ffffff'),
            'heroStatValueColor' => SiteSetting::valueFor('welcome_hero_stat_value_color', '#ffffff'),
            'heroStatLabelColor' => SiteSetting::valueFor('welcome_hero_stat_label_color', '#94a3b8'),
            'pageAppearance' => SiteSetting::welcomeAppearance(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'welcome_background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'welcome_hero_grid_background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'welcome_hero_heading_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'welcome_hero_stat_value_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'welcome_hero_stat_label_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            ...collect(SiteSetting::welcomeDefaults())->mapWithKeys(fn ($default, $key) => [
                $key => str_ends_with($key, '_font_size')
                    ? ['required', 'integer', 'min:10', 'max:120']
                    : ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            ])->all(),
        ]);

        foreach ([
            'welcome_background_color',
            'welcome_hero_grid_background_color',
            'welcome_hero_heading_color',
            'welcome_hero_stat_value_color',
            'welcome_hero_stat_label_color',
        ] as $key) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => strtolower($data[$key])],
            );
        }

        foreach (array_keys(SiteSetting::welcomeDefaults()) as $key) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['value' => str_ends_with($key, '_font_size') ? (string) $data[$key] : strtolower($data[$key])],
            );
        }

        return back()->with('success', 'Welcome page background color updated.');
    }
}
