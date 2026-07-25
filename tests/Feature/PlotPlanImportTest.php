<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlotPlanImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_management_user_can_analyze_review_and_import_a_plotting_plan(): void
    {
        config(['services.gemini.api_key' => 'test-key', 'services.gemini.model' => 'test-vision-model']);
        $analysisJson = json_encode([
            'summary' => 'Two plots detected.', 'warnings' => ['Review the second plot.'],
            'blocks' => [['name' => 'Block A', 'plots' => [
                ['plot_number' => '1', 'dimensions' => "25' x 50'", 'size_marla' => 4.59, 'confidence' => .96, 'note' => 'Clear'],
                ['plot_number' => '2', 'dimensions' => "25' x 50'", 'size_marla' => 4.59, 'confidence' => .65, 'note' => 'Marking nearby'],
            ]]],
        ], JSON_THROW_ON_ERROR);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => $analysisJson]]]]],
        ])]);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create(['name' => 'Plan Project', 'slug' => 'plan-project', 'location' => 'Abbottabad', 'gross_area_marla' => 1000, 'saleable_area_marla' => 800]);

        $response = $this->actingAs($admin)->post(route('projects.plot-plan.analyze', $project), [
            'plan' => UploadedFile::fake()->image('plan.jpg', 1200, 1600),
            'category' => 'residential', 'price_per_marla' => 200000,
        ]);

        $response->assertOk()->assertSee('Review detected inventory')->assertSee('Block A')->assertSee('Marking nearby');
        Http::assertSent(fn ($request) => str_contains($request->url(), '/models/test-vision-model:generateContent')
            && $request->hasHeader('x-goog-api-key', 'test-key')
            && $request['contents'][0]['parts'][1]['inline_data']['mime_type'] === 'image/jpeg'
            && filled($request['contents'][0]['parts'][1]['inline_data']['data']));
        $analysis = session('plot_plan_import.'.$project->id);

        $this->post(route('projects.plot-plan.store', $project), [
            'token' => $analysis['token'], 'category' => 'residential', 'price_per_marla' => 200000,
            'blocks' => [['name' => 'Block A', 'plots' => [
                ['include' => 1, 'plot_number' => '1', 'size_marla' => 4.59],
                ['include' => 0, 'plot_number' => '2', 'size_marla' => 4.59],
            ]]],
        ])->assertRedirect(route('projects.index'))->assertSessionHas('success', '1 plots added to inventory. 0 existing plots were skipped.');

        $this->assertDatabaseHas('blocks', ['project_id' => $project->id, 'name' => 'Block A']);
        $this->assertDatabaseHas('plots', ['project_id' => $project->id, 'plot_number' => '1', 'size_marla' => 4.59, 'total_price' => 918000]);
        $this->assertDatabaseMissing('plots', ['project_id' => $project->id, 'plot_number' => '2']);
    }

    public function test_analysis_requires_an_api_key(): void
    {
        config(['services.gemini.api_key' => null]);
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::create(['name' => 'Plan Project', 'slug' => 'plan-project', 'location' => 'Abbottabad', 'gross_area_marla' => 1000, 'saleable_area_marla' => 800]);

        $this->actingAs($admin)->from(route('projects.plot-plan.create', $project))->post(route('projects.plot-plan.analyze', $project), [
            'plan' => UploadedFile::fake()->image('plan.jpg'), 'category' => 'residential', 'price_per_marla' => 0,
        ])->assertRedirect(route('projects.plot-plan.create', $project))->assertSessionHasErrors('plan');
    }
}
