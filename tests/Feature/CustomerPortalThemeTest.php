<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_updates_the_shared_customer_portal_theme(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $theme = [
            'customer_theme_page_background' => '#f8fafc',
            'customer_theme_nav_background' => '#020617',
            'customer_theme_nav_text' => '#cbd5e1',
            'customer_theme_primary' => '#2563eb',
            'customer_theme_accent' => '#9333ea',
            'customer_theme_hero_start' => '#020617',
            'customer_theme_hero_middle' => '#172554',
            'customer_theme_hero_end' => '#312e81',
            'customer_theme_hero_text' => '#ffffff',
            'customer_theme_blur_color' => '#ec4899',
            'customer_theme_active_badge_background' => '#14532d',
            'customer_theme_active_badge_text' => '#bbf7d0',
            'customer_theme_card_background' => '#ffffff',
            'customer_theme_surface_background' => '#f1f5f9',
            'customer_theme_text' => '#0f172a',
            'customer_theme_muted_text' => '#64748b',
            'customer_theme_border' => '#cbd5e1',
            'customer_theme_button_text' => '#ffffff',
        ];

        $this->actingAs($admin)->get(route('customer-portal-theme.edit'))
            ->assertOk()
            ->assertSee('Customer portal theme')
            ->assertSee('indigo cards');

        $this->actingAs($admin)->put(route('customer-portal-theme.update'), $theme)
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame($theme, SiteSetting::customerPortalTheme());

        $customer = User::factory()->create(['role' => 'customer', 'email_verified_at' => now()]);
        $this->actingAs($customer)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('--customer-primary: #2563eb', false)
            ->assertSee('--customer-surface-base: #f1f5f9', false)
            ->assertSee('--customer-surface: var(--customer-surface-base)', false)
            ->assertSee('customer-portal-shell', false)
            ->assertSee('customer-portal-nav', false);
    }
}
