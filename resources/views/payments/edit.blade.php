<x-app-layout>
    <x-slot name="header"><div><h2 class="text-xl font-semibold">Review payment</h2><p class="text-sm text-gray-500">Receipt {{ $payment->receipt_number }}</p></div></x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl px-4">
        @if($errors->any())<div class="mb-5 rounded-lg bg-red-100 p-4 text-red-800">{{ $errors->first() }}</div>@endif

        <div class="grid overflow-hidden rounded-2xl bg-white shadow-xl lg:grid-cols-2">
            <section class="flex min-h-[32rem] flex-col border-b bg-slate-950 p-5 lg:border-b-0 lg:border-e lg:p-6">
                <div class="mb-4 flex items-center justify-between"><div><h3 class="font-bold text-white">Customer payment proof</h3><p class="mt-1 text-xs text-slate-400">{{ $payment->proof_original_name ?: 'No file name available' }}</p></div>@if($payment->proof_path)<a target="_blank" href="{{ route('payments.proof', $payment) }}" class="rounded-lg bg-white/10 px-3 py-2 text-xs font-bold text-white hover:bg-white/20">Open full size</a>@endif</div>
                <div class="flex flex-1 items-start justify-center overflow-hidden rounded-xl border border-white/10 bg-black/30">
                    @if($payment->proof_path && str_starts_with($proofMime ?? '', 'image/'))
                        <a target="_blank" href="{{ route('payments.proof', $payment) }}" class="flex h-full w-full items-start justify-center"><img data-testid="payment-proof-preview" src="{{ route('payments.proof', $payment) }}" alt="Customer payment proof" class="max-h-[70vh] max-w-full object-contain object-top"></a>
                    @elseif($payment->proof_path && ($proofMime ?? '') === 'application/pdf')
                        <iframe data-testid="payment-proof-preview" src="{{ route('payments.proof', $payment) }}" title="Customer payment proof" class="h-[70vh] min-h-[32rem] w-full bg-white"></iframe>
                    @elseif($payment->proof_path)
                        <a target="_blank" href="{{ route('payments.proof', $payment) }}" class="rounded-xl bg-white/10 px-6 py-4 font-semibold text-white">Open payment proof</a>
                    @else
                        <div class="px-6 text-center"><div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white/10 text-2xl text-slate-300">—</div><div class="mt-4 font-semibold text-slate-300">No payment proof uploaded</div></div>
                    @endif
                </div>
            </section>

            <section class="p-5 lg:p-7">
                <div class="mb-6"><div class="flex items-start justify-between gap-3"><div><div class="text-xs font-bold uppercase tracking-wider text-gray-400">Amount submitted</div><div class="mt-1 text-3xl font-black text-gray-900">Rs {{ number_format($payment->amount, 2) }}</div></div><span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $payment->status==='verified'?'bg-green-100 text-green-700':($payment->status==='pending'?'bg-amber-100 text-amber-700':'bg-red-100 text-red-700') }}">{{ $payment->status==='pending'?'Pending review':ucfirst($payment->status) }}</span></div></div>

                <div class="mb-6 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-gray-50 p-4"><span class="text-xs text-gray-500">Customer</span><b class="mt-1 block text-gray-900">{{ $payment->customer->name }}</b></div>
                    <div class="rounded-xl bg-gray-50 p-4"><span class="text-xs text-gray-500">Booking</span><b class="mt-1 block font-mono text-gray-900">{{ $payment->booking->booking_number }}</b></div>
                    <div class="rounded-xl bg-gray-50 p-4"><span class="text-xs text-gray-500">Applied to</span><b class="mt-1 block text-gray-900">{{ $payment->installment?'Month '.$payment->installment->installment_number:'Booking payment' }}</b></div>
                    <div class="rounded-xl bg-gray-50 p-4"><span class="text-xs text-gray-500">Submitted</span><b class="mt-1 block text-gray-900">{{ $payment->payment_date->format('d M Y, h:i A') }}</b></div>
                </div>

                <form
                    x-data="{
                        notes: @js(old('verification_notes', $payment->verification_notes ?? '')),
                        addNote(note) {
                            if (!this.notes.trim()) {
                                this.notes = note;
                                return;
                            }
                            if (!this.notes.includes(note)) {
                                this.notes = this.notes.trim() + '\n' + note;
                            }
                        }
                    }"
                    method="POST"
                    action="{{ route('payments.update',$payment) }}"
                    class="space-y-4 border-t pt-6"
                >@csrf @method('PUT')
                    <label class="block text-sm font-semibold text-gray-700">Payment method<select name="payment_method" class="mt-1.5 w-full rounded-lg border-gray-300">@foreach(['cash','bank_transfer','cheque','card','easypaisa','jazzcash','online_transfer','direct_deposit','crypto'] as $method)<option value="{{ $method }}" @selected(old('payment_method',$payment->payment_method)===$method)>{{ ucwords(str_replace('_',' ',$method)) }}</option>@endforeach</select></label>
                    <label class="block text-sm font-semibold text-gray-700">Transaction reference<input name="transaction_reference" value="{{ old('transaction_reference',$payment->transaction_reference) }}" class="mt-1.5 w-full rounded-lg border-gray-300"></label>
                    <label class="block text-sm font-semibold text-gray-700">Decision<select name="status" class="mt-1.5 w-full rounded-lg border-gray-300">@if($payment->status==='pending')<option value="pending" @selected(old('status',$payment->status)==='pending')>Keep under review</option>@endif<option value="verified" @selected(old('status',$payment->status)==='verified')>Verify payment</option><option value="reversed" @selected(old('status',$payment->status)==='reversed')>Reject / reverse payment</option></select></label>
                    <div>
                        <div class="flex items-center justify-between gap-3"><label for="verification_notes" class="text-sm font-semibold text-gray-700">Review notes</label><span class="text-[10px] font-bold uppercase tracking-wider text-indigo-500">Quick notes</span></div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach([
                                'Payment proof verified and transaction details matched.',
                                'Payment received and approved successfully.',
                                'Payment proof is unclear. Please upload a clearer image.',
                                'Transaction details do not match. Payment rejected.',
                            ] as $index => $quickNote)
                                <button
                                    type="button"
                                    @click="addNote(@js($quickNote))"
                                    class="group inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-left text-[11px] font-bold transition {{ $index < 2 ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100' }}"
                                >
                                    <span class="text-sm leading-none">{{ $index < 2 ? '✓' : '!' }}</span>
                                    {{ $quickNote }}
                                    <span class="ml-0.5 opacity-40 transition group-hover:opacity-100">＋</span>
                                </button>
                            @endforeach
                        </div>
                        <textarea id="verification_notes" name="verification_notes" x-model="notes" rows="4" placeholder="Click a quick note above or write your own review..." class="mt-3 w-full rounded-xl border-gray-300 leading-6 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        <div class="mt-1 flex items-center justify-between text-[10px] text-gray-400"><span>Clicking another note adds it on a new line.</span><button type="button" x-show="notes" @click="notes=''" class="font-bold text-red-500">Clear notes</button></div>
                    </div>
                    <p class="rounded-lg bg-red-50 p-3 text-xs leading-5 text-red-800">Rejecting or reversing removes this amount from the installment and reverses related commissions.</p>
                    <div class="flex flex-col gap-3 pt-1 sm:flex-row"><button class="rounded-lg bg-indigo-600 px-6 py-3 font-bold text-white hover:bg-indigo-700">Save decision</button><a href="{{ route('payments.index') }}" class="rounded-lg border px-6 py-3 text-center font-semibold text-gray-700">Back to payments</a></div>
                </form>
            </section>
        </div>
    </div></div>
</x-app-layout>
