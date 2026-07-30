<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold dark:text-white">Manage payments</h2></x-slot>
    <div class="py-5 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-5 px-3 sm:px-4">
            @if(session('success'))<div class="rounded bg-green-100 p-4 text-green-800">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="rounded bg-red-100 p-4 text-red-800">{{ $errors->first() }}</div>@endif

            <form class="grid gap-3 rounded-xl bg-white p-4 shadow sm:grid-cols-2 lg:grid-cols-4 dark:bg-slate-800">
                <input name="search" value="{{ request('search') }}" placeholder="Receipt, customer or reference" class="rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                <select name="project" class="rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white"><option value="">All projects</option>@foreach($projects as $project)<option value="{{ $project->id }}" @selected(request('project')==$project->id)>{{ $project->name }}</option>@endforeach</select>
                <select name="status" class="rounded-md border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white"><option value="">All statuses</option>@foreach(['pending','verified','reversed'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>@endforeach</select>
                <button class="rounded bg-indigo-600 px-4 py-2.5 font-bold text-white">Filter</button>
            </form>

            <div class="rounded-xl bg-white shadow dark:bg-slate-800">
                <table class="admin-responsive-table w-full text-sm">
                    <thead class="bg-gray-50 text-left dark:bg-slate-900 dark:text-slate-300"><tr><th class="p-4">Receipt</th><th>Customer / Booking</th><th>Project</th><th>Installment</th><th>Amount</th><th>Method</th><th>Date</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr class="border-t dark:border-slate-700 dark:text-white">
                                <td data-label="Receipt" class="p-4 font-semibold">{{ $payment->receipt_number }}</td>
                                <td data-label="Customer"><span>{{ $payment->customer->name }}<small class="block text-xs"><a class="text-indigo-600 dark:text-indigo-300" href="{{ route('bookings.show',$payment->booking) }}">{{ $payment->booking->booking_number }}</a></small></span></td>
                                <td data-label="Project">{{ $payment->booking->project->name }}</td>
                                <td data-label="Applied to">{{ $payment->installment?'Month '.$payment->installment->installment_number:'Booking payment' }}</td>
                                <td data-label="Amount" class="font-semibold">Rs {{ number_format($payment->amount) }}</td>
                                <td data-label="Method">{{ ucwords(str_replace('_',' ',$payment->payment_method)) }}</td>
                                <td data-label="Date">{{ $payment->payment_date->format('d M Y') }}</td>
                                <td data-label="Status"><span class="rounded-full px-2 py-1 {{ $payment->status==='verified'?'bg-green-100 text-green-700':($payment->status==='pending'?'bg-amber-100 text-amber-700':'bg-red-100 text-red-700') }}">{{ ucfirst($payment->status) }}</span></td>
                                <td data-label="Action"><a href="{{ route('payments.edit',$payment) }}" class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 font-bold text-white">Manage</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="p-10 text-center">No payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $payments->links() }}
        </div>
    </div>
</x-app-layout>
