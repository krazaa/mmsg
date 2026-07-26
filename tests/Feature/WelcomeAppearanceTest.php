<?php

namespace Tests\Feature;

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
}
