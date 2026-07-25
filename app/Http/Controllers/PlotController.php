<?php

namespace App\Http\Controllers;

use App\Enums\PlotStatus;
use App\Models\Block;
use App\Models\Plot;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlotController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::orderBy('name')->get();
        $blocks = Block::with('project')->orderBy('project_id')->orderBy('name')->get();
        $plots = Plot::with(['project', 'block'])->withExists('allotment')
            ->when($request->integer('project'), fn ($query, $id) => $query->where('project_id', $id))
            ->when($request->integer('block'), fn ($query, $id) => $query->where('block_id', $id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($query) => $query->where('plot_number', 'like', '%'.$request->string('search')->trim().'%'))
            ->latest()->paginate(25)->withQueryString();

        return view('plots.index', compact('plots', 'projects', 'blocks'));
    }

    public function create()
    {
        $projects = Project::where('status', true)->orderBy('name')->get();
        $blocks = Block::with('project')->where('status', true)->whereHas('project', fn ($query) => $query->where('status', true))
            ->orderBy('project_id')->orderBy('name')->get();

        return view('plots.create', compact('projects', 'blocks'));
    }

    public function store(Request $request)
    {
        Plot::create($this->validated($request));

        return redirect()->route('plots.index')->with('success', 'Plot created successfully.');
    }

    public function edit(Plot $plot)
    {
        if ($plot->allotment()->exists()) {
            return redirect()->route('plots.index')->withErrors(['plot' => 'An allotted plot cannot be edited.']);
        }

        $projects = Project::orderBy('name')->get();
        $blocks = Block::with('project')->orderBy('project_id')->orderBy('name')->get();

        return view('plots.edit', compact('plot', 'projects', 'blocks'));
    }

    public function update(Request $request, Plot $plot)
    {
        if ($plot->allotment()->exists()) {
            return redirect()->route('plots.index')->withErrors(['plot' => 'An allotted plot cannot be edited.']);
        }

        $plot->update($this->validated($request, $plot));

        return redirect()->route('plots.index')->with('success', 'Plot updated successfully.');
    }

    public function destroy(Plot $plot)
    {
        if ($plot->allotment()->exists()) {
            return back()->withErrors(['plot' => 'An allotted plot cannot be deleted.']);
        }

        $plot->delete();

        return redirect()->route('plots.index')->with('success', 'Plot deleted successfully.');
    }

    private function validated(Request $request, ?Plot $plot = null): array
    {
        $data = $request->validate([
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'block_id' => ['required', 'integer', Rule::exists('blocks', 'id')],
            'plot_number' => [
                'required', 'string', 'max:50',
                Rule::unique('plots')->where(fn ($query) => $query->where('block_id', $request->integer('block_id')))->ignore($plot),
            ],
            'size_marla' => ['required', 'numeric', 'gt:0'],
            'category' => ['required', Rule::in(['residential', 'commercial', 'farmhouse'])],
            'base_price' => ['required', 'numeric', 'min:0'],
            'premium_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(PlotStatus::class)],
        ]);

        $block = Block::findOrFail($data['block_id']);
        if ($block->project_id !== (int) $data['project_id']) {
            throw ValidationException::withMessages(['block_id' => 'Select a block belonging to the chosen project.']);
        }

        $data['premium_amount'] = (float) ($data['premium_amount'] ?? 0);
        $data['total_price'] = (float) $data['base_price'] + $data['premium_amount'];
        $data['version'] = ($plot?->version ?? 0) + 1;

        return $data;
    }
}
