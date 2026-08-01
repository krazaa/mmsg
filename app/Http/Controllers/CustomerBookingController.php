<?php

namespace App\Http\Controllers;

use App\Contracts\InstallmentScheduleGenerator;
use App\Models\Booking;
use App\Models\PlotPackage;
use App\Models\Project;
use App\Models\User;
use App\Notifications\AccountActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerBookingController extends Controller
{
    public function __construct(private readonly InstallmentScheduleGenerator $schedules) {}

    public function create(Request $request)
    {
        abort_unless($request->user()->role === 'customer' && $request->user()->customer, 403);
        $pendingBooking = $request->user()->customer->bookings()
            ->whereIn('status', ['pending', 'approved'])
            ->whereDoesntHave('payments', fn ($query) => $query->whereNull('installment_schedule_id')->where('status', 'verified'))
            ->latest('id')
            ->first();
        $projects = Project::where('status', true)->with(['packages' => fn ($query) => $query->where('status', true)->orderBy('size_marla')])->orderBy('name')->get()
            ->filter(fn ($project) => $project->available_area_marla > 0)
            ->map(function ($project) {
                $project->setRelation('packages', $project->packages
                    ->filter(fn ($package) => (float) $package->size_marla <= $project->available_area_marla)->values());

                return $project;
            })->filter(fn ($project) => $project->packages->isNotEmpty())->values();
        $customerBookings = $request->user()->customer->bookings()->with([
            'payments',
            'installments' => fn ($query) => $query->whereDate('due_date', '<=', today()->endOfMonth()),
        ])->get();
        $dueNow = $customerBookings->sum(function ($booking) {
            $firstPayments = $booking->payments->whereNull('installment_schedule_id');
            $firstVerified = $firstPayments->contains('status', 'verified');
            if ($booking->status === 'approved' && ! $firstVerified) {
                return $firstPayments->contains('status', 'pending') ? 0 : (float) $booking->booking_amount;
            }
            if ($booking->status !== 'active' || ! $firstVerified) {
                return 0;
            }

            return $booking->installments->whereNotIn('status', ['paid', 'waived', 'cancelled'])->sum(function ($installment) use ($booking) {
                $underReview = (float) $booking->payments->where('status', 'pending')->where('installment_schedule_id', $installment->id)->sum('amount');

                return max(0, (float) $installment->total_due - (float) $installment->paid_amount - $underReview);
            });
        });

        return view('customer-bookings.create', compact('projects', 'dueNow', 'pendingBooking'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'customer' && $request->user()->customer, 403);
        $data = $request->validate([
            'package_id' => ['required', 'exists:plot_packages,id'],
            'payment_plan' => ['required', 'in:cash,installment'],
        ]);
        $customer = $request->user()->customer;

        $booking = DB::transaction(function () use ($data, $customer) {
            User::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            if ($customer->bookings()->whereIn('status', ['pending', 'approved'])
                ->whereDoesntHave('payments', fn ($query) => $query->whereNull('installment_schedule_id')->where('status', 'verified'))->exists()) {
                throw ValidationException::withMessages([
                    'package_id' => 'Complete and verify the first payment for your current booking before creating another booking.',
                ]);
            }

            $package = PlotPackage::where('status', true)->findOrFail($data['package_id']);
            if ($data['payment_plan'] === 'cash' && ! $package->offersCash()) {
                throw ValidationException::withMessages([
                    'payment_plan' => 'Cash is not available for this package.',
                ]);
            }
            if ($data['payment_plan'] === 'installment' && ! $package->offersInstallments()) {
                throw ValidationException::withMessages([
                    'payment_plan' => 'Installments are not available for this package.',
                ]);
            }
            $project = Project::where('status', true)->lockForUpdate()->findOrFail($package->project_id);
            if ($project->available_area_marla < (float) $package->size_marla) {
                throw ValidationException::withMessages(['package_id' => 'This package is currently unavailable.']);
            }
            $booking = Booking::create([
                'booking_number' => 'BKG-'.now()->format('ymd').'-'.strtoupper(substr((string) str()->uuid(), 0, 6)),
                'project_id' => $project->id,
                'package_id' => $package->id,
                'customer_id' => $customer->id,
                'agent_id' => $customer->referral_agent_id,
                'booking_date' => today(),
                'payment_plan' => $data['payment_plan'],
                'total_price' => $data['payment_plan'] === 'cash' ? $package->effective_cash_price : $package->total_price,
                'booking_amount' => $data['payment_plan'] === 'cash' ? $package->effective_cash_price : $package->booking_amount,
                'financed_amount' => $data['payment_plan'] === 'cash' ? 0 : $package->total_price - (float) $package->booking_amount,
                'status' => 'approved',
            ]);
            if ($booking->payment_plan === 'installment') {
                $this->schedules->generate($booking);
            }
            $project->increment('reserved_area_marla', (float) $package->size_marla);

            return $booking;
        });

        $customer->user->notify(new AccountActivityNotification('Booking created — payment required', 'Your plot is reserved. Submit the required first payment to activate the booking and unlock another booking.', 'booking', route('dashboard').'#payments', ['Booking' => $booking->booking_number, 'Project' => $booking->project->name]));

        return redirect()->route('dashboard')->with('success', 'Booking '.$booking->booking_number.' created. Submit the required payment to activate it.');
    }
}
