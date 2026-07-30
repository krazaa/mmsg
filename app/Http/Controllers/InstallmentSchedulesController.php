<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\InstallmentSchedules;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InstallmentSchedulesController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::orderBy('name')->get();
        $bookings = Booking::with('customer')->when($request->integer('project'), fn ($query, $id) => $query->where('project_id', $id))->latest()->get();
        $nextUpcomingIds = InstallmentSchedules::query()
            ->join('bookings', 'bookings.id', '=', 'installment_schedules.booking_id')
            ->where('installment_schedules.status', 'pending')
            ->whereDate('installment_schedules.due_date', '>', today())
            ->orderBy('installment_schedules.due_date')
            ->orderBy('installment_schedules.installment_number')
            ->get(['installment_schedules.id', 'installment_schedules.booking_id', 'bookings.customer_id'])
            ->unique('customer_id')
            ->pluck('id');

        $installments = InstallmentSchedules::with(['booking.customer', 'booking.project'])
            ->whereHas('booking')
            ->when($request->integer('project'), fn ($query, $id) => $query->whereHas('booking', fn ($booking) => $booking->where('project_id', $id)))
            ->when($request->integer('booking'), fn ($query, $id) => $query->where('booking_id', $id))
            ->when(! $request->filled('status'), fn ($query) => $query->whereIn('id', $nextUpcomingIds))
            ->when($request->filled('status'), function ($query) use ($request, $nextUpcomingIds) {
                if ($request->status === 'overdue') {
                    $query->whereIn('status', ['pending', 'partial'])->whereDate('due_date', '<', today());
                } elseif ($request->status === 'upcoming') {
                    $query->whereIn('id', $nextUpcomingIds);
                } else {
                    $query->where('status', $request->status);
                }
            })->orderBy('due_date')->paginate(25)->withQueryString();

        $summary = [
            'total' => InstallmentSchedules::whereHas('booking')->count(),
            'upcoming' => $nextUpcomingIds->count(),
            'overdue' => InstallmentSchedules::whereHas('booking')
                ->whereIn('status', ['pending', 'partial'])
                ->whereDate('due_date', '<', today())
                ->count(),
            'received' => InstallmentSchedules::whereHas('booking')->sum('paid_amount'),
        ];

        return view('installments.index', compact('projects', 'bookings', 'installments', 'nextUpcomingIds', 'summary'));
    }

    public function edit(InstallmentSchedules $installment)
    {
        return view('installments.edit', ['installment' => $installment->load('booking.customer', 'booking.package')]);
    }

    public function update(Request $request, InstallmentSchedules $installment)
    {
        $data = $request->validate([
            'due_date' => ['required', 'date'], 'regular_amount' => ['required', 'numeric', 'min:0'],
            'balloon_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['pending', 'waived', 'cancelled'])],
        ]);
        $newTotal = (float) $data['regular_amount'] + (float) $data['balloon_amount'];
        if ($newTotal < (float) $installment->paid_amount) {
            throw ValidationException::withMessages(['regular_amount' => 'Total due cannot be less than the amount already paid.']);
        }
        if ((float) $installment->paid_amount > 0 && in_array($data['status'], ['waived', 'cancelled'], true)) {
            throw ValidationException::withMessages(['status' => 'An installment with payments cannot be waived or cancelled.']);
        }

        DB::transaction(function () use ($installment, $data, $newTotal) {
            $dueDateChanged = ! $installment->due_date->isSameDay($data['due_date']);
            $status = $data['status'];
            if ($status === 'pending' && (float) $installment->paid_amount > 0) {
                $status = (float) $installment->paid_amount >= $newTotal ? 'paid' : 'partial';
            }
            $installment->update(array_merge($data, [
                'total_due' => $newTotal, 'status' => $status,
                'reminder_sent_at' => $dueDateChanged ? null : $installment->reminder_sent_at,
            ]));
            $booking = $installment->booking()->lockForUpdate()->firstOrFail();
            $scheduled = $booking->installments()->whereNotIn('status', ['waived', 'cancelled'])->sum('total_due');
            $booking->update(['total_price' => (float) $booking->booking_amount + (float) $scheduled, 'financed_amount' => $scheduled]);
        });

        return redirect()->route('installments.index', ['booking' => $installment->booking_id])->with('success', 'Installment and booking totals updated.');
    }
}
