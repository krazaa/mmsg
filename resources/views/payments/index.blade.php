<x-app-layout>
    <div class="py-5 sm:py-8">
        <div class="mx-auto max-w-7xl space-y-5 px-3 sm:px-4">
            <section class="admin-command-card admin-command-summary p-5 text-white shadow-2xl sm:p-8">
                <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full"></div>
                <div class="relative"><div class="admin-command-kicker text-[10px] font-black uppercase tracking-[.22em]">Payment management</div><h1 class="mt-2 text-3xl font-black sm:text-4xl">Payments</h1><p class="mt-2 text-sm text-emerald-100/80">Review customer receipts, verify transactions and monitor collected funds.</p></div>
                <div class="admin-command-stat-grid mt-4 grid grid-cols-2 gap-2.5 lg:grid-cols-4">@foreach([['All payments',$summary['total'],'Total receipts','bg-emerald-400',false],['Pending review',$summary['pending'],'Awaiting verification','bg-amber-400',false],['Verified',$summary['verified'],'Approved receipts','bg-cyan-400',false],['Amount received',$summary['received'],'Verified funds','bg-violet-400',true]] as [$label,$value,$hint,$accent,$currency])<div class="admin-command-stat rounded-2xl border p-3.5 backdrop-blur sm:p-4"><div class="flex items-center gap-2 text-[9px] font-black uppercase tracking-wider text-emerald-200 sm:text-[10px]"><span class="h-2 w-2 rounded-full {{ $accent }}"></span>{{ $label }}</div><div class="mt-2 break-words text-xl font-black sm:text-3xl">{{ $currency ? 'Rs '.number_format($value) : number_format($value) }}</div><div class="mt-1 text-[10px] text-emerald-100/65 sm:text-xs">{{ $hint }}</div></div>@endforeach</div>
            </section>
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
