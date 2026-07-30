<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-gray-800 dark:text-white">Add staff member</h2></x-slot>
    <div class="py-5 sm:py-8"><div class="mx-auto max-w-3xl px-3 sm:px-4">
        @if($errors->any())<div class="mb-5 rounded-lg bg-red-100 p-4 text-red-800"><ul class="ms-5 list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('staff.store') }}" class="rounded-xl bg-white p-4 shadow dark:bg-slate-800 sm:p-6">@include('staff._form')</form>
    </div></div>
</x-app-layout>
