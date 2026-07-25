@include('emails.partials.status-message', [
    'eyebrow' => 'UPCOMING INSTALLMENT', 'title' => $dueLabel,
    'name' => $installment->booking->customer->name, 'color' => '#d97706',
    'message' => 'This is a friendly reminder that an installment on your active '.config('app.name').' payment plan is approaching.',
    'details' => [
        'Booking' => $installment->booking->booking_number,
        'Project' => $installment->booking->project->name,
        'Installment' => 'Month '.$installment->installment_number,
        'Due date' => $installment->due_date->format('d M Y'),
        'Amount remaining' => 'Rs '.number_format(max(0, (float) $installment->total_due - (float) $installment->paid_amount), 2),
    ],
    'button' => 'View installment',
])
