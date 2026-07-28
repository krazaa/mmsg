<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Support\DataVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealtimeDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_clients_can_detect_model_changes_without_websockets(): void
    {
        $user = User::factory()->create();
        $before = DataVersion::current();

        Project::create([
            'name' => 'Live Project',
            'slug' => 'live-project',
            'location' => 'Live Location',
            'gross_area_marla' => 100,
            'saleable_area_marla' => 80,
            'status' => true,
        ]);

        $this->actingAs($user)->getJson(route('data-version'))
            ->assertOk()
            ->assertJsonPath('version', fn (int $version): bool => $version > $before);
    }

    public function test_data_version_endpoint_requires_authentication(): void
    {
        $this->get(route('data-version'))->assertRedirect(route('login'));
    }
}
