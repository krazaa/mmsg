<x-app-layout>
    <x-slot name="header"><div><h2 class="text-xl font-black text-slate-950 dark:text-white">WhatsApp notifications</h2><p class="text-sm text-slate-500 dark:text-slate-300">Connection status and customer lifecycle message coverage</p></div></x-slot>

    <div class="py-8"><div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6">
        @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 font-semibold text-emerald-800">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="rounded-xl border border-red-200 bg-red-50 p-4 font-semibold text-red-800">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 font-semibold text-red-800">{{ $errors->first() }}</div>@endif

        <section class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800"><div class="text-xs font-black uppercase tracking-wider text-slate-400">Channel</div><div class="mt-2 text-xl font-black {{ $enabled ? 'text-emerald-600' : 'text-red-600' }}">{{ $enabled ? 'Enabled' : 'Disabled' }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800"><div class="text-xs font-black uppercase tracking-wider text-slate-400">Credentials</div><div class="mt-2 text-xl font-black {{ $configured ? 'text-emerald-600' : 'text-amber-600' }}">{{ $configured ? 'Ready' : 'Incomplete' }}</div></div>
            <div class="rounded-2xl border bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800"><div class="text-xs font-black uppercase tracking-wider text-slate-400">Phone number ID</div><div class="mt-2 font-mono text-sm font-bold text-slate-700 dark:text-slate-200">{{ $phoneNumberId ? str_repeat('•', max(0, strlen($phoneNumberId) - 4)).substr($phoneNumberId, -4) : 'Not configured' }}</div></div>
        </section>

        <section class="rounded-2xl border bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800"><h3 class="text-lg font-black dark:text-white">Automatic messages</h3><p class="mt-1 text-sm text-slate-500">These existing account events are also sent through WhatsApp when the customer has a phone number.</p><div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach(['Booking request submitted','Booking approved or cancelled','Property plan activated','Payment submitted','Payment verified or reversed','Installment due reminder'] as $event)<div class="rounded-xl bg-slate-50 p-3 text-sm font-bold text-slate-700 dark:bg-slate-900 dark:text-slate-200">✓ {{ $event }}</div>@endforeach</div></section>

        <section class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm dark:border-emerald-900 dark:bg-slate-800">
            <h3 class="text-lg font-black dark:text-white">Owner payment alerts</h3>
            <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-300">These WhatsApp numbers receive an alert when a customer submits a first payment or an installment payment. Add one number per line, including country code when possible.</p>
            <form method="POST" action="{{ route('management.whatsapp.owners.update') }}" class="mt-5">@csrf @method('PUT')
                <label class="block text-xs font-black uppercase tracking-wide text-slate-500">Owner WhatsApp numbers
                    <textarea name="owner_numbers" rows="5" placeholder="+923001234567&#10;+923009876543" class="mt-1.5 w-full rounded-xl border-slate-300 font-mono text-sm dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old('owner_numbers', $ownerNumbers) }}</textarea>
                </label>
                <x-input-error :messages="$errors->get('owner_numbers')" class="mt-2" />
                <button class="mt-4 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700">Save owner numbers</button>
            </form>
        </section>

        <section id="notification-templates" class="scroll-mt-24 rounded-2xl border border-indigo-200 bg-white p-6 shadow-sm dark:border-indigo-900 dark:bg-slate-800">
            <h3 class="text-lg font-black dark:text-white">Notification templates</h3>
            <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-300">Edit the WhatsApp message text. Available placeholders: <code>{customer}</code>, <code>{booking}</code>, <code>{project}</code>, <code>{amount}</code>, <code>{receipt}</code>, <code>{due_date}</code>, <code>{days_overdue}</code>, and <code>{url}</code>.</p>
            <form method="POST" action="{{ route('management.whatsapp.templates.update') }}" class="mt-5 space-y-4">@csrf @method('PUT')
                @foreach([
                    'customer_booking_approved' => 'Customer · Plot approved',
                    'customer_first_payment_verified' => 'Customer · First payment verified',
                    'customer_installment_verified' => 'Customer · Installment verified',
                    'customer_upcoming_installment' => 'Customer · Due in five days',
                    'customer_overdue_installment' => 'Customer · Overdue weekly reminder',
                    'owner_first_payment_received' => 'Owner · First payment received',
                    'owner_installment_received' => 'Owner · Installment received',
                ] as $key => $label)
                    <label class="block text-xs font-black uppercase tracking-wide text-slate-500">{{ $label }}
                        <textarea name="templates[{{ $key }}]" rows="5" required class="mt-1.5 w-full rounded-xl border-slate-300 font-mono text-xs leading-5 dark:border-slate-600 dark:bg-slate-900 dark:text-white">{{ old("templates.$key", $templates[$key]) }}</textarea>
                    </label>
                    <x-input-error :messages="$errors->get('templates.'.$key)" class="mt-2" />
                @endforeach
                <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white hover:bg-indigo-700">Save notification templates</button>
            </form>
        </section>

        <section class="rounded-2xl border bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800"><h3 class="text-lg font-black dark:text-white">Send a test message</h3><p class="mt-1 text-sm text-slate-500">Enter a WhatsApp number, including or omitting Pakistan's country code.</p><form method="POST" action="{{ route('management.whatsapp.test') }}" class="mt-5 flex flex-col gap-3 sm:flex-row">@csrf<input name="phone" value="{{ old('phone', auth()->user()->phone) }}" placeholder="03001234567" class="flex-1 rounded-xl border-slate-300 dark:bg-slate-900" required><button class="rounded-xl bg-emerald-600 px-5 py-3 font-black text-white hover:bg-emerald-700" @disabled(!$configured)>Send test WhatsApp</button></form>@unless($configured)<p class="mt-3 text-xs font-semibold text-amber-700">Enable WhatsApp and add the Phone Number ID and access token in the environment configuration before testing.</p>@endunless</section>
    </div></div>
</x-app-layout>
