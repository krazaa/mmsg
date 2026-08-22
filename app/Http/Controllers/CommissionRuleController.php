<?php

namespace App\Http\Controllers;

use App\Models\CommissionRule;
use App\Models\PlotPackage;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CommissionRuleController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with('packages')->orderBy('name')->get();
        $project = Project::query()->when($request->integer('project'), fn ($query, $id) => $query->whereKey($id))->firstOrFail();
        $packages = $project->packages()->with('commissionRules')->orderBy('size_marla')->get();
        $package = $packages->firstWhere('id', $request->integer('package')) ?? $packages->first();

        $canChangeCalculationType = $request->user()->hasRole('super_admin');

        return view('commissions.rules', compact('projects', 'project', 'packages', 'package', 'canChangeCalculationType'));
    }

    public function update(Request $request, PlotPackage $package)
    {
        $data = $request->validate([
            'levels' => ['required', 'array'],
            'levels.cash' => ['required', 'array', 'size:3'],
            'levels.first_payment' => ['required', 'array', 'size:3'],
            'levels.installment' => ['required', 'array', 'size:3'],
            'levels.*.1' => ['required', 'numeric', 'between:0,100'],
            'levels.*.2' => ['required', 'numeric', 'between:0,100'],
            'levels.*.3' => ['required', 'numeric', 'between:0,100'],
            'calculation_types' => ['nullable', 'array'],
            'calculation_types.*' => ['nullable', 'array'],
            'calculation_types.*.*' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'fixed_amounts' => ['nullable', 'array'],
            'fixed_amounts.*' => ['nullable', 'array'],
            'fixed_amounts.*.*' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'active' => ['nullable', 'array'],
            'active.*' => ['nullable', 'array'],
            'active.*.*' => ['nullable', 'boolean'],
        ]);

        $canChangeCalculationType = $request->user()->hasRole('super_admin');

        DB::transaction(function () use ($package, $data, $canChangeCalculationType) {
            $existingRules = CommissionRule::where('package_id', $package->id)
                ->get()
                ->keyBy(fn (CommissionRule $rule) => $rule->payment_plan.'-'.$rule->level);

            foreach (['cash', 'first_payment', 'installment'] as $paymentPlan) {
                foreach ([1, 2, 3] as $level) {
                    $calculationType = $canChangeCalculationType
                        ? ($data['calculation_types'][$paymentPlan][$level] ?? 'percentage')
                        : ($existingRules->get($paymentPlan.'-'.$level)?->calculation_type ?? 'percentage');

                    CommissionRule::updateOrCreate(
                        ['package_id' => $package->id, 'payment_plan' => $paymentPlan, 'level' => $level],
                        [
                            'percentage' => $calculationType === 'percentage' ? $data['levels'][$paymentPlan][$level] : 0,
                            'calculation_type' => $calculationType,
                            'fixed_amount' => $calculationType === 'fixed' ? ($data['fixed_amounts'][$paymentPlan][$level] ?? 0) : 0,
                            'status' => (bool) ($data['active'][$paymentPlan][$level] ?? false),
                        ]
                    );
                }
            }
            CommissionRule::where('package_id', $package->id)
                ->where(fn ($query) => $query->whereNotIn('payment_plan', ['cash', 'first_payment', 'installment'])->orWhereNotIn('level', [1, 2, 3]))
                ->delete();
        });

        return redirect()->route('commission-rules.index', ['project' => $package->project_id, 'package' => $package->id])->with('success', 'Cash, booking payment, and installment commission levels updated.');
    }
}
