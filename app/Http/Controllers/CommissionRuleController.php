<?php

namespace App\Http\Controllers;

use App\Models\CommissionRule;
use App\Models\PlotPackage;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionRuleController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with('packages')->orderBy('name')->get();
        $project = Project::query()->when($request->integer('project'), fn ($query, $id) => $query->whereKey($id))->firstOrFail();
        $packages = $project->packages()->with('commissionRules')->orderBy('size_marla')->get();
        $package = $packages->firstWhere('id', $request->integer('package')) ?? $packages->first();

        return view('commissions.rules', compact('projects', 'project', 'packages', 'package'));
    }

    public function update(Request $request, PlotPackage $package)
    {
        $data = $request->validate([
            'levels' => ['required', 'array', 'size:3'],
            'levels.1' => ['required', 'numeric', 'between:0,100'],
            'levels.2' => ['required', 'numeric', 'between:0,100'],
            'levels.3' => ['required', 'numeric', 'between:0,100'],
            'active' => ['nullable', 'array'], 'active.*' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($package, $data) {
            foreach ([1, 2, 3] as $level) {
                CommissionRule::updateOrCreate(
                    ['package_id' => $package->id, 'level' => $level],
                    ['percentage' => $data['levels'][$level], 'status' => (bool) ($data['active'][$level] ?? false)]
                );
            }
            CommissionRule::where('package_id', $package->id)->whereNotIn('level', [1, 2, 3])->delete();
        });

        return redirect()->route('commission-rules.index', ['project' => $package->project_id, 'package' => $package->id])->with('success', 'Package commission levels updated.');
    }
}
