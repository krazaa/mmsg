<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class SecurityAndAuditComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_enforce_permissions_and_critical_changes_are_audited(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->can(Permissions::MANAGE_PROJECTS));
        $this->assertTrue($customer->hasRole('customer'));
        $this->assertFalse($customer->can(Permissions::ACCESS_MANAGEMENT));
        $this->actingAs($customer)->get(route('projects.index'))->assertForbidden();

        $this->actingAs($admin);
        $project = Project::create(['name' => 'Audited Project', 'slug' => 'audited-project', 'location' => 'Abbottabad', 'gross_area_marla' => 100, 'saleable_area_marla' => 100]);
        $project->update(['location' => 'Islamabad']);

        $activity = Activity::where('subject_type', Project::class)->where('subject_id', $project->id)->where('event', 'updated')->firstOrFail();
        $this->assertSame($admin->id, $activity->causer_id);
        $this->assertSame('updated', $activity->event);
        $this->assertSame('Islamabad', $activity->properties['attributes']['location']);
        $this->actingAs($admin)->get(route('management.activity-log.index'))->assertOk()->assertSee('Audited Project');
    }
}
