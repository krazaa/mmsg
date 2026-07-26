<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeAppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_uses_the_saved_background_color(): void
    {
        SiteSetting::create([
            'key' => 'welcome_background_color',
            'value' => '#123456',
        ]);
        SiteSetting::create([
            'key' => 'welcome_hero_grid_background_color',
            'value' => '#654321',
        ]);
        SiteSetting::create([
            'key' => 'welcome_hero_heading_color',
            'value' => '#abcdef',
        ]);
        SiteSetting::create([
            'key' => 'welcome_hero_stat_value_color',
            'value' => '#fedcba',
        ]);
        SiteSetting::create([
            'key' => 'welcome_hero_stat_label_color',
            'value' => '#aabbcc',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('background-color:#123456', false)
            ->assertSee('background-color: #654321', false)
            ->assertSee('color: #abcdef', false)
            ->assertSee('color: #fedcba', false)
            ->assertSee('color: #aabbcc', false);
    }

    public function test_welcome_page_uses_project_image_paths_from_the_database(): void
    {
        Project::create([
            'name' => 'Sample Project',
            'slug' => 'sample-project',
            'location' => 'Sample Location',
            'image_path' => 'projects/sample-project.jpg',
            'blueprint_path' => 'projects/sample-project-blueprint.jpg',
            'gross_area_marla' => 200,
            'saleable_area_marla' => 150,
            'sold_area_marla' => 0,
            'reserved_area_marla' => 0,
            'status' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(asset('storage/projects/sample-project.jpg'), false)
            ->assertSee(asset('storage/projects/sample-project-blueprint.jpg'), false);
    }

    public function test_welcome_page_features_the_latest_active_project_first(): void
    {
        Project::create([
            'name' => 'Older Project',
            'slug' => 'older-project',
            'location' => 'Older Location',
            'image_path' => 'projects/older.jpg',
            'gross_area_marla' => 200,
            'saleable_area_marla' => 150,
            'sold_area_marla' => 0,
            'reserved_area_marla' => 0,
            'status' => true,
            'created_at' => now()->subDay(),
        ]);

        Project::create([
            'name' => 'Latest Project',
            'slug' => 'latest-project',
            'location' => 'Latest Location',
            'image_path' => 'projects/latest.jpg',
            'gross_area_marla' => 200,
            'saleable_area_marla' => 150,
            'sold_area_marla' => 0,
            'reserved_area_marla' => 0,
            'status' => true,
            'created_at' => now(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['Latest Project', 'Older Project']);
    }
}
