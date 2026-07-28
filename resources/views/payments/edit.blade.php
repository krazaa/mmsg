<x-app-layout>
    <x-slot name="header"><div><h2 class="text-xl font-semibold">Review payment</h2><p class="text-sm text-gray-500">Receipt {{ $payment->receipt_number }}</p></div></x-slot>
    <div class="py-5"><div class="mx-auto max-w-7xl px-4">
        @if($errors->any())<div class="mb-5 rounded-lg bg-red-100 p-4 text-red-800">{{ $errors->first() }}</div>@endif

        <div class="grid overflow-hidden rounded-2xl bg-white shadow-xl lg:grid-cols-2">
            <section class="flex min-h-[26rem] flex-col border-b bg-slate-950 p-4 lg:border-b-0 lg:border-e">
                <div class="mb-3 flex items-center justify-between"><div><h3 class="text-sm font-bold text-white">Customer payment proof</h3><p class="mt-0.5 text-[10px] text-slate-400">{{ $payment->proof_original_name ?: 'No file name available' }}</p></div>@if($payment->proof_path)<a target="_blank" href="{{ route('payments.proof', $payment) }}" class="rounded-lg bg-white/10 px-3 py-1.5 text-[10px] font-bold text-white hover:bg-white/20">Open full size</a>@endif</div>
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

            <section class="p-4 lg:p-5">
                <div class="mb-4"><div class="flex items-start justify-between gap-3"><div><div class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Amount submitted</div><div class="mt-0.5 text-2xl font-black text-gray-900">Rs {{ number_format($payment->amount, 2) }}</div></div><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $payment->status==='verified'?'bg-green-100 text-green-700':($payment->status==='pending'?'bg-amber-100 text-amber-700':'bg-red-100 text-red-700') }}">{{ $payment->status==='pending'?'Pending review':ucfirst($payment->status) }}</span></div></div>

                <div class="mb-4 grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-lg bg-gray-50 p-3"><span class="text-[10px] text-gray-500">Customer</span><b class="mt-0.5 block text-gray-900">{{ $payment->customer->name }}</b></div>
                    <div class="rounded-lg bg-gray-50 p-3"><span class="text-[10px] text-gray-500">Booking</span><b class="mt-0.5 block font-mono text-gray-900">{{ $payment->booking->booking_number }}</b></div>
                    <div class="rounded-lg bg-gray-50 p-3"><span class="text-[10px] text-gray-500">Applied to</span><b class="mt-0.5 block text-gray-900">{{ $payment->installment?'Month '.$payment->installment->installment_number:'Booking payment' }}</b></div>
                    <div class="rounded-lg bg-gray-50 p-3"><span class="text-[10px] text-gray-500">Submitted</span><b class="mt-0.5 block text-gray-900">{{ $payment->payment_date->format('d M Y, h:i A') }}</b></div>
                </div>

                <form
                    x-data="{
                        notes: @js(old('verification_notes', $payment->verification_notes ?? '')),
                        status: @js(old('status', $payment->status)),
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
                    class="space-y-3 border-t pt-4"
                >@csrf @method('PUT')
                    <div class="grid gap-3 sm:grid-cols-2"><label class="block text-xs font-semibold text-gray-700">Payment method<select name="payment_method" class="mt-1 w-full rounded-lg border-gray-300 py-2 text-sm">@foreach(['cash','bank_transfer','cheque','card','easypaisa','jazzcash','online_transfer','direct_deposit','crypto'] as $method)<option value="{{ $method }}" @selected(old('payment_method',$payment->payment_method)===$method)>{{ ucwords(str_replace('_',' ',$method)) }}</option>@endforeach</select></label>
                    <label class="block text-xs font-semibold text-gray-700">Transaction reference<input name="transaction_reference" value="{{ old('transaction_reference',$payment->transaction_reference) }}" class="mt-1 w-full rounded-lg border-gray-300 py-2 text-sm"></label></div>
                    <fieldset>
                        <legend class="text-sm font-semibold text-gray-700">Decision</legend>
                        <p class="mt-1 text-xs text-gray-500">Choose how this payment should be handled.</p>
                        <div class="mt-2 grid gap-2 {{ $payment->status === 'pending' ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }}">
                            @if($payment->status === 'pending')
                                <label class="cursor-pointer rounded-xl border-2 p-2.5 transition" :class="status === 'pending' ? 'border-amber-400 bg-amber-50 shadow-sm ring-2 ring-amber-100' : 'border-slate-200 bg-white hover:border-amber-200'">
                                    <input type="radio" name="status" value="pending" x-model="status" class="sr-only">
                                    <span class="flex items-start gap-2"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-amber-100 text-base text-amber-700">◷</span><span><b class="block text-xs text-slate-900">Keep under review</b><small class="mt-0.5 block text-[10px] leading-3 text-slate-500">No account changes.</small></span></span>
                                    <span x-show="status === 'pending'" class="mt-2 block text-[9px] font-black uppercase tracking-wider text-amber-700">✓ Selected</span>
                                </label>
                            @endif
                            <label class="cursor-pointer rounded-xl border-2 p-2.5 transition" :class="status === 'verified' ? 'border-emerald-500 bg-emerald-50 shadow-sm ring-2 ring-emerald-100' : 'border-slate-200 bg-white hover:border-emerald-200'">
                                <input type="radio" name="status" value="verified" x-model="status" class="sr-only">
                                <span class="flex items-start gap-2"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-emerald-100 text-base font-black text-emerald-700">✓</span><span><b class="block text-xs text-slate-900">Verify payment</b><small class="mt-0.5 block text-[10px] leading-3 text-slate-500">Approve and credit.</small></span></span>
                                <span x-show="status === 'verified'" class="mt-2 block text-[9px] font-black uppercase tracking-wider text-emerald-700">✓ Selected</span>
                            </label>
                            <label class="cursor-pointer rounded-xl border-2 p-2.5 transition" :class="status === 'reversed' ? 'border-rose-500 bg-rose-50 shadow-sm ring-2 ring-rose-100' : 'border-slate-200 bg-white hover:border-rose-200'">
                                <input type="radio" name="status" value="reversed" x-model="status" class="sr-only">
                                <span class="flex items-start gap-2"><span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-rose-100 text-base font-black text-rose-700">×</span><span><b class="block text-xs text-slate-900">Reject / reverse</b><small class="mt-0.5 block text-[10px] leading-3 text-slate-500">Reverse related entries.</small></span></span>
                                <span x-show="status === 'reversed'" class="mt-2 block text-[9px] font-black uppercase tracking-wider text-rose-700">✓ Selected</span>
                            </label>
                        </div>
                    </fieldset>
                    @if($payment->installment_schedule_id === null)
                        <label class="block text-sm font-semibold text-gray-700">
                            Customer file number
                            <input name="file_no" value="{{ old('file_no', $payment->customer->file_no) }}" :required="status === 'verified' && @js($payment->status === 'pending')" maxlength="50" placeholder="Enter the manually assigned file number" class="mt-1.5 w-full rounded-lg border-gray-300 font-mono uppercase">
                            <span class="mt-1 block text-xs font-normal text-gray-500">Required when approving the first payment. The number must be unique.</span>
                            <x-input-error :messages="$errors->get('file_no')" class="mt-2" />
                        </label>
                    @endif
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
                        <textarea id="verification_notes" name="verification_notes" x-model="notes" rows="3" placeholder="Click a quick note above or write your own review..." class="mt-2 w-full rounded-xl border-gray-300 text-sm leading-5 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        <div class="mt-1 flex items-center justify-between text-[10px] text-gray-400"><span>Clicking another note adds it on a new line.</span><button type="button" x-show="notes" @click="notes=''" class="font-bold text-red-500">Clear notes</button></div>
                    </div>
                    <p class="rounded-lg bg-red-50 p-3 text-xs leading-5 text-red-800">Rejecting or reversing removes this amount from the installment and reverses related commissions.</p>
                    <div class="flex flex-col gap-2 pt-1 sm:flex-row"><button :class="status === 'verified' ? 'bg-emerald-600 hover:bg-emerald-700' : (status === 'reversed' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-amber-500 hover:bg-amber-600')" class="rounded-lg px-5 py-2.5 text-sm font-bold text-white" x-text="status === 'verified' ? 'Verify payment' : (status === 'reversed' ? 'Reject / reverse payment' : 'Keep under review')">Save decision</button><a href="{{ route('payments.index') }}" class="rounded-lg border px-5 py-2.5 text-center text-sm font-semibold text-gray-700">Back to payments</a></div>
                </form>
            </section>
        </div>
    </div></div>
</x-app-layout>
