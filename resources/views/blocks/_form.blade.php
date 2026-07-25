@csrf
@if(isset($block)) @method('PUT') @endif

<div class="grid gap-5 sm:grid-cols-2">
    <label class="text-sm font-semibold text-gray-700">Project
        <select name="project_id" required class="mt-1.5 w-full rounded-xl border-gray-300">
            <option value="">Select project</option>
            @foreach($projects as $project)
                <option value="{{ $project->id }}" @selected(old('project_id', $block->project_id ?? request('project')) == $project->id)>{{ $project->name }}</option>
            @endforeach
        </select>
    </label>
    <label class="text-sm font-semibold text-gray-700">Block name
        <input name="name" value="{{ old('name', $block->name ?? '') }}" maxlength="255" required placeholder="e.g. Block A" class="mt-1.5 w-full rounded-xl border-gray-300">
    </label>
    <label class="text-sm font-semibold text-gray-700">Total area (marla)
        <input type="number" name="total_area_marla" value="{{ old('total_area_marla', $block->total_area_marla ?? '') }}" min="0.01" step="0.01" required class="mt-1.5 w-full rounded-xl border-gray-300">
    </label>
    <label class="text-sm font-semibold text-gray-700">Saleable area (marla)
        <input type="number" name="saleable_area_marla" value="{{ old('saleable_area_marla', $block->saleable_area_marla ?? '') }}" min="0.01" step="0.01" required class="mt-1.5 w-full rounded-xl border-gray-300">
    </label>
    <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 sm:col-span-2">
        <input type="hidden" name="status" value="0">
        <input type="checkbox" name="status" value="1" @checked(old('status', $block->status ?? true)) class="rounded border-gray-300 text-indigo-600">
        Active and available for inventory
    </label>
</div>

<div class="mt-7 flex gap-3">
    <button class="rounded-xl bg-indigo-600 px-5 py-3 font-black text-white hover:bg-indigo-700">{{ isset($block) ? 'Save changes' : 'Create block' }}</button>
    <a href="{{ route('blocks.index') }}" class="rounded-xl border border-gray-300 px-5 py-3 font-bold text-gray-700 hover:bg-gray-50">Cancel</a>
</div>
