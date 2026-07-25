@csrf
@if(isset($project)) @method('PUT') @endif

<div class="grid gap-5 sm:grid-cols-2">
    <label class="block text-sm font-medium text-gray-700">Project name
        <input name="name" value="{{ old('name', $project->name ?? '') }}" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
    </label>
    <label class="block text-sm font-medium text-gray-700">Slug
        <input name="slug" value="{{ old('slug', $project->slug ?? '') }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" placeholder="Generated from name if blank">
    </label>
    <label class="block text-sm font-medium text-gray-700 sm:col-span-2">Location
        <input name="location" value="{{ old('location', $project->location ?? '') }}" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
    </label>
    <label class="block text-sm font-medium text-gray-700 sm:col-span-2">Description
        <textarea name="description" rows="4" maxlength="2000" class="mt-1 w-full rounded-md border-gray-300 shadow-sm" placeholder="Briefly describe the project for the front page.">{{ old('description', $project->description ?? '') }}</textarea>
    </label>
    <label class="block text-sm font-medium text-gray-700">Project image
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm">
        @if(isset($project) && $project->image_path)<span class="mt-1 block text-xs text-emerald-700">Current image is shown on the front page.</span>@endif
    </label>
    <label class="block text-sm font-medium text-gray-700">Project blueprint
        <input type="file" name="blueprint" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm">
        @if(isset($project) && $project->blueprint_path)<span class="mt-1 block text-xs text-emerald-700">Current blueprint is shown on the front page.</span>@endif
    </label>
    <label class="block text-sm font-medium text-gray-700">Total area (kanal)
        <input type="number" step="0.01" min="0.01" name="gross_area_kanal" value="{{ old('gross_area_kanal', isset($project) ? $project->gross_area_marla / 20 : '') }}" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
    </label>
    <label class="block text-sm font-medium text-gray-700">Saleable area (kanal)
        <input type="number" step="0.01" min="0.01" name="saleable_area_kanal" value="{{ old('saleable_area_kanal', isset($project) ? $project->saleable_area_marla / 20 : '') }}" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
    </label>
    <label class="block text-sm font-medium text-gray-700">Reserved area (kanal)
        <input type="number" step="0.01" min="0" name="reserved_area_kanal" value="{{ old('reserved_area_kanal', isset($project) ? $project->reserved_area_marla / 20 : 0) }}" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
    </label>
    <label class="flex items-center gap-2 self-end pb-3 text-sm font-medium text-gray-700">
        <input type="hidden" name="status" value="0">
        <input type="checkbox" name="status" value="1" @checked(old('status', $project->status ?? true)) class="rounded border-gray-300 text-indigo-600">
        Active and available for sales
    </label>
</div>

<div class="mt-6 flex gap-3">
    <button class="rounded-md bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-700">{{ isset($project) ? 'Save changes' : 'Create project' }}</button>
    <a href="{{ route('projects.index') }}" class="rounded-md border px-5 py-2.5 text-gray-700">Cancel</a>
</div>
