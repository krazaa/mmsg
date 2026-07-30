<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerPortalThemeController extends Controller
{
    public function edit(): View
    {
        return view('customer-portal-theme.edit', [
            'theme' => SiteSetting::customerPortalTheme(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(
            collect(SiteSetting::customerPortalThemeDefaults())
                ->mapWithKeys(fn (string $default, string $key) => [
                    $key => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                ])->all()
        );

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => strtolower($value)]);
        }

        return back()->with('success', 'Customer portal theme updated for all customers.');
    }
}
