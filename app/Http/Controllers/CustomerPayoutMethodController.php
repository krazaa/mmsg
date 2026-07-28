<?php

namespace App\Http\Controllers;

use App\Models\CustomerPayoutMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerPayoutMethodController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureCustomer($request);

        return view('customer-payout-methods.index', [
            'methods' => $request->user()->payoutMethods()->orderByDesc('is_default')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureCustomer($request);
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $data): void {
            $hasMethods = $request->user()->payoutMethods()->lockForUpdate()->exists();
            $makeDefault = ! $hasMethods || (bool) ($data['is_default'] ?? false);

            if ($makeDefault) {
                $request->user()->payoutMethods()->update(['is_default' => false]);
            }

            $data['is_default'] = $makeDefault;
            $request->user()->payoutMethods()->create($data);
        });

        return back()->with('success', 'Payout method added successfully.');
    }

    public function update(Request $request, CustomerPayoutMethod $payoutMethod): RedirectResponse
    {
        $this->ensureOwner($request, $payoutMethod);
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $payoutMethod, $data): void {
            if ((bool) ($data['is_default'] ?? false)) {
                $request->user()->payoutMethods()->whereKeyNot($payoutMethod->id)->update(['is_default' => false]);
            }

            $payoutMethod->update($data);
        });

        return back()->with('success', 'Payout method updated successfully.');
    }

    public function makeDefault(Request $request, CustomerPayoutMethod $payoutMethod): RedirectResponse
    {
        $this->ensureOwner($request, $payoutMethod);

        DB::transaction(function () use ($request, $payoutMethod): void {
            $request->user()->payoutMethods()->update(['is_default' => false]);
            $payoutMethod->update(['is_default' => true]);
        });

        return back()->with('success', 'Default payout method changed.');
    }

    public function destroy(Request $request, CustomerPayoutMethod $payoutMethod): RedirectResponse
    {
        $this->ensureOwner($request, $payoutMethod);

        DB::transaction(function () use ($request, $payoutMethod): void {
            $wasDefault = $payoutMethod->is_default;
            $payoutMethod->delete();

            if ($wasDefault) {
                $request->user()->payoutMethods()->oldest()->first()?->update(['is_default' => true]);
            }
        });

        return back()->with('success', 'Payout method removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'payment_method' => ['required', Rule::in(['bank_transfer', 'raast', 'easypaisa', 'jazzcash', 'crypto'])],
            'account_title' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:150'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'network' => ['nullable', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function ensureCustomer(Request $request): void
    {
        abort_unless($request->user()->role === 'customer', 403);
    }

    private function ensureOwner(Request $request, CustomerPayoutMethod $payoutMethod): void
    {
        $this->ensureCustomer($request);
        abort_unless($payoutMethod->customer_id === $request->user()->id, 404);
    }
}
