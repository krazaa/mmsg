<x-app-layout>
    <x-slot name="header"><div class="flex items-center justify-between"><h2 class="text-xl font-semibold text-gray-800">Manage projects</h2><a href="{{ route('projects.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Add project</a></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl px-4 space-y-5">
        @if(session('success'))<div class="rounded-lg bg-green-100 p-4 text-green-800">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="rounded-lg bg-red-100 p-4 text-red-800">{{ $errors->first() }}</div>@endif
        <div class="overflow-x-auto rounded-xl bg-white shadow">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr>
                        <th class="p-4">Project</th>
                        <th>Area</th>
                        {{-- <th>Inventory</th> --}}
                        <th>Available</th>
                        <th>Packages</th>
                        <th>Bookings</th>
                        <th>Status</th>
                        <th class="pe-4 text-right">Actions</th>
                    </tr>
                </thead>
            <tbody>
                @forelse($projects as $project)
                <tr class="border-t">
                    <td class="p-4">
                        <div class="font-semibold">{{ $project->name }}</div>
                        <div class="text-gray-500">{{ $project->location }}</div>
                    </td>
                    <td>{{ number_format($project->gross_area_marla / 20, 2) }} kanal</td>
                <td>
                    {{-- <div>{{ $project->plots_count }} plots</div> --}}
                    <div class="text-xs text-gray-500">{{ number_format($project->available_area_marla / 20, 2) }} kanal</div>
                </td>
                    <td>{{ $project->packages_count }}</td>
                    <td>{{ $project->bookings_count }}</td>
                    <td><span class="rounded-full px-2 py-1 {{ $project->status ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $project->status ? 'Active' : 'Inactive' }}</span></td>
                    <td class="pe-4"><div class="flex justify-end gap-3">@if($project->status)<a href="{{ route('sales.create',['project'=>$project->id]) }}" class="text-green-700">Sales</a>@endif
                        {{-- <a href="{{ route('projects.plot-plan.create',$project) }}" class="font-semibold text-violet-700">Import plan</a> --}}
                        <a href="{{ route('projects.edit',$project) }}" class="text-indigo-600">Edit</a><form method="POST" action="{{ route('projects.destroy',$project) }}" onsubmit="return confirm('Delete this project?')">@csrf @method('DELETE')<button class="text-red-600">Delete</button></form></div></td></tr>@empty<tr><td colspan="7" class="p-10 text-center text-gray-500">No projects found.</td></tr>@endforelse</tbody>
            </table>
        </div>
    </div></div>
</x-app-layout>
