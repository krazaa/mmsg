<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserstampsTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_models_automatically_track_creator_and_updater(): void
    {
        $creator = User::factory()->create(['role' => 'admin']);
        $updater = User::factory()->create(['role' => 'admin']);

        $this->actingAs($creator);
        $project = Project::create([
            'name' => 'Audited Project',
            'slug' => 'audited-project',
            'location' => 'Abbottabad',
            'gross_area_marla' => 100,
            'saleable_area_marla' => 80,
        ]);

        $this->assertSame($creator->id, $project->created_by);
        $this->assertSame($creator->id, $project->updated_by);
        $this->assertTrue($project->createdBy->is($creator));

        $this->actingAs($updater);
        $project->update(['location' => 'Islamabad']);

        $this->assertSame($creator->id, $project->refresh()->created_by);
        $this->assertSame($updater->id, $project->updated_by);
        $this->assertTrue($project->updatedBy->is($updater));
    }

    public function test_user_table_does_not_receive_userstamp_columns(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'created_by'));
        $this->assertFalse(Schema::hasColumn('users', 'updated_by'));
    }
}
