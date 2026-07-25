<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('customers.index', ['type' => 'agent']);

        /*
        $agents = User::where('role', 'agent')->with('referral')->withCount('agentBookings')
            ->withSum(['commissions as payable_commission' => fn ($query) => $query->where('status', 'earned')], 'amount')
            ->withSum(['commissions as reversed_commission' => fn ($query) => $query->where('status', 'reversed')], 'amount')
            ->withSum('commissions as lifetime_commission', 'amount')
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($i) => $i->where('name', 'like', '%'.$request->search.'%')->orWhere('email', 'like', '%'.$request->search.'%')->orWhere('referral_code', 'like', '%'.$request->search.'%')))
            ->latest()->paginate(25)->withQueryString();

        return view('agents.index', compact('agents')); */
    }

    public function create()
    {
        return view('agents.create', ['agents' => User::where('role', 'agent')->where('status', true)->orderBy('name')->get()]);
    }

    public function show(Request $request, User $agent)
    {
        abort_unless($agent->role === 'agent', 404);
        $commissions = Commission::with(['payment', 'booking.customer', 'booking.package', 'booking.project'])
            ->where('beneficiary_id', $agent->id)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->integer('level'), fn ($query, $level) => $query->where('level', $level))
            ->latest()->paginate(25)->withQueryString();
        $summary = Commission::where('beneficiary_id', $agent->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'earned' THEN amount ELSE 0 END), 0) as payable")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'reversed' THEN amount ELSE 0 END), 0) as reversed")
            ->selectRaw('COALESCE(SUM(amount), 0) as lifetime')->first();
        $upline = $this->upline($agent);
        $downline = $this->downline($agent->id);
        $payouts = CommissionPayout::where('agent_id', $agent->id)->latest('paid_at')->limit(25)->get();

        return view('agents.show', compact('agent', 'commissions', 'summary', 'upline', 'downline', 'payouts'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data) {
            $agent = User::create(['name' => $data['name'], 'email' => $data['email'], 'referral_code' => $data['referral_code'] ?? null, 'phone' => $data['phone'] ?? null, 'password' => $data['password'], 'role' => 'agent', 'status' => (bool) ($data['status'] ?? false), 'email_verified_at' => now()]);
            if (! $agent->referral_code) {
                $agent->update(['referral_code' => 'AGT-'.str_pad((string) $agent->id, 6, '0', STR_PAD_LEFT)]);
            }
            Referral::create(['user_id' => $agent->id, 'sponsor_id' => $data['sponsor_id'] ?? null]);
        });

        return redirect()->route('agents.index')->with('success', 'Agent created.');
    }

    public function edit(User $agent)
    {
        abort_unless($agent->role === 'agent', 404);

        return view('agents.edit', ['agent' => $agent, 'agents' => User::where('role', 'agent')->whereKeyNot($agent->id)->orderBy('name')->get()]);
    }

    public function update(Request $request, User $agent)
    {
        abort_unless($agent->role === 'agent', 404);
        $data = $this->validated($request, $agent);
        $this->guardSponsorChain($agent, $data['sponsor_id'] ?? null);
        DB::transaction(function () use ($agent, $data) {
            $values = ['name' => $data['name'], 'email' => $data['email'], 'referral_code' => $data['referral_code'], 'phone' => $data['phone'] ?? null, 'status' => (bool) ($data['status'] ?? false)];
            if (! empty($data['password'])) {
                $values['password'] = $data['password'];
            } $agent->update($values);
            Referral::updateOrCreate(['user_id' => $agent->id], ['sponsor_id' => $data['sponsor_id'] ?? null]);
        });

        return redirect()->route('agents.index')->with('success', 'Agent updated.');
    }

    public function destroy(User $agent)
    {
        abort_unless($agent->role === 'agent', 404);
        if ($agent->agentBookings()->exists() || Commission::where('beneficiary_id', $agent->id)->exists()) {
            return back()->withErrors(['agent' => 'Agents with bookings or commissions cannot be deleted. Deactivate them instead.']);
        }
        $agent->delete();

        return back()->with('success', 'Agent deleted.');
    }

    private function validated(Request $request, ?User $agent = null): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', Rule::unique('users')->ignore($agent)], 'referral_code' => [$agent ? 'required' : 'nullable', 'nullable', 'string', 'max:30', Rule::unique('users')->ignore($agent)], 'phone' => ['nullable', 'string', 'max:30'], 'password' => [$agent ? 'nullable' : 'required', 'nullable', 'string', 'min:8'], 'sponsor_id' => ['nullable', 'exists:users,id'], 'status' => ['nullable', 'boolean']]);
    }

    private function guardSponsorChain(User $agent, ?int $sponsor): void
    {
        for ($level = 0; $sponsor && $level < 10; $level++) {
            if ($sponsor === $agent->id) {
                throw ValidationException::withMessages(['sponsor_id' => 'This sponsor selection creates a referral cycle.']);
            } $sponsor = Referral::where('user_id', $sponsor)->value('sponsor_id');
        }
    }

    private function upline(User $agent): array
    {
        $result = [];
        $userId = $agent->id;
        $seen = [$userId];
        for ($level = 1; $level <= 3; $level++) {
            $sponsorId = Referral::where('user_id', $userId)->value('sponsor_id');
            if (! $sponsorId || in_array($sponsorId, $seen, true)) {
                break;
            }
            $sponsor = User::withSum(['commissions as payable_commission' => fn ($query) => $query->where('status', 'earned')], 'amount')->find($sponsorId);
            if (! $sponsor) {
                break;
            }
            $result[] = ['level' => $level, 'user' => $sponsor];
            $seen[] = $sponsorId;
            $userId = $sponsorId;
        }

        return $result;
    }

    private function downline(int $sponsorId, int $depth = 1, array $seen = []): array
    {
        if ($depth > 3 || in_array($sponsorId, $seen, true)) {
            return [];
        }
        $seen[] = $sponsorId;

        return User::whereIn('id', Referral::where('sponsor_id', $sponsorId)->pluck('user_id'))
            ->withSum(['commissions as payable_commission' => fn ($query) => $query->where('status', 'earned')], 'amount')
            ->orderBy('name')->get()->map(fn ($user) => [
                'user' => $user, 'level' => $depth,
                'children' => $this->downline($user->id, $depth + 1, $seen),
            ])->all();
    }
}
