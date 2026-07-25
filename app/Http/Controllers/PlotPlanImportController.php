<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Plot;
use App\Models\Project;
use App\Services\PlotPlanAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlotPlanImportController extends Controller
{
    public function create(Project $project)
    {
        return view('plot-plans.create', compact('project'));
    }

    public function analyze(Request $request, Project $project, PlotPlanAnalyzer $analyzer)
    {
        $data = $request->validate([
            'plan' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'category' => ['required', Rule::in(['residential', 'commercial', 'farmhouse'])],
            'price_per_marla' => ['required', 'numeric', 'min:0'],
        ]);
        $file = $request->file('plan');
        $result = $analyzer->analyze($file->get(), $file->getMimeType());
        $path = $file->store('plot-plans/'.$project->id, 'local');
        $token = (string) Str::uuid();
        $payload = $result + [
            'token' => $token, 'path' => $path, 'category' => $data['category'],
            'price_per_marla' => (float) $data['price_per_marla'],
        ];
        $request->session()->put($this->sessionKey($project), $payload);

        return view('plot-plans.review', ['project' => $project, 'analysis' => $payload]);
    }

    public function store(Request $request, Project $project)
    {
        $analysis = $request->session()->get($this->sessionKey($project));
        if (! is_array($analysis) || ! hash_equals((string) ($analysis['token'] ?? ''), (string) $request->input('token'))) {
            throw ValidationException::withMessages(['plan' => 'This plan review expired. Please upload and analyze the plan again.']);
        }

        $data = $request->validate([
            'token' => ['required', 'uuid'],
            'category' => ['required', Rule::in(['residential', 'commercial', 'farmhouse'])],
            'price_per_marla' => ['required', 'numeric', 'min:0'],
            'blocks' => ['required', 'array', 'max:50'],
            'blocks.*.name' => ['required', 'string', 'max:100'],
            'blocks.*.plots' => ['required', 'array', 'max:500'],
            'blocks.*.plots.*.include' => ['nullable', 'boolean'],
            'blocks.*.plots.*.plot_number' => ['required', 'string', 'max:50'],
            'blocks.*.plots.*.size_marla' => ['required', 'numeric', 'gt:0', 'max:100000'],
        ]);

        $created = 0;
        $skipped = 0;
        DB::transaction(function () use ($data, $project, &$created, &$skipped) {
            foreach ($data['blocks'] as $blockData) {
                $selected = collect($blockData['plots'])->filter(fn (array $plot) => (bool) ($plot['include'] ?? false));
                if ($selected->isEmpty()) {
                    continue;
                }
                $block = Block::firstOrCreate(
                    ['project_id' => $project->id, 'name' => trim($blockData['name'])],
                    ['total_area_marla' => $selected->sum('size_marla'), 'saleable_area_marla' => $selected->sum('size_marla'), 'status' => true]
                );
                foreach ($selected as $plotData) {
                    $size = (float) $plotData['size_marla'];
                    $plot = Plot::firstOrCreate(
                        ['block_id' => $block->id, 'plot_number' => trim($plotData['plot_number'])],
                        ['project_id' => $project->id, 'size_marla' => $size, 'category' => $data['category'],
                            'base_price' => $size * (float) $data['price_per_marla'], 'premium_amount' => 0,
                            'total_price' => $size * (float) $data['price_per_marla'], 'status' => 'available']
                    );
                    $plot->wasRecentlyCreated ? $created++ : $skipped++;
                }
                $area = (float) $block->plots()->sum('size_marla');
                $block->update(['total_area_marla' => $area, 'saleable_area_marla' => $area]);
            }
        });

        $request->session()->forget($this->sessionKey($project));

        return redirect()->route('projects.index')->with('success', "$created plots added to inventory. $skipped existing plots were skipped.");
    }

    private function sessionKey(Project $project): string
    {
        return 'plot_plan_import.'.$project->id;
    }
}
