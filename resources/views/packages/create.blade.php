<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800">Add package</h2></x-slot>
    <div class="py-8"><div class="mx-auto max-w-3xl px-4">@if($errors->any())<div class="mb-5 rounded-lg bg-red-100 p-4 text-red-800"><ul class="list-disc ms-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif<form method="POST" action="{{ route('packages.store') }}" class="rounded-xl bg-white p-6 shadow">@include('packages._form')</form></div></div>
</x-app-layout>
