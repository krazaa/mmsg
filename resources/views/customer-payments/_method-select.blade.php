<div x-data="{ selected: @js($paymentMethods->first()?->code), methods: @js($paymentMethods->keyBy('code')) }">
    <label class="text-sm font-semibold text-gray-700">Payment method
        <select name="payment_method" x-model="selected" required class="mt-1.5 w-full rounded-lg border-gray-300 bg-white">
            @foreach($paymentMethods as $method)<option value="{{ $method->code }}">{{ $method->name }}</option>@endforeach
        </select>
    </label>
    <template x-if="selected && methods[selected]">
        <div class="mt-2 rounded-xl border border-indigo-200 bg-white p-3 text-xs shadow-sm">
            <div class="mb-2 font-black uppercase tracking-wider text-indigo-600" x-text="methods[selected].name + ' details'"></div>
            <dl class="space-y-1.5 text-gray-600">
                <template x-if="methods[selected].bank_name"><div class="flex justify-between gap-3"><dt>Bank / provider</dt><dd class="text-right font-bold text-gray-900" x-text="methods[selected].bank_name"></dd></div></template>
                <template x-if="methods[selected].account_title"><div class="flex justify-between gap-3"><dt>Account title</dt><dd class="text-right font-bold text-gray-900" x-text="methods[selected].account_title"></dd></div></template>
                <template x-if="methods[selected].account_number"><div class="flex justify-between gap-3"><dt>Account number</dt><dd class="break-all text-right font-mono font-bold text-gray-900" x-text="methods[selected].account_number"></dd></div></template>
                <template x-if="methods[selected].crypto_network"><div class="flex justify-between gap-3"><dt>Network</dt><dd class="text-right font-bold text-gray-900" x-text="methods[selected].crypto_network"></dd></div></template>
                <template x-if="methods[selected].wallet_address"><div><dt class="mb-1">Wallet address</dt><dd class="break-all rounded-lg bg-gray-50 p-2 font-mono font-bold text-gray-900" x-text="methods[selected].wallet_address"></dd></div></template>
                <template x-if="methods[selected].instructions"><div class="border-t border-gray-100 pt-2 leading-5 text-gray-500" x-text="methods[selected].instructions"></div></template>
            </dl>
        </div>
    </template>
</div>
