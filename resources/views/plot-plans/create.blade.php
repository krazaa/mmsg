<x-app-layout>
    <x-slot name="header"><div><h2 class="text-xl font-black text-gray-900">Import plotting plan</h2><p class="text-sm text-gray-500">{{ $project->name }} · Automatically prepare blocks and plots from a map image</p></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-4xl space-y-5 px-4">
        @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">{{ $errors->first() }}</div>@endif
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-5 text-sm text-indigo-900"><b>How it works:</b> upload a clear full-resolution JPG, PNG, or WebP. The map is analyzed for block boundaries, plot numbers, and dimensions. You will review and correct everything before inventory is created.</div>
        <form method="POST" action="{{ route('projects.plot-plan.analyze',$project) }}" enctype="multipart/form-data" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 sm:p-8">@csrf
            <div class="grid gap-6 sm:grid-cols-2">
                <label class="sm:col-span-2"><span class="text-sm font-bold text-gray-800">Plotting plan image</span><input id="plan" type="file" name="plan" required accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-dashed border-gray-300 p-4 text-sm" onchange="document.getElementById('preview').src=URL.createObjectURL(this.files[0]);document.getElementById('preview').classList.remove('hidden')"><span class="mt-2 block text-xs text-gray-500">Maximum 20 MB. Avoid screenshots when the original high-resolution map is available.</span></label>
                <img id="preview" alt="Plan preview" class="hidden max-h-96 w-full rounded-xl border object-contain sm:col-span-2">
                <label class="text-sm font-bold text-gray-800">Plot category<select name="category" required class="mt-2 w-full rounded-lg border-gray-300"><option value="residential">Residential</option><option value="commercial">Commercial</option><option value="farmhouse">Farmhouse</option></select></label>
                <label class="text-sm font-bold text-gray-800">Base price per marla (Rs)<input type="number" name="price_per_marla" value="{{ old('price_per_marla',0) }}" min="0" step="0.01" required class="mt-2 w-full rounded-lg border-gray-300"><span class="mt-1 block text-xs font-normal text-gray-500">You can use 0 and set pricing later.</span></label>
            </div>
            <div class="mt-7 flex items-center justify-between gap-4"><a href="{{ route('projects.index') }}" class="text-sm font-bold text-gray-600">Cancel</a><button class="rounded-xl bg-violet-600 px-6 py-3 font-black text-white shadow hover:bg-violet-700">Analyze plotting plan</button></div>
        </form>
    </div></div>
</x-app-layout>
