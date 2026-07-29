<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_mode_blocks_public_and_customer_access_but_keeps_admin_access(): void
    {
        $this->seed();

        foreach ([
            'maintenance_mode_enabled' => '1',
            'maintenance_page_title' => 'Platform upgrade in progress',
            'maintenance_page_message' => 'We are preparing a faster customer experience.',
        ] as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->get('/')
            ->assertStatus(503)
            ->assertSee('Platform upgrade in progress')
            ->assertSee('We are preparing a faster customer experience.')
            ->assertDontSee('Admin login');

        $this->get('/login')
            ->assertStatus(503)
            ->assertDontSee('Email address');
        $this->get('/forgot-password')->assertStatus(503);

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get('/')->assertStatus(503);
        auth()->logout();

        $this->get(route('management.login'))
            ->assertOk()
            ->assertSee('Management sign in');
        $this->post(route('management.login.store'), [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $this->post(route('management.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));
        $this->get(route('app-settings.edit'))
            ->assertOk()
            ->assertSee('Maintenance mode');
        $this->get(route('app-settings.maintenance-preview'))
            ->assertOk()
            ->assertSee('Maintenance page preview');
    }
}
