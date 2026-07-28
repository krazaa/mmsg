<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Booking;
use App\Models\Plot;
use App\Models\PlotAllotment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlotAllotmentController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::orderBy('name')->get();
        $allotments = PlotAllotment::with(['booking.customer', 'booking.project', 'booking.package', 'plot.block', 'allottedBy'])
            ->when($request->integer('project'), fn ($query, $id) => $query->whereHas('booking', fn ($booking) => $booking->where('project_id', $id)))
            ->latest('allotment_date')->paginate(25)->withQueryString();
        $bookings = Booking::with(['customer', 'project', 'package'])->where('status', 'active')
            ->whereDoesntHave('allotment')
            ->latest()->get();
        $plots = Plot::with(['project', 'block'])->where('status', 'available')
            ->whereHas('project', fn ($query) => $query->where('status', true))
            ->whereHas('block', fn ($query) => $query->where('status', true))
            ->orderBy('project_id')->orderBy('block_id')->orderBy('plot_number')->get();
        $blocks = Block::with('project')->where('status', true)
            ->whereHas('project', fn ($query) => $query->where('status', true))
            ->orderBy('project_id')->orderBy('name')->get();

        return view('allotments.index', compact('projects', 'allotments', 'bookings', 'plots', 'blocks'));
    }

    public function storeInventory(Request $request)
    {
        $data = $request->validate([
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('status', true)],
            'block_id' => ['required', 'integer', Rule::exists('blocks', 'id')->where('status', true)],
            'plot_number' => [
                'required', 'string', 'max:50',
                Rule::unique('plots')->where(fn ($query) => $query->where('block_id', $request->integer('block_id'))),
            ],
            'size_marla' => ['required', 'numeric', 'gt:0'],
            'category' => ['required', Rule::in(['residential', 'commercial', 'farmhouse'])],
            'base_price' => ['required', 'numeric', 'min:0'],
            'premium_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $block = Block::findOrFail($data['block_id']);
        if ($block->project_id !== (int) $data['project_id']) {
            throw ValidationException::withMessages(['block_id' => 'Select a block belonging to the chosen project.']);
        }

        $premium = (float) ($data['premium_amount'] ?? 0);
        Plot::create($data + [
            'premium_amount' => $premium,
            'total_price' => (float) $data['base_price'] + $premium,
            'status' => 'available',
            'version' => 1,
        ]);

        return back()->with('success', 'Plot added to inventory successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'integer', Rule::exists('bookings', 'id')->where('status', 'active'), Rule::unique('plot_allotments', 'booking_id')],
            'plot_id' => ['required', 'integer', Rule::exists('plots', 'id')->where('status', 'available'), Rule::unique('plot_allotments', 'plot_id')],
            'allotment_date' => ['required', 'date'], 'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data) {
            $booking = Booking::with('package')->lockForUpdate()->findOrFail($data['booking_id']);
            $plot = Plot::lockForUpdate()->findOrFail($data['plot_id']);
            if ($booking->status !== 'active' || $plot->status->value !== 'available') {
                throw ValidationException::withMessages(['plot_id' => 'This booking or plot is no longer available for allotment.']);
            }
            if ($booking->project_id !== $plot->project_id || abs((float) $booking->package->size_marla - (float) $plot->size_marla) > 0.001) {
                throw ValidationException::withMessages(['plot_id' => 'Select a plot from the same project with the same package size.']);
            }
            $allotment = PlotAllotment::create($data + ['allotment_number' => 'ALT-'.now()->format('ymd').'-'.strtoupper(str()->random(6)), 'allotted_by' => auth()->id()]);
            $plot->update(['status' => 'sold', 'version' => $plot->version + 1]);
        });

        return back()->with('success', 'Plot allotted successfully.');
    }
}
