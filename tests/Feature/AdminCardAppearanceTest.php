<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCardAppearanceTest extends TestCase
{
    use RefreshDatabase;

    private function appSettingsPayload(array $appearance = []): array
    {
        return [
            'fee_enabled' => 0,
            'fee_type' => 'fixed',
            'fee_value' => 0,
            'pin_recovery_enabled' => 1,
            'customer_portal_show_referral_code' => 1,
            'maintenance_mode_enabled' => 0,
            'maintenance_page_title' => 'We will be back shortly.',
            'maintenance_page_message' => 'Scheduled improvements are underway.',
            ...$appearance,
        ];
    }

    public function test_super_admin_can_update_shared_admin_card_appearance(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'email_verified_at' => now()]);
        $appearance = [
            'admin_page_background' => '#f1f5f9',
            'admin_page_background_mode' => 'gradient',
            'admin_page_gradient_start' => '#f8fafc',
            'admin_page_gradient_end' => '#e2e8f0',
            'admin_card_background' => '#fafafa',
            'admin_card_background_mode' => 'gradient',
            'admin_card_gradient_start' => '#ffffff',
            'admin_card_pattern' => '#e5e7eb',
            'admin_card_accent_start' => '#111827',
            'admin_card_accent_end' => '#374151',
            'admin_card_badge_background' => '#4b5563',
            'admin_card_badge_text' => '#ffffff',
            'admin_card_action_background' => '#000000',
            'admin_card_action_text' => '#ffffff',
        ];

        $this->actingAs($superAdmin)
            ->get(route('app-settings.edit'))
            ->assertOk()
            ->assertSee('Admin card appearance');

        $this->actingAs($superAdmin)
            ->put(route('app-settings.update'), $this->appSettingsPayload($appearance))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame($appearance, SiteSetting::adminCardAppearance());
        $this->actingAs($superAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('--admin-page-background: linear-gradient(135deg, #f8fafc, #e2e8f0)', false)
            ->assertSee('--admin-card-background: #fafafa', false)
            ->assertSee('--admin-command-card-background: linear-gradient(135deg, #ffffff, #fafafa)', false)
            ->assertSee('--admin-card-action-background: #000000', false);
    }

    public function test_regular_admin_cannot_view_or_change_admin_card_appearance(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->actingAs($admin)
            ->get(route('app-settings.edit'))
            ->assertOk()
            ->assertDontSee('Admin card appearance');

        $this->actingAs($admin)
            ->put(route('app-settings.update'), $this->appSettingsPayload([
                'admin_card_background' => '#000000',
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(
            SiteSetting::adminCardAppearanceDefaults(),
            SiteSetting::adminCardAppearance(),
        );
    }
}
