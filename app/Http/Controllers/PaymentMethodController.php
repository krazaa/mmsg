<?php

namespace App\Http\Controllers;

use App\Models\CommissionPayout;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::orderBy('sort_order')->orderBy('name')->get();

        return view('payment-methods.index', compact('methods'));
    }

    public function store(Request $request)
    {
        PaymentMethod::create($this->validated($request));

        return back()->with('success', 'Payment method created.');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $paymentMethod->update($this->validated($request, $paymentMethod));

        return back()->with('success', 'Payment method updated.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        $inUse = Payment::where('payment_method', $paymentMethod->code)->exists()
            || CommissionPayout::where('payment_method', $paymentMethod->code)->exists();

        if ($inUse) {
            return back()->withErrors(['payment_method' => 'This method has transaction history. Deactivate it instead.']);
        }

        $paymentMethod->delete();

        return back()->with('success', 'Payment method deleted.');
    }

    private function validated(Request $request, ?PaymentMethod $paymentMethod = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:30', 'regex:/^[a-z0-9_]+$/', Rule::unique('payment_methods')->ignore($paymentMethod)],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'account_title' => ['nullable', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:150'],
            'crypto_network' => ['nullable', 'string', 'max:100'],
            'wallet_address' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['required', 'integer', 'between:0,999'],
            'customer_portal' => ['nullable', 'boolean'],
            'status' => ['nullable', 'boolean'],
        ]);
        $data['customer_portal'] = $request->boolean('customer_portal');
        $data['status'] = $request->boolean('status');

        return $data;
    }
}
