<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerNotificationController extends Controller
{
    public function managementIndex(Request $request): View
    {
        $query = $request->user()->notifications();
        $totalCount = (clone $query)->count();
        $unreadCount = (clone $query)->whereNull('read_at')->count();
        $todayCount = (clone $query)->whereDate('created_at', today())->count();
        $status = in_array($request->string('status')->toString(), ['all', 'unread', 'read'], true)
            ? $request->string('status')->toString() : 'unread';
        $notifications = $query
            ->when($status === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($status === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('management-notifications.index', compact('notifications', 'totalCount', 'unreadCount', 'todayCount', 'status'));
    }

    public function managementRead(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        return redirect()->to($item->data['url'] ?? route('management.notifications.index'));
    }

    public function managementReadAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'customer', 403);

        $query = $request->user()->notifications();
        $totalCount = (clone $query)->count();
        $unreadCount = (clone $query)->whereNull('read_at')->count();
        $todayCount = (clone $query)->whereDate('created_at', today())->count();
        $status = in_array($request->string('status')->toString(), ['all', 'unread', 'read'], true)
            ? $request->string('status')->toString()
            : 'all';
        $notifications = $query
            ->when($status === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($status === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $bookingNumbers = $notifications->getCollection()
            ->pluck('data.details.Booking')
            ->filter()
            ->unique()
            ->values();
        $bookingDetails = Booking::query()
            ->with(['package', 'allotment.plot.block'])
            ->whereIn('booking_number', $bookingNumbers)
            ->get()
            ->mapWithKeys(function (Booking $booking): array {
                $details = [
                    'Plot size' => $booking->package ? number_format((float) $booking->package->size_marla, 2).' marla' : null,
                    'Payment plan' => ucfirst((string) $booking->payment_plan),
                ];
                if ($booking->allotment?->plot) {
                    $details['Plot'] = collect([
                        $booking->allotment->plot->block?->name,
                        'Plot '.$booking->allotment->plot->plot_number,
                    ])->filter()->implode(' · ');
                }

                return [$booking->booking_number => array_filter($details, fn (mixed $value) => filled($value))];
            });

        return view('customer-notifications.index', compact('notifications', 'totalCount', 'unreadCount', 'todayCount', 'status', 'bookingDetails'));
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        return redirect()->to($item->data['url'] ?? route('customer.notifications.index'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === 'customer', 403);
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
