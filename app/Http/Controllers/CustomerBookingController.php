<?php

namespace App\Http\Controllers;

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
    public function create(Request $request)
    {
        abort_unless($request->user()->role === 'customer' && $request->user()->customer, 403);
        $pendingBooking = $request->user()->customer->bookings()
            ->where('status', 'pending')
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
        $data = $request->validate(['package_id' => ['required', 'exists:plot_packages,id']]);
        $customer = $request->user()->customer;

        $booking = DB::transaction(function () use ($data, $customer) {
            User::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            if ($customer->bookings()->where('status', 'pending')->exists()) {
                throw ValidationException::withMessages([
                    'package_id' => 'Your previous plot request is still pending office approval. You can submit another request after it is approved or cancelled.',
                ]);
            }

            $package = PlotPackage::where('status', true)->findOrFail($data['package_id']);
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
                'total_price' => $package->total_price,
                'booking_amount' => $package->booking_amount,
                'financed_amount' => $package->total_price - (float) $package->booking_amount,
                'status' => 'pending',
            ]);
            $project->increment('reserved_area_marla', (float) $package->size_marla);

            return $booking;
        });

        $customer->user->notify(new AccountActivityNotification('Booking request submitted', 'Your plot booking request has been received and is awaiting office approval.', 'booking', route('dashboard'), ['Booking' => $booking->booking_number, 'Project' => $booking->project->name]));
        User::whereIn('role', ['super_admin', 'admin'])->where('status', true)->each(fn (User $admin) => $admin->notify(new AccountActivityNotification(
            'New booking requires approval',
            $customer->name.' submitted a new plot booking request.',
            'booking',
            route('bookings.manage', $booking),
            ['Customer' => $customer->name, 'Booking' => $booking->booking_number, 'Project' => $booking->project->name, 'Package' => $booking->package->name],
        )));

        return redirect()->route('dashboard')->with('success', 'Plot booking request '.$booking->booking_number.' submitted for office approval.');
    }
}
