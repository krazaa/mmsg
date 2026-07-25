<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Email preferences</title>@vite(['resources/css/app.css'])</head>
<body class="grid min-h-screen place-items-center bg-gradient-to-br from-indigo-50 to-violet-100 p-4">
    <main class="w-full max-w-md rounded-3xl bg-white p-8 text-center shadow-xl">
        <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-indigo-100 text-2xl">✉</div>
        @if(session('success'))
            <h1 class="mt-5 text-xl font-black text-slate-900">Preferences updated</h1><p class="mt-2 text-sm text-slate-500">{{ session('success') }}</p>
        @else
            <h1 class="mt-5 text-xl font-black text-slate-900">Unsubscribe from emails?</h1><p class="mt-2 text-sm leading-6 text-slate-500">{{ $recipient->email }} will stop receiving promotional campaigns. Essential booking and payment emails will continue.</p>
            <form method="POST" action="{{ route('email-unsubscribe.store',$recipient->unsubscribe_token) }}" class="mt-6">@csrf<button class="w-full rounded-xl bg-rose-600 px-5 py-3 font-black text-white">Confirm unsubscribe</button></form>
        @endif
    </main>
</body></html>
