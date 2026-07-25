@include('emails.partials.status-message', [
    'eyebrow' => 'PLAN ACTIVE', 'title' => 'Your payment plan is now active',
    'name' => $booking->customer->name, 'color' => '#7c3aed',
    'message' => 'Your first payment has been verified and the booking is now active. You can follow upcoming installments from your dashboard.',
    'details' => ['Booking' => $booking->booking_number, 'Project' => $booking->project->name, 'Package' => $booking->package->name, 'Monthly installment' => 'Rs '.number_format($booking->package->monthly_amount, 2)],
    'button' => 'View active plan',
])
