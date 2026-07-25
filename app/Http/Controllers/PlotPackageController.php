<?php

namespace App\Http\Controllers;

use App\Models\PlotPackage;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PlotPackageController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::orderBy('name')->get();
        $project = Project::query()
            ->when($request->integer('project'), fn ($query, $id) => $query->whereKey($id))
            ->firstOrFail();
        $sort = in_array($request->string('sort')->toString(), ['name', 'size', 'total', 'booking_amount', 'monthly_amount', 'bookings', 'status'], true)
            ? $request->string('sort')->toString() : 'size';
        $direction = $request->string('direction')->toString() === 'desc' ? 'desc' : 'asc';
        $search = trim($request->string('q')->toString());
        $status = in_array($request->string('status')->toString(), ['active', 'inactive'], true)
            ? $request->string('status')->toString() : 'all';
        $query = $project->packages()
            ->withCount('bookings')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($status !== 'all', fn ($query) => $query->where('status', $status === 'active'));
        match ($sort) {
            'name' => $query->orderBy('name', $direction),
            'total' => $query->orderBy('id'),
            'booking_amount' => $query->orderBy('booking_amount', $direction),
            'monthly_amount' => $query->orderBy('monthly_amount', $direction),
            'bookings' => $query->orderBy('bookings_count', $direction),
            'status' => $query->orderBy('status', $direction),
            default => $query->orderBy('size_marla', $direction),
        };
        $packages = $query->orderBy('id')->get();
        if ($sort === 'total') {
            $packages = $direction === 'desc'
                ? $packages->sortByDesc('total_price')->values()
                : $packages->sortBy('total_price')->values();
        }

        return view('packages.index', compact('projects', 'project', 'packages', 'sort', 'direction', 'search', 'status'));
    }

    public function create(Request $request)
    {
        $projects = Project::orderBy('name')->get();
        $selectedProject = $request->integer('project');

        return view('packages.create', compact('projects', 'selectedProject'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $package = PlotPackage::create($this->attributes($data));
        foreach ([1 => 5, 2 => 3, 3 => 2] as $level => $percentage) {
            $package->commissionRules()->create(compact('level', 'percentage') + ['status' => true]);
        }

        return redirect()->route('packages.index', ['project' => $data['project_id']])->with('success', 'Package created successfully.');
    }

    public function edit(PlotPackage $package)
    {
        $projects = Project::orderBy('name')->get();

        return view('packages.edit', compact('package', 'projects'));
    }

    public function update(Request $request, PlotPackage $package)
    {
        $data = $this->validated($request, $package);
        $package->update($this->attributes($data));

        return redirect()->route('packages.index', ['project' => $data['project_id']])->with('success', 'Package updated. Existing booking schedules were not changed.');
    }

    public function destroy(PlotPackage $package)
    {
        if ($package->bookings()->exists()) {
            return back()->withErrors(['package' => 'A package used by bookings cannot be deleted. Deactivate it instead.']);
        }

        $projectId = $package->project_id;
        $package->commissionRules()->delete();
        $package->delete();

        return redirect()->route('packages.index', ['project' => $projectId])->with('success', 'Package deleted.');
    }

    private function validated(Request $request, ?PlotPackage $package = null): array
    {
        $data = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'size_marla' => ['required', 'numeric', 'gt:0'],
            'booking_amount' => ['required', 'numeric', 'min:0'],
            'months' => ['required', 'integer', 'between:1,60'],
            'monthly_amount' => ['required', 'numeric', 'min:0'],
            'balloons' => ['nullable', 'array', 'max:60'],
            'balloons.*.month' => ['required', 'integer', 'min:1', 'distinct'],
            'balloons.*.amount' => ['required', 'numeric', 'gt:0'],
            'status' => ['nullable', 'boolean'],
        ]);

        $duplicate = PlotPackage::query()->where('project_id', $data['project_id'])->where('name', $data['name'])
            ->when($package, fn ($query) => $query->whereKeyNot($package->id))->exists();
        if ($duplicate) {
            throw ValidationException::withMessages(['name' => 'This project already has a package with that name.']);
        }

        $project = Project::findOrFail($data['project_id']);
        if ((float) $data['size_marla'] > (float) $project->saleable_area_marla) {
            throw ValidationException::withMessages(['size_marla' => 'Package size cannot exceed the project saleable area.']);
        }

        foreach ($data['balloons'] ?? [] as $index => $balloon) {
            if ((int) $balloon['month'] > (int) $data['months']) {
                throw ValidationException::withMessages([
                    "balloons.$index.month" => 'A balloon payment month cannot exceed the package duration.',
                ]);
            }
        }

        return $data;
    }

    private function attributes(array $data): array
    {
        $balloons = collect($data['balloons'] ?? [])->map(fn (array $balloon) => [
            'month' => (int) $balloon['month'],
            'amount' => (float) $balloon['amount'],
        ])->sortBy('month')->values()->all();

        unset($data['balloons']);

        return $data + [
            'balloon_payments' => $balloons,
            'month_12_balloon' => collect($balloons)->firstWhere('month', 12)['amount'] ?? 0,
            'month_24_balloon' => collect($balloons)->firstWhere('month', 24)['amount'] ?? 0,
            'month_36_balloon' => collect($balloons)->firstWhere('month', 36)['amount'] ?? 0,
            'status' => (bool) ($data['status'] ?? false),
        ];
    }
}
