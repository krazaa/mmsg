@csrf
@if(isset($package)) @method('PUT') @endif
@php
    $defaultBalloons = isset($package)
        ? $package->balloonPayments()
        : [];
    $initialBalloons = old('balloons', $defaultBalloons);
    $initialPlan = old('payment_plan_options', isset($package) ? $package->payment_plan_options : '');
@endphp

<div class="grid gap-5 sm:grid-cols-2" x-data="{
    plan: @js($initialPlan),
    months: @js(old('months', $package->months ?? '')),
    booking: @js(old('booking_amount', $package->booking_amount ?? '')),
    cashPrice: @js(old('cash_price', $package->cash_price ?? '')),
    monthly: @js(old('monthly_amount', $package->monthly_amount ?? '')),
    balloons: @js(array_values($initialBalloons)),
    addBalloon() { this.balloons.push({ month: '', amount: '' }) },
    removeBalloon(index) { this.balloons.splice(index, 1) },
    hasInvalidBalloonMonths() {
        return this.balloons.some(item => Number(item.month) > Number(this.months))
    },
    total() {
        return Number(this.booking || 0) + (Number(this.months || 0) * Number(this.monthly || 0))
            + this.balloons.reduce((sum, item) => sum + Number(item.amount || 0), 0)
    }
}">
    <label class="block text-sm font-medium text-gray-700">Project
        <select name="project_id" required class="mt-1 w-full rounded-md border-gray-300">
            @foreach($projects as $project)<option value="{{ $project->id }}" @selected(old('project_id', $package->project_id ?? $selectedProject ?? '') == $project->id)>{{ $project->name }}</option>@endforeach
        </select>
    </label>
    <label class="block text-sm font-medium text-gray-700">Package name
        <input name="name" value="{{ old('name', $package->name ?? '') }}" required class="mt-1 w-full rounded-md border-gray-300" placeholder="e.g. 5 Marla">
    </label>
    <label class="block text-sm font-medium text-gray-700">Plot size (marla)
        <input type="number" step="0.01" min="0.01" name="size_marla" value="{{ old('size_marla', $package->size_marla ?? '') }}" required class="mt-1 w-full rounded-md border-gray-300">
    </label>
    <label x-show="plan !== 'cash'" x-cloak class="block text-sm font-medium text-gray-700">First payment (Rs)
        <input type="number" step="0.01" min="0" name="booking_amount" x-model.number="booking" value="{{ old('booking_amount', $package->booking_amount ?? '') }}" :required="plan !== 'cash'" :disabled="plan === 'cash'" class="mt-1 w-full rounded-md border-gray-300">
    </label>
    <label class="block text-sm font-medium text-gray-700">Cash price (Rs)
        <input type="number" step="0.01" min="0.01" name="cash_price" x-model.number="cashPrice" value="{{ old('cash_price', $package->cash_price ?? '') }}" class="mt-1 w-full rounded-md border-gray-300" placeholder="Leave blank to disable cash plan">
        <span class="mt-1 block text-xs font-normal text-gray-500">Optional. When entered, it must differ from the installment price.</span>
    </label>
    <label class="block text-sm font-medium text-gray-700">Available payment plans
        <select name="payment_plan_options" x-model="plan" required class="mt-1 w-full rounded-md border-gray-300">
            <option value="" disabled>Select payment plan</option>
            <option value="both" @selected($initialPlan === 'both')>Cash & Installments</option>
            <option value="cash" @selected($initialPlan === 'cash')>Cash Only</option>
            <option value="installment" @selected($initialPlan === 'installment')>Installments Only</option>
        </select>
        <span class="mt-1 block text-xs font-normal text-gray-500">Choose which plans customers can select for this package.</span>
    </label>
    <label class="block sm:col-span-2 text-sm font-medium text-gray-700">Welcome page offer <span class="font-normal text-gray-400">(optional)</span>
        <textarea name="welcome_offer" rows="3" maxlength="500" class="mt-1 w-full rounded-md border-gray-300" placeholder="Example: Book this month and receive free processing plus priority allotment.">{{ old('welcome_offer', $package->welcome_offer ?? '') }}</textarea>
        <span class="mt-1 block text-xs font-normal text-gray-500">Active package offers appear randomly beneath the featured project on the public welcome page. Leave blank to hide this package from the offer rotation.</span>
    </label>
    <label x-show="plan !== 'cash'" x-cloak class="block text-sm font-medium text-gray-700">Monthly installment (Rs)
        <input type="number" step="0.01" min="0" name="monthly_amount" x-model.number="monthly" value="{{ old('monthly_amount', $package->monthly_amount ?? '') }}" :required="plan !== 'cash'" :disabled="plan === 'cash'" class="mt-1 w-full rounded-md border-gray-300">
    </label>
    <label x-show="plan !== 'cash'" x-cloak class="block text-sm font-medium text-gray-700">Duration (months)
        <input type="number" min="1" max="60" step="1" name="months" x-model.number="months" value="{{ old('months', $package->months ?? '') }}" :required="plan !== 'cash'" :disabled="plan === 'cash'" class="mt-1 w-full rounded-md border-gray-300">
        <span class="mt-1 block text-xs font-normal text-gray-500">Enter any duration from 1 to 60 months.</span>
    </label>
    <div x-show="plan !== 'cash'" x-cloak class="sm:col-span-2 rounded-lg border border-gray-200 p-4">
        <div class="mb-3 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Balloon payments</h3>
                <p class="text-xs text-gray-500">Add payments at any month within the package duration.</p>
            </div>
            <button type="button" @click="addBalloon()" class="rounded-md bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">+ Add payment</button>
        </div>
        <div class="space-y-3">
            <template x-for="(balloon, index) in balloons" :key="index">
                <div class="grid grid-cols-[1fr_1fr_auto] items-end gap-3">
                    <label class="block text-sm font-medium text-gray-700">Month
                        <input type="number" min="1" step="1" :max="months" :name="`balloons[${index}][month]`" x-model.number="balloon.month" :required="plan !== 'cash'" :disabled="plan === 'cash'" class="mt-1 w-full rounded-md border-gray-300" :class="Number(balloon.month) > Number(months) ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''">
                        <span x-show="Number(balloon.month) > Number(months)" class="mt-1 block text-xs text-red-600" x-text="`Month must not exceed the ${months}-month duration.`"></span>
                    </label>
                    <label class="block text-sm font-medium text-gray-700">Amount (Rs)
                        <input type="number" min="0.01" step="0.01" :name="`balloons[${index}][amount]`" x-model.number="balloon.amount" :required="plan !== 'cash'" :disabled="plan === 'cash'" class="mt-1 w-full rounded-md border-gray-300">
                    </label>
                    <button type="button" @click="removeBalloon(index)" class="mb-0.5 rounded-md border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50" aria-label="Remove balloon payment">Remove</button>
                </div>
            </template>
            <p x-show="balloons.length === 0" class="rounded-md bg-gray-50 p-3 text-sm text-gray-500">No balloon payments. Use “Add payment” if this plan needs one.</p>
        </div>
    </div>
    <label x-show="plan !== 'cash'" x-cloak class="block rounded-lg bg-indigo-50 p-4 text-sm font-semibold text-indigo-900">Installment price (calculated)
        <div class="mt-2 text-2xl font-black" x-text="'Rs ' + total().toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></div>
        <span class="mt-1 block text-xs font-normal text-indigo-600">Updates automatically from the payment plan above.</span>
    </label>
    <div class="rounded-lg bg-emerald-50 p-4 text-sm font-semibold text-emerald-900">Cash price
        <div class="mt-2 text-2xl font-black" x-text="cashPrice ? 'Rs ' + Number(cashPrice).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'Not configured'"></div>
        <span class="mt-1 block text-xs font-normal text-emerald-700" x-text="cashPrice ? 'Paid in full without an installment schedule.' : 'Customers will only see the installment plan.'"></span>
    </div>
    <label class="flex items-center gap-2 self-end pb-3 text-sm font-medium text-gray-700">
        <input type="hidden" name="status" value="0"><input type="checkbox" name="status" value="1" @checked(old('status', $package->status ?? true)) class="rounded border-gray-300 text-indigo-600">Active and available for booking
    </label>
</div>
<div class="mt-6 flex gap-3"><button :disabled="plan !== 'cash' && hasInvalidBalloonMonths()" class="rounded-md bg-indigo-600 px-5 py-2.5 font-semibold text-white disabled:cursor-not-allowed disabled:bg-gray-400">{{ isset($package) ? 'Save changes' : 'Create package' }}</button><a href="{{ route('packages.index', ['project' => $package->project_id ?? $selectedProject ?? null]) }}" class="rounded-md border px-5 py-2.5 text-gray-700">Cancel</a></div>
