<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_operational_metrics_and_inventory(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()
            ->assertSee('Business dashboard')->assertSee('Verified receipts')
            ->assertSee('Outstanding')->assertSee('Payable commission')
            ->assertSee('Project inventory')->assertSee('Abdullah Town');
    }
}
