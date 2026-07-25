<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_project_without_automatic_packages(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name' => 'Green Valley', 'location' => 'Islamabad',
            'gross_area_kanal' => 500, 'saleable_area_kanal' => 400,
            'reserved_area_kanal' => 20, 'status' => 1,
        ]);

        $project = Project::where('slug', 'green-valley')->firstOrFail();
        $response->assertRedirect(route('packages.create', ['project' => $project->id]));
        $this->assertEquals(10000, (float) $project->gross_area_marla);
        $this->assertEquals(8000, (float) $project->saleable_area_marla);
        $this->assertCount(0, $project->packages);
    }

    public function test_saleable_area_cannot_be_reduced_below_sold_and_reserved_area(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'name' => 'Test', 'slug' => 'test', 'location' => 'Test',
            'gross_area_marla' => 1000, 'saleable_area_marla' => 800,
            'sold_area_marla' => 500, 'reserved_area_marla' => 100, 'status' => true,
        ]);

        $this->actingAs($user)->put(route('projects.update', $project), [
            'name' => 'Test', 'slug' => 'test', 'location' => 'Test',
            'gross_area_kanal' => 50, 'saleable_area_kanal' => 25,
            'reserved_area_kanal' => 5, 'status' => 1,
        ])->assertSessionHasErrors('saleable_area_kanal');
    }
}
