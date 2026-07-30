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
            'welcome_projects_card_heading_color' => '#020617',
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

    public static function customerPortalThemeDefaults(): array
    {
        return [
            'customer_theme_page_background' => '#f1f5f9',
            'customer_theme_nav_background' => '#ffffff',
            'customer_theme_nav_text' => '#475569',
            'customer_theme_primary' => '#4f46e5',
            'customer_theme_accent' => '#7c3aed',
            'customer_theme_hero_start' => '#020617',
            'customer_theme_hero_middle' => '#312e81',
            'customer_theme_hero_end' => '#6d28d9',
            'customer_theme_hero_text' => '#ffffff',
            'customer_theme_blur_color' => '#d946ef',
            'customer_theme_active_badge_background' => '#064e3b',
            'customer_theme_active_badge_text' => '#a7f3d0',
            'customer_theme_card_background' => '#ffffff',
            'customer_theme_surface_background' => '#f8fafc',
            'customer_theme_text' => '#0f172a',
            'customer_theme_muted_text' => '#64748b',
            'customer_theme_border' => '#e2e8f0',
            'customer_theme_button_text' => '#ffffff',
        ];
    }

    public static function customerPortalTheme(): array
    {
        return collect(static::customerPortalThemeDefaults())
            ->mapWithKeys(fn (string $default, string $key) => [$key => static::valueFor($key, $default)])
            ->all();
    }

    public static function adminCardAppearanceDefaults(): array
    {
        return [
            'admin_page_background' => '#f1f5f9',
            'admin_page_background_mode' => 'solid',
            'admin_page_gradient_start' => '#f8fafc',
            'admin_page_gradient_end' => '#e2e8f0',
            'admin_card_background' => '#ffffff',
            'admin_card_background_mode' => 'solid',
            'admin_card_gradient_start' => '#ffffff',
            'admin_card_pattern' => '#dbe3f1',
            'admin_card_accent_start' => '#000000',
            'admin_card_accent_end' => '#000000',
            'admin_card_badge_background' => '#4b5563',
            'admin_card_badge_text' => '#ffffff',
            'admin_card_action_background' => '#000000',
            'admin_card_action_text' => '#ffffff',
        ];
    }

    public static function adminCardAppearance(): array
    {
        $appearance = collect(static::adminCardAppearanceDefaults())
            ->mapWithKeys(fn (string $default, string $key) => [$key => static::valueFor($key, $default)])
            ->all();

        if (static::valueFor('admin_card_background_mode') === null
            && filter_var(static::valueFor('admin_card_background_transparent', '0'), FILTER_VALIDATE_BOOL)) {
            $appearance['admin_card_background_mode'] = 'transparent';
        }

        return $appearance;
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

    public static function ownerWhatsAppNumbers(): array
    {
        return collect(preg_split('/[\s,;]+/', (string) static::valueFor('whatsapp_owner_numbers', '')))
            ->map(fn (?string $number) => trim((string) $number))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function maintenanceModeEnabled(): bool
    {
        return filter_var(
            static::valueFor('maintenance_mode_enabled', '0'),
            FILTER_VALIDATE_BOOL,
        );
    }

    public static function maintenancePage(): array
    {
        return [
            'title' => (string) static::valueFor('maintenance_page_title', 'We’ll be back shortly.'),
            'message' => (string) static::valueFor(
                'maintenance_page_message',
                'We are completing scheduled improvements. Please check back soon.',
            ),
        ];
    }
}
