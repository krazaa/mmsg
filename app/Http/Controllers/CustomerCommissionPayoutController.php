<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerCommissionPayoutController extends Controller
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'cheque', 'easypaisa', 'jazzcash', 'crypto'])],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payout = DB::transaction(function () use ($customer, $data, $request): CommissionPayout {
            $commissions = Commission::where('beneficiary_id', $customer->id)
                ->where('status', 'earned')
                ->lockForUpdate()
                ->get();

            if ($commissions->isEmpty()) {
                throw ValidationException::withMessages(['commission' => 'This customer has no payable commission.']);
            }

            $payout = CommissionPayout::create([
                'payout_number' => 'PAY-'.now()->format('ymdHis').'-'.random_int(100, 999),
                'agent_id' => $customer->id,
                'amount' => $commissions->sum('amount'),
                'payment_method' => $data['payment_method'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'paid_by' => $request->user()->id,
                'paid_at' => now(),
            ]);

            $commissions->each(fn (Commission $commission) => $commission->update([
                'status' => 'paid',
                'commission_payout_id' => $payout->id,
            ]));

            return $payout;
        });

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Commission payout '.$payout->payout_number.' recorded.');
    }
}
