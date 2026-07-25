<?php

namespace Tests\Feature;

use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_update_package(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Project', 'slug' => 'project', 'location' => 'Abbottabad',
            'gross_area_marla' => 8000, 'saleable_area_marla' => 8000, 'status' => true,
        ]);
        $data = [
            'project_id' => $project->id, 'name' => '8 Marla', 'size_marla' => 8,
            'booking_amount' => 350000, 'months' => 24, 'monthly_amount' => 50000,
            'balloons' => [
                ['month' => 12, 'amount' => 150000],
                ['month' => 24, 'amount' => 250000],
            ],
            'status' => 1,
        ];

        $this->actingAs($user)->post(route('packages.store'), $data)
            ->assertRedirect(route('packages.index', ['project' => $project->id]));
        $package = PlotPackage::where('name', '8 Marla')->firstOrFail();
        $this->assertEquals(1950000, $package->total_price);
        $this->assertEquals(24, $package->months);
        $this->assertSame([
            ['month' => 12, 'amount' => 150000.0],
            ['month' => 24, 'amount' => 250000.0],
        ], $package->balloonPayments());
        $this->actingAs($user)->get(route('packages.edit', $package))
            ->assertOk()->assertSee('Total price (calculated)')->assertSee('Add payment')->assertSee('Remove');

        $data['monthly_amount'] = 60000;
        $this->actingAs($user)->put(route('packages.update', $package), $data)
            ->assertRedirect(route('packages.index', ['project' => $project->id]));
        $this->assertEquals(60000, (float) $package->refresh()->monthly_amount);
    }

    public function test_dynamic_balloon_months_are_validated(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Project', 'slug' => 'project', 'gross_area_marla' => 100,
            'saleable_area_marla' => 100, 'status' => true,
        ]);
        $data = [
            'project_id' => $project->id, 'name' => 'Plan', 'size_marla' => 5,
            'booking_amount' => 100, 'months' => 18, 'monthly_amount' => 10, 'status' => 1,
        ];

        $this->actingAs($user)->post(route('packages.store'), $data + [
            'balloons' => [['month' => 19, 'amount' => 50]],
        ])->assertSessionHasErrors('balloons.0.month');

        $this->actingAs($user)->post(route('packages.store'), $data + [
            'balloons' => [
                ['month' => 6, 'amount' => 50],
                ['month' => 6, 'amount' => 75],
            ],
        ])->assertSessionHasErrors('balloons.1.month');
    }

    public function test_duplicate_package_name_in_same_project_is_rejected(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $project = Project::firstOrFail();

        $this->actingAs($user)->post(route('packages.store'), [
            'project_id' => $project->id, 'name' => '5 Marla', 'size_marla' => 5,
            'booking_amount' => 1, 'months' => 36, 'monthly_amount' => 1, 'month_12_balloon' => 1,
            'month_24_balloon' => 1, 'month_36_balloon' => 1, 'status' => 1,
        ])->assertSessionHasErrors('name');
    }

    public function test_packages_can_be_sorted_by_name_descending(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $project = Project::firstOrFail();

        $this->actingAs($user)->get(route('packages.index', ['project' => $project->id, 'sort' => 'name', 'direction' => 'desc']))
            ->assertOk()->assertSeeInOrder(['Farmhouse', '5 Marla', '20 Marla', '15 Marla', '10 Marla']);
    }

    public function test_packages_can_be_searched_and_filtered_by_status(): void
    {
        $this->seed();
        $user = User::where('email', 'admin@abdullahtown.pk')->firstOrFail();
        $project = Project::firstOrFail();
        $project->packages()->where('name', '5 Marla')->update(['status' => false]);

        $this->actingAs($user)->get(route('packages.index', [
            'project' => $project->id, 'q' => '5 Marla', 'status' => 'inactive',
        ]))->assertOk()->assertViewHas('packages', fn ($packages) => $packages->pluck('name')->all() === ['5 Marla']);

        $this->actingAs($user)->get(route('packages.index', [
            'project' => $project->id, 'status' => 'active',
        ]))->assertOk()->assertViewHas('packages', fn ($packages) => ! $packages->pluck('name')->contains('5 Marla'));
    }
}
