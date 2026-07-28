@include('emails.partials.status-message', [
    'eyebrow' => 'PAYMENT VERIFIED', 'title' => 'Your payment has been verified',
    'name' => $payment->customer->name, 'color' => '#059669',
    'notificationMessage' => 'Your payment has been reviewed and successfully credited to your property account.',
    'details' => ['Receipt' => $payment->receipt_number, 'Booking' => $payment->booking->booking_number, 'Amount' => 'Rs '.number_format($payment->amount, 2), 'Payment for' => $payment->installment ? 'Installment month '.$payment->installment->installment_number : 'First payment'],
    'button' => 'View payment history',
])
