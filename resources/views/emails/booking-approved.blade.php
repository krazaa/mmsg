@include('emails.partials.status-message', [
    'eyebrow' => 'BOOKING APPROVED', 'title' => 'Your booking is approved',
    'name' => $booking->customer->name, 'color' => '#4f46e5',
    'notificationMessage' => 'The office has approved your booking. You can now sign in and submit your first payment for verification.',
    'details' => ['Booking' => $booking->booking_number, 'Project' => $booking->project->name, 'Package' => $booking->package->name, 'First payment' => 'Rs '.number_format($booking->booking_amount, 2)],
    'button' => 'Make first payment',
])
