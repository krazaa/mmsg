<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_settings.values'));
        static::deleted(fn () => Cache::forget('site_settings.values'));
    }

    public static function welcomeDefaults(): array
    {
        return [
            'welcome_header_background_color' => '#020617',
            'welcome_hero_body_color' => '#cbd5e1',
            'welcome_hero_heading_font_size' => '72',
            'welcome_hero_body_font_size' => '18',
            'welcome_hero_primary_button_background_color' => '#7c3aed',
            'welcome_hero_primary_button_text_color' => '#ffffff',
            'welcome_hero_primary_button_hover_color' => '#4f46e5',
            'welcome_hero_secondary_button_background_color' => '#ffffff',
            'welcome_hero_secondary_button_text_color' => '#020617',
            'welcome_hero_secondary_button_hover_color' => '#eef2ff',
            'welcome_hero_explore_button_background_color' => '#1e1b4b',
            'welcome_hero_explore_button_text_color' => '#ffffff',
            'welcome_hero_explore_button_hover_color' => '#312e81',
            'welcome_hero_blur_primary_color' => '#7c3aed',
            'welcome_hero_blur_secondary_color' => '#0ea5e9',
            'welcome_hero_badge_background_color' => '#064e3b',
            'welcome_hero_badge_text_color' => '#6ee7b7',
            'welcome_hero_badge_border_color' => '#34d399',
            'welcome_hero_image_border_color' => '#334155',
            'welcome_hero_image_gradient_start_color' => '#7c3aed',
            'welcome_hero_image_gradient_end_color' => '#0ea5e9',
            'welcome_projects_background_color' => '#020617',
            'welcome_projects_text_color' => '#ffffff',
            'welcome_projects_heading_color' => '#ffffff',
            'welcome_projects_heading_font_size' => '48',
            'welcome_projects_card_heading_color' => '#ffffff',
            'welcome_projects_card_heading_font_size' => '30',
            'welcome_projects_stat_background_color' => '#111827',
            'welcome_projects_stat_value_color' => '#ffffff',
            'welcome_projects_stat_label_color' => '#cbd5e1',
            'welcome_projects_stat_font_size' => '12',
            'welcome_projects_button_background_color' => '#312e81',
            'welcome_projects_button_text_color' => '#c7d2fe',
            'welcome_projects_button_font_size' => '12',
            'welcome_projects_blur_color' => '#6366f1',
            'welcome_projects_eyebrow_color' => '#a5b4fc',
            'welcome_projects_location_color' => '#a5b4fc',
            'welcome_projects_initials_background_color' => '#020617',
            'welcome_projects_initials_text_color' => '#ffffff',
            'welcome_projects_status_background_color' => '#020617',
            'welcome_projects_status_text_color' => '#a7f3d0',
            'welcome_projects_cta_background_color' => '#ffffff',
            'welcome_projects_cta_text_color' => '#020617',
            'welcome_projects_cta_hover_color' => '#eef2ff',
            'welcome_platform_background_color' => '#f8fafc',
            'welcome_platform_text_color' => '#0f172a',
            'welcome_journey_background_color' => '#ffffff',
            'welcome_journey_text_color' => '#0f172a',
            'welcome_cta_background_color' => '#4338ca',
            'welcome_cta_text_color' => '#ffffff',
            'welcome_footer_background_color' => '#020617',
            'welcome_footer_text_color' => '#94a3b8',
            'welcome_section_heading_font_size' => '36',
            'welcome_body_font_size' => '16',
        ];
    }

    public static function welcomeAppearance(): array
    {
        return collect(static::welcomeDefaults())
            ->mapWithKeys(fn ($default, $key) => [$key => static::valueFor($key, $default)])
            ->all();
    }

    public static function valueFor(string $key, mixed $default = null): mixed
    {
        $values = Cache::rememberForever(
            'site_settings.values',
            fn () => static::query()->pluck('value', 'key')->all(),
        );

        return $values[$key] ?? $default;
    }

    public static function showReferralCodesOnCustomerPortal(): bool
    {
        return filter_var(
            static::valueFor('customer_portal_show_referral_code', '1'),
            FILTER_VALIDATE_BOOL,
        );
    }

}
