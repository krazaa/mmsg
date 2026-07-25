<x-app-layout>
    <x-slot name="header"><div><h2 class="text-xl font-black text-gray-900">Edit {{ $block->name }}</h2><p class="text-sm text-gray-500">{{ $block->project?->name }}</p></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl px-4">
        @if($errors->any())<div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('blocks.update', $block) }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">@include('blocks._form')</form>
    </div></div>
</x-app-layout>
