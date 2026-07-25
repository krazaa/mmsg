<?php

namespace App\Http\Controllers;

use App\Contracts\BookingCreator;
use App\Contracts\BookingLifecycleManager;
use App\Mail\BookingApprovedMail;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Notifications\AccountActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function sales(Request $request)
    {
        $projects = Project::query()->where('status', true)->orderBy('name')->get();

        $project = Project::with(['packages' => fn ($query) => $query->orderBy('id')])->where('status', true)
            ->when($request->integer('project'), fn ($query, $id) => $query->whereKey($id))
            ->firstOrFail();

        $project->setRelation('packages', $project->packages->where('status', true)->values());

        $bookings = Booking::with('customer', 'package', 'agent')->where('project_id', $project->id)->latest()->limit(20)->get();

        return view('bookings.index', compact('project', 'projects', 'bookings') + [
            'agents' => User::where('role', 'agent')->where('status', true)->orderBy('name')->get(),
            'customers' => Customer::where('status', true)->with(['referralAgent', 'latestBooking'])->orderBy('name')->get(),
        ]);
    }

    public function index(Request $request)
    {
        $projects = Project::orderBy('name')->get();

        $bookings = Booking::with(['customer', 'project', 'package', 'agent'])
            ->withSum(['payments as paid_total' => fn ($query) => $query->where('status', 'verified')], 'amount')
            ->when($request->integer('project'), fn ($query, $id) => $query->where('project_id', $id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search')->trim().'%';
                $query->where(fn ($inner) => $inner->where('booking_number', 'like', $search)
                    ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', $search)->orWhere('cnic', 'like', $search)->orWhere('phone', 'like', $search)));
            })->latest()->paginate(25)->withQueryString();

        return view('bookings.manage', compact('projects', 'bookings'));
    }

    public function store(Request $request, BookingCreator $creator)
    {
        $data = $request->validate([
            'package_id' => ['required', 'exists:plot_packages,id'], 'customer_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'customer')->where('status', true)],
            'name' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'], 'cnic' => ['required_without:customer_id', 'nullable', 'string', 'max:15', 'unique:users,cnic'],
            'phone' => ['required_without:customer_id', 'nullable', 'string', 'max:30'], 'email' => ['nullable', 'email', 'unique:users,email'], 'address' => ['nullable', 'string'],
            'agent_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'agent')->where('status', true)], 'booking_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,bank_transfer,cheque,card,easypaisa,jazzcash'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
        ]);
        $booking = $creator->create($data);
        $booking->customer->notify(new AccountActivityNotification('Booking and plan activated', 'Your booking was created by the office, the first payment was verified, and your property plan is active.', 'booking', route('dashboard').'#payments', ['Booking' => $booking->booking_number, 'Project' => $booking->project->name]));

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking created and installment schedule generated.');
    }

    public function show(Booking $booking)
    {
        return view('bookings.show', ['booking' => $booking->load('project', 'package', 'customer', 'agent', 'installments', 'payments')]);
    }

    public function edit(Booking $booking)
    {
        return view('bookings.edit', ['booking' => $booking->load('customer', 'project', 'package'), 'agents' => User::whereIn('role', ['agent', 'customer'])->where(fn ($query) => $query->where('status', true)->orWhere('id', $booking->agent_id))->orderBy('name')->get()]);
    }

    public function manage(Booking $booking)
    {
        return view('bookings.manage-booking', ['booking' => $booking->load(['customer', 'project', 'package', 'agent', 'payments'])]);
    }

    public function status(Request $request, Booking $booking, BookingLifecycleManager $lifecycle)
    {
        $previousStatus = $booking->status;
        $decision = $request->validate(['status' => ['required', Rule::in(['approved', 'cancelled', 'defaulted', 'completed'])], 'management_notes' => ['nullable', 'required_if:status,cancelled,defaulted', 'string', 'max:2000']]);
        $status = $decision['status'];
        $request->merge([
            'name' => $booking->customer->name,
            'father_name' => $booking->customer->father_name,
            'cnic' => $booking->customer->cnic,
            'phone' => $booking->customer->phone,
            'email' => $booking->customer->email,
            'address' => $booking->customer->address,
            'agent_id' => $booking->agent_id,
            'booking_date' => $booking->booking_date->toDateString(),
            'status' => $status,
        ]);

        $response = $this->update($request, $booking, $lifecycle);
        $booking->update(['management_notes' => $decision['management_notes'] ?? null]);

        if ($previousStatus !== $status && $status !== 'approved') {
            $updatedBooking = $booking->fresh()->load('customer');
            $updatedBooking->customer->notify(new AccountActivityNotification('Booking status updated', 'Your booking status changed from '.ucfirst($previousStatus).' to '.ucfirst($status).'.', 'booking', route('dashboard').'#payments', ['Booking' => $updatedBooking->booking_number, 'Status' => ucfirst($status)]));
        }

        return $response;
    }

    public function update(Request $request, Booking $booking, BookingLifecycleManager $lifecycle)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'father_name' => ['nullable', 'string', 'max:255'],
            'cnic' => ['nullable', 'string', 'max:15', Rule::unique('users')->ignore($booking->customer_id)],
            'phone' => ['required', 'string', 'max:30'], 'email' => ['nullable', 'email'], 'address' => ['nullable', 'string'],
            'agent_id' => ['nullable', Rule::exists('users', 'id')->whereIn('role', ['agent', 'customer'])], 'booking_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['pending', 'approved', 'active', 'completed', 'cancelled', 'defaulted'])],
        ]);

        $approved = $lifecycle->update($booking, $data);

        if ($approved) {
            $approvedBooking = $booking->fresh()->load(['customer', 'project', 'package']);
            Mail::to($approvedBooking->customer->email)->send(new BookingApprovedMail($approvedBooking));
            $approvedBooking->customer->notify(new AccountActivityNotification('Booking approved', 'Your booking has been approved. You can now submit the first payment from your portal.', 'booking', route('dashboard').'#payments', ['Booking' => $approvedBooking->booking_number, 'Project' => $approvedBooking->project->name]));
        }

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking updated successfully.');
    }
}
