<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $payment->receipt_number }} · {{ config('app.name', 'MMS Group') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            @page { margin: 12mm; }
            body { background: white !important; }
            .no-print { display: none !important; }
            .receipt { box-shadow: none !important; border-color: #d1d5db !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 px-4 py-8 font-sans text-slate-900 sm:py-12">
    <div class="no-print mx-auto mb-4 flex max-w-3xl items-center justify-between gap-3">
        <a href="{{ route('dashboard') }}#payments" class="text-sm font-bold text-indigo-700 hover:text-indigo-900">← Back to payments</a>
        <button type="button" onclick="window.print()" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow hover:bg-indigo-700">Print / save PDF</button>
    </div>

    <main class="receipt mx-auto max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
        <header class="flex flex-col gap-5 bg-gradient-to-r from-indigo-950 to-indigo-700 p-7 text-white sm:flex-row sm:items-center sm:justify-between sm:p-9">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-indigo-200">MMS Group</p>
                <h1 class="mt-2 text-3xl font-black">Payment receipt</h1>
                <p class="mt-1 text-sm text-indigo-100">Customer property account</p>
            </div>
            <div class="sm:text-right">
                <p class="text-xs font-bold uppercase tracking-wider text-indigo-200">Receipt number</p>
                <p class="mt-1 font-mono text-lg font-black">{{ $payment->receipt_number }}</p>
                <span class="mt-2 inline-block rounded-full px-3 py-1 text-xs font-black {{ $payment->status === 'verified' ? 'bg-emerald-400 text-emerald-950' : ($payment->status === 'pending' ? 'bg-amber-300 text-amber-950' : 'bg-red-300 text-red-950') }}">
                    {{ $payment->status === 'pending' ? 'UNDER REVIEW' : strtoupper($payment->status) }}
                </span>
            </div>
        </header>

        @if($payment->status !== 'verified')
            <div class="border-b border-amber-200 bg-amber-50 px-7 py-4 text-sm font-semibold text-amber-900 sm:px-9">
                {{ $payment->status === 'pending' ? 'This acknowledges your submission. It is not a verified payment receipt until office review is complete.' : 'This payment was reversed and is not included in your verified balance.' }}
            </div>
        @endif

        <section class="p-7 sm:p-9">
            <div class="grid gap-6 border-b border-slate-200 pb-7 sm:grid-cols-2">
                <div><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Received from</p><p class="mt-2 text-lg font-black">{{ $payment->customer->name }}</p><p class="mt-1 text-sm text-slate-500">{{ $payment->customer->phone }}</p></div>
                <div class="sm:text-right"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Amount</p><p class="mt-2 text-3xl font-black text-indigo-700">Rs {{ number_format($payment->amount, 2) }}</p><p class="mt-1 text-sm text-slate-500">Paid on {{ $payment->payment_date->format('d M Y') }}</p></div>
            </div>

            <dl class="mt-7 grid gap-x-8 gap-y-5 text-sm sm:grid-cols-2">
                <div><dt class="text-slate-500">Project</dt><dd class="mt-1 font-bold">{{ $payment->booking->project->name }}</dd></div>
                <div><dt class="text-slate-500">Booking number</dt><dd class="mt-1 font-mono font-bold">{{ $payment->booking->booking_number }}</dd></div>
                <div><dt class="text-slate-500">Plot package</dt><dd class="mt-1 font-bold">{{ $payment->booking->package->name }}</dd></div>
                <div><dt class="text-slate-500">Payment for</dt><dd class="mt-1 font-bold">{{ $payment->installment ? 'Installment month '.$payment->installment->installment_number : 'First payment' }}</dd></div>
                <div><dt class="text-slate-500">Payment method</dt><dd class="mt-1 font-bold">{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</dd></div>
                <div><dt class="text-slate-500">Transaction reference</dt><dd class="mt-1 font-mono font-bold">{{ $payment->transaction_reference ?: '—' }}</dd></div>
                @if($payment->verified_at)<div><dt class="text-slate-500">Verified on</dt><dd class="mt-1 font-bold">{{ $payment->verified_at->format('d M Y, h:i A') }}</dd></div>@endif
            </dl>
        </section>

        <footer class="border-t border-slate-200 bg-slate-50 px-7 py-5 text-center text-xs text-slate-500 sm:px-9">
            This computer-generated receipt does not require a signature. Keep the receipt number for future reference.
        </footer>
    </main>
</body>
</html>
