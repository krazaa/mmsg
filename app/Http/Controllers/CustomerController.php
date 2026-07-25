<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\Customer;
use App\Models\Referral;
use App\Models\User;
use App\Services\ReferralNetworkService;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CustomerController extends Controller
{
    public function __construct(private readonly ReferralNetworkService $referralNetwork) {}

    public function index(Request $request)
    {
        $customers = Customer::with(['referralAgent.customer', 'user.referral.sponsor'])->withCount(['bookings', 'payments'])
            ->when($request->string('type')->toString() === 'agent', fn ($query) => $query->whereNotNull('referral_agent_id'))
            ->when($request->filled('referral_code'), fn ($query) => $query->where('referral_code', 'like', '%'.$request->string('referral_code')->trim().'%'))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($q) => $q->where('name', 'like', $search)->orWhere('file_no', 'like', $search)->orWhere('cnic', 'like', $search)->orWhere('phone', 'like', $search)->orWhere('referral_code', 'like', $search));
            })->latest()->paginate(25)->withQueryString();
        $customers->getCollection()->each(function ($customer) {
            $customer->payable_commission = $customer->user
                ? $customer->user->commissions()->where('status', 'earned')->sum('amount') : 0;
        });

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create', ['agents' => $this->agents()]);
    }

    public function show(Request $request, Customer $customer)
    {
        $customer->loadMissing('user.referral.sponsor');
        $payments = $customer->payments()->with(['booking.project', 'booking.package', 'installment'])
            ->whereHas('booking')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest('payment_date')->paginate(25)->withQueryString();
        $verifiedTotal = $customer->payments()->whereHas('booking')->where('status', 'verified')->sum('amount');
        $reversedTotal = $customer->payments()->whereHas('booking')->where('status', 'reversed')->sum('amount');
        $bookingTotal = $customer->bookings()->whereNot('status', 'cancelled')->sum('total_price');
        $bookings = $customer->bookings()->with(['project', 'package', 'agent'])->latest()->get();
        $agent = $customer->user;
        $agentSummary = $agent ? Commission::where('beneficiary_id', $agent->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'earned' THEN amount ELSE 0 END), 0) as payable")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as paid")
            ->selectRaw('COALESCE(SUM(amount), 0) as lifetime')->first() : null;
        $agentCommissions = $agent ? Commission::with(['payment', 'booking.customer'])->where('beneficiary_id', $agent->id)->latest()->limit(25)->get() : collect();
        $agentPayouts = $agent ? CommissionPayout::where('agent_id', $agent->id)->latest('paid_at')->limit(25)->get() : collect();
        $downline = $this->referralNetwork->downline($customer->id);
        $downlineCounts = $downline->countBy('level');
        $downlineTree = $this->referralNetwork->tree($customer->id);

        return view('customers.show', compact('customer', 'payments', 'verifiedTotal', 'reversedTotal', 'bookingTotal', 'bookings', 'agent', 'agentSummary', 'agentCommissions', 'agentPayouts', 'downline', 'downlineCounts', 'downlineTree'));
    }

    public function downlinePage(Customer $customer)
    {
        $downline = $this->referralNetwork->downline($customer->id);
        $downlineCounts = $downline->countBy('level');
        $downlineTree = $this->referralNetwork->tree($customer->id);
        $payableCommission = $customer->commissions()->where('status', 'earned')->sum('amount');

        return view('customers.downline', compact('customer', 'downline', 'downlineCounts', 'downlineTree', 'payableCommission'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $referrerId = $this->referrerId($data['referred_by_code'] ?? null);
        $customer = DB::transaction(function () use ($data, $referrerId) {
            unset($data['referred_by_code']);
            $customer = Customer::create($data + ['referral_agent_id' => $referrerId, 'email_verified_at' => now()]);
            $this->syncReferral($customer);
            $this->syncCustomerAccess($customer);

            return $customer;
        });

        return redirect()->route('customers.index')->with('success', 'Customer created.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', ['customer' => $customer, 'agents' => $this->agents($customer->referral_agent_id)]);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $this->validated($request, $customer);
        $referrerId = $this->referrerId($data['referred_by_code'] ?? null);
        if ($referrerId === $customer->id) {
            throw ValidationException::withMessages(['referred_by_code' => 'A customer cannot use their own referral code.']);
        }
        DB::transaction(function () use ($customer, $data, $referrerId) {
            $password = $data['password'] ?? null;
            unset($data['password'], $data['referred_by_code']);
            if ($password) {
                $data['password'] = $password;
            }
            $customer->update($data + ['referral_agent_id' => $referrerId]);
            $this->syncReferral($customer);
            $this->syncCustomerAccess($customer);
        });

        return redirect()->route('customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->bookings()->exists() || $customer->payments()->exists()) {
            return back()->withErrors(['customer' => 'Customers with bookings or payments cannot be deleted. Deactivate them instead.']);
        }
        $customer->delete();

        return back()->with('success', 'Customer deleted.');
    }

    private function validated(Request $request, ?Customer $customer = null): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'father_name' => ['nullable', 'string', 'max:255'],
            'file_no' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($customer)],
            'cnic' => ['nullable', 'string', 'max:15', Rule::unique('users')->ignore($customer)], 'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($customer)],
            'password' => [$customer ? 'nullable' : 'required', 'nullable', 'string', 'min:8'],
            'address' => ['nullable', 'string'], 'status' => ['nullable', 'boolean'],
            'referred_by_code' => ['nullable', 'string', 'max:30', Rule::exists('users', 'referral_code')->where('role', 'customer')],
        ]);
    }

    private function referrerId(?string $code): int
    {
        if (! $code) {
            return (int) User::where('email', 'direct-sales@mmsgroup.pk')->value('id');
        }

        $referrer = Customer::where('referral_code', $code)->firstOrFail();

        return $referrer->id;
    }

    private function syncReferral(Customer $customer): void
    {
        Referral::updateOrCreate(['user_id' => $customer->id], ['sponsor_id' => $customer->referral_agent_id]);
    }

    private function syncCustomerAccess(Customer $customer): void
    {
        foreach (Permissions::customer() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        $role->syncPermissions(Permissions::customer());
        User::findOrFail($customer->id)->syncRoles($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function agents(?int $include = null)
    {
        return User::where('role', 'agent')->where(fn ($query) => $query->where('status', true)->when($include, fn ($q) => $q->orWhere('id', $include)))->orderBy('name')->get();
    }
}
