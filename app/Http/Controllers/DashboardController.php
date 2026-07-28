<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\InstallmentSchedules;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Project;
use App\Models\Referral;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\ReferralNetworkService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly ReferralNetworkService $referralNetwork) {}

    public function customerCommissions(Request $request, Customer $customer): View
    {
        $request->setUserResolver(fn () => $customer);

        return $this->commissions($request)->with('portalPreview', true);
    }

    public function commissions(Request $request): View
    {
        abort_unless($request->user()->role === 'customer', 403);

        $customer = $request->user()->customer;
        abort_unless($customer, 404);

        $search = $request->string('search')->trim()->toString();
        $commissions = Commission::with(['payment', 'booking.customer', 'booking.project', 'booking.package'])
            ->where('beneficiary_id', $customer->id)
            ->whereHas('booking')
            ->when($search, function ($query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(function ($query) use ($term) {
                    $query->whereHas('booking.customer', fn ($customer) => $customer
                        ->where('name', 'like', $term)
                        ->orWhere('referral_code', 'like', $term))
                        ->orWhereHas('booking', fn ($booking) => $booking->where('booking_number', 'like', $term))
                        ->orWhereHas('payment', fn ($payment) => $payment->where('receipt_number', 'like', $term));
                });
            })
            ->when($request->filled('project'), fn ($query) => $query->whereHas('booking', fn ($booking) => $booking->where('project_id', $request->integer('project'))))
            ->when($request->filled('level'), fn ($query) => $query->where('level', $request->integer('level')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->latest()
            ->paginate(25)
            ->withQueryString();
        $projects = Project::whereHas('bookings', fn ($booking) => $booking->whereHas('commissions', fn ($commission) => $commission->where('beneficiary_id', $customer->id)))
            ->orderBy('name')
            ->get(['id', 'name']);
        $summary = Commission::where('beneficiary_id', $customer->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'earned' THEN amount - paid_amount ELSE 0 END), 0) as payable")
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as paid')
            ->selectRaw('COALESCE(SUM(amount), 0) as lifetime')->first();

        $showReferralCode = SiteSetting::showReferralCodesOnCustomerPortal();

        return view('customer-commissions', compact('customer', 'commissions', 'summary', 'projects', 'showReferralCode'));
    }

    public function customerTeam(Request $request, Customer $customer): View
    {
        $request->setUserResolver(fn () => $customer);

        return $this->team($request)->with('portalPreview', true);
    }

    public function team(Request $request): View
    {
        abort_unless($request->user()->role === 'customer', 403);

        $customer = $request->user()->customer;
        abort_unless($customer, 404);

        $customer->loadMissing('referralAgent');
        $directReferralIds = Referral::where('sponsor_id', $customer->id)->pluck('user_id');
        $directReferrals = Customer::whereIn('id', $directReferralIds)->withCount('bookings')->orderBy('name')->get();
        $downline = $this->referralNetwork->downline($customer->id);
        $downlineCounts = $downline->countBy('level');
        $downlineTree = $this->referralNetwork->tree($customer->id);
        $referralSummary = Commission::where('beneficiary_id', $customer->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'earned' THEN amount - paid_amount ELSE 0 END), 0) as payable")
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as paid')
            ->selectRaw('COALESCE(SUM(amount), 0) as lifetime')->first();
        $levelCommissions = Commission::where('beneficiary_id', $customer->id)
            ->whereIn('status', ['earned', 'paid'])
            ->selectRaw('level, SUM(amount) as total')
            ->groupBy('level')
            ->pluck('total', 'level');

        $showReferralCode = SiteSetting::showReferralCodesOnCustomerPortal();

        return view('customer-team', compact('customer', 'directReferrals', 'referralSummary', 'levelCommissions', 'downline', 'downlineCounts', 'downlineTree', 'showReferralCode'));
    }

    public function customerPortal(Request $request, Customer $customer): View
    {
        $request->setUserResolver(fn () => $customer);

        return $this->index($request)->with('portalPreview', true);
    }

    public function installments(Request $request): View
    {
        abort_unless($request->user()->role === 'customer', 403);

        $customer = $request->user()->customer;
        abort_unless($customer, 404);

        $bookings = $customer->bookings()
            ->with([
                'project',
                'package',
                'allotment.plot.block',
                'installments' => fn ($query) => $query->orderBy('due_date')->orderBy('installment_number'),
                'payments' => fn ($query) => $query->whereNull('installment_schedule_id')->latest('payment_date'),
            ])
            ->latest('booking_date')
            ->get();

        return view('customer-installments', compact('customer', 'bookings'));
    }

    public function index(Request $request)
    {
        if ($request->user()->role === 'customer') {
            $customer = $request->user()->customer;
            $bookings = $customer?->bookings()
                ->with([
                    'project', 'package', 'agent', 'allotment.plot.block',
                    'installments' => fn ($query) => $query->whereDate('due_date', '<=', today()->endOfMonth()),
                    'payments' => fn ($query) => $query->latest('payment_date'),
                ])
                ->latest('booking_date')->get() ?? collect();
            $paid = $customer?->payments()->whereHas('booking')->where('status', 'verified')->sum('amount') ?? 0;
            $bookings->each(function ($booking) {
                $firstPayments = $booking->payments->whereNull('installment_schedule_id');
                $firstVerified = $firstPayments->contains('status', 'verified');
                $firstUnderReview = $firstPayments->contains('status', 'pending');
                if ($booking->status === 'approved' && ! $firstVerified) {
                    $booking->due_now = $firstUnderReview ? 0 : (float) $booking->booking_amount;

                    return;
                }
                if ($booking->status !== 'active' || ! $firstVerified) {
                    $booking->due_now = 0;

                    return;
                }
                $booking->due_now = $booking->installments->whereNotIn('status', ['paid', 'waived', 'cancelled'])->sum(function ($installment) use ($booking) {
                    $underReview = (float) $booking->payments->where('status', 'pending')->where('installment_schedule_id', $installment->id)->sum('amount');

                    return max(0, (float) $installment->total_due - (float) $installment->paid_amount - $underReview);
                });
            });
            $dueNow = $bookings->sum('due_now');
            $paymentMethods = PaymentMethod::where('customer_portal', true)
                ->where('status', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['code', 'name', 'bank_name', 'account_title', 'account_number', 'crypto_network', 'wallet_address', 'instructions']);

            $notificationUser = User::findOrFail($request->user()->id);
            $notifications = $notificationUser->notifications()->latest()->limit(5)->get();
            $unreadNotificationCount = $notificationUser->unreadNotifications()->count();
            $showReferralCode = SiteSetting::showReferralCodesOnCustomerPortal();

            return view('customer-dashboard', compact('customer', 'bookings', 'paid', 'dueNow', 'paymentMethods', 'notifications', 'unreadNotificationCount', 'showReferralCode'));
        }

        $projects = Project::orderBy('name')->get();
        $projectId = $request->integer('project') ?: null;
        $bookingScope = fn ($query) => $query->when($projectId, fn ($q) => $q->where('project_id', $projectId));

        $activeBookings = Booking::whereIn('status', ['active', 'approved'])->when($projectId, fn ($q) => $q->where('project_id', $projectId))->count();
        $received = Payment::where('status', 'verified')->when($projectId, fn ($q) => $q->whereHas('booking', $bookingScope))->sum('amount');
        $overdue = InstallmentSchedules::whereIn('status', ['pending', 'partial'])->whereDate('due_date', '<', today())
            ->when($projectId, fn ($q) => $q->whereHas('booking', $bookingScope))->selectRaw('COALESCE(SUM(total_due - paid_amount), 0) as amount')->value('amount');
        $outstanding = InstallmentSchedules::whereNotIn('status', ['paid', 'waived', 'cancelled'])
            ->when($projectId, fn ($q) => $q->whereHas('booking', $bookingScope))->selectRaw('COALESCE(SUM(total_due - paid_amount), 0) as amount')->value('amount');
        $payableCommission = Commission::where('status', 'earned')->when($projectId, fn ($q) => $q->whereHas('booking', $bookingScope))->sum('amount');
        $inventory = Project::when($projectId, fn ($q) => $q->whereKey($projectId))->orderBy('name')->get();
        $availableMarla = $inventory->sum(fn ($project) => $project->available_area_marla);
        $recentBookings = Booking::with(['customer', 'project', 'package', 'agent'])->when($projectId, fn ($q) => $q->where('project_id', $projectId))->latest()->limit(6)->get();
        $recentPayments = Payment::with(['customer', 'booking'])->when($projectId, fn ($q) => $q->whereHas('booking', $bookingScope))->latest('payment_date')->limit(6)->get();

        return view('dashboard', compact('projects', 'projectId', 'activeBookings', 'received', 'overdue', 'outstanding', 'payableCommission', 'availableMarla', 'inventory', 'recentBookings', 'recentPayments'));
    }
}
