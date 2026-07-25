<?php

namespace App\Http\Controllers;

use App\Models\CommissionRule;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::withCount(['packages', 'bookings', 'plots'])->latest()->get();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $project = Project::create($this->attributes($data, $request));

        return redirect()->route('packages.create', ['project' => $project->id])
            ->with('success', 'Project created successfully. Add its first package manually.');
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $this->validated($request, $project);
        $attributes = $this->attributes($data, $request, $project);

        if ((float) $attributes['saleable_area_marla'] < (float) $project->sold_area_marla + (float) $attributes['reserved_area_marla']) {
            throw ValidationException::withMessages([
                'saleable_area_kanal' => 'Saleable area cannot be less than the sold and reserved area.',
            ]);
        }

        $project->update($attributes);

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        if ($project->bookings()->exists()) {
            return back()->withErrors(['project' => 'A project with bookings cannot be deleted. Deactivate it instead.']);
        }

        DB::transaction(function () use ($project) {
            CommissionRule::whereIn('package_id', $project->packages()->pluck('id'))->delete();
            $project->packages()->delete();
            $project->delete();
        });

        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    private function validated(Request $request, ?Project $project = null): array
    {
        if (! $request->filled('slug') && $request->filled('name')) {
            $request->merge(['slug' => Str::slug($request->string('name')->toString())]);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('projects')->ignore($project)],
            'location' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'blueprint' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'gross_area_kanal' => ['required', 'numeric', 'gt:0'],
            'saleable_area_kanal' => ['required', 'numeric', 'gt:0', 'lte:gross_area_kanal'],
            'reserved_area_kanal' => ['nullable', 'numeric', 'min:0', 'lte:saleable_area_kanal'],
            'status' => ['nullable', 'boolean'],
        ]);
    }

    private function attributes(array $data, Request $request, ?Project $project = null): array
    {
        $attributes = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'location' => $data['location'],
            'description' => $data['description'] ?? null,
            'gross_area_marla' => $data['gross_area_kanal'] * 20,
            'saleable_area_marla' => $data['saleable_area_kanal'] * 20,
            'reserved_area_marla' => ($data['reserved_area_kanal'] ?? 0) * 20,
            'status' => (bool) ($data['status'] ?? false),
        ];

        foreach (['image' => 'image_path', 'blueprint' => 'blueprint_path'] as $input => $column) {
            if (! $request->hasFile($input)) {
                continue;
            }

            if ($project?->{$column} && str_starts_with($project->{$column}, 'projects/')) {
                Storage::disk('public')->delete($project->{$column});
            }

            $attributes[$column] = $request->file($input)->store('projects', 'public');
        }

        return $attributes;
    }
}
