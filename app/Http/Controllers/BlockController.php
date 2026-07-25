<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlockController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::orderBy('name')->get();
        $blocks = Block::with('project')->withCount('plots')
            ->when($request->integer('project'), fn ($query, $id) => $query->where('project_id', $id))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->trim().'%'))
            ->latest()->paginate(25)->withQueryString();

        return view('blocks.index', compact('blocks', 'projects'));
    }

    public function create()
    {
        $projects = Project::where('status', true)->orderBy('name')->get();

        return view('blocks.create', compact('projects'));
    }

    public function store(Request $request)
    {
        Block::create($this->validated($request));

        return redirect()->route('blocks.index')->with('success', 'Block created successfully.');
    }

    public function edit(Block $block)
    {
        $projects = Project::orderBy('name')->get();

        return view('blocks.edit', compact('block', 'projects'));
    }

    public function update(Request $request, Block $block)
    {
        $block->update($this->validated($request, $block));

        return redirect()->route('blocks.index')->with('success', 'Block updated successfully.');
    }

    public function destroy(Block $block)
    {
        if ($block->plots()->exists()) {
            return back()->withErrors(['block' => 'A block containing plots cannot be deleted. Deactivate it instead.']);
        }

        $block->delete();

        return redirect()->route('blocks.index')->with('success', 'Block deleted successfully.');
    }

    private function validated(Request $request, ?Block $block = null): array
    {
        return $request->validate([
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('blocks')->where(fn ($query) => $query->where('project_id', $request->integer('project_id')))->ignore($block),
            ],
            'total_area_marla' => ['required', 'numeric', 'gt:0'],
            'saleable_area_marla' => ['required', 'numeric', 'gt:0', 'lte:total_area_marla'],
            'status' => ['nullable', 'boolean'],
        ]) + ['status' => $request->boolean('status')];
    }
}
