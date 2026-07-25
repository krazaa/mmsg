<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AgentCommissionPayoutController extends Controller
{
    public function store(Request $request, User $agent)
    {
        abort_unless(in_array($agent->role, ['agent', 'customer'], true), 404);
        $data = $request->validate([
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'cheque', 'easypaisa', 'jazzcash', 'crypto'])],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payout = DB::transaction(function () use ($agent, $data, $request) {
            $commissions = Commission::where('beneficiary_id', $agent->id)->where('status', 'earned')->lockForUpdate()->get();
            if ($commissions->isEmpty()) {
                throw ValidationException::withMessages(['commission' => 'This agent has no payable commission.']);
            }
            $payout = CommissionPayout::create([
                'payout_number' => 'PAY-'.now()->format('ymdHis').'-'.random_int(100, 999),
                'agent_id' => $agent->id,
                'amount' => $commissions->sum('amount'),
                'payment_method' => $data['payment_method'],
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'paid_by' => $request->user()->id,
                'paid_at' => now(),
            ]);
            $commissions->each(fn (Commission $commission) => $commission->update(['status' => 'paid', 'commission_payout_id' => $payout->id]));

            return $payout;
        });

        if ($request->input('return_to') === 'customer' && $agent->customer) {
            return redirect()->route('customers.show', $agent->id)->with('success', 'Commission payout '.$payout->payout_number.' recorded.');
        }

        return redirect()->route('agents.show', $agent)->with('success', 'Commission payout '.$payout->payout_number.' recorded.');
    }
}
