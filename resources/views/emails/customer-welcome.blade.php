<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Welcome to {{ config('app.name', 'Laravel') }}</title></head>
<body style="margin:0;background:#f1f5f9;font-family:Arial,sans-serif;color:#0f172a">
    <div style="max-width:620px;margin:0 auto;padding:32px 16px">
        <div style="overflow:hidden;border-radius:20px;background:#ffffff;box-shadow:0 10px 30px rgba(15,23,42,.08)">
            <div style="padding:32px;background:linear-gradient(135deg,#312e81,#6d28d9);color:#ffffff">
                @include('emails.partials.header', [
                    'eyebrow' => 'Customer account',
                    'title' => 'Welcome, '.$customer->name,
                    'mutedColor' => '#ddd6fe',
                ])
                <p style="margin:10px 0 0;line-height:1.6;color:#e0e7ff">Your secure property account has been created successfully.</p>
            </div>
            <div style="padding:32px">
                <p style="margin:0;line-height:1.7;color:#475569">You can now book a plot, follow installments, submit payment proof and access verified receipts from your customer portal.</p>
                <div style="margin:26px 0;padding:22px;border:1px solid #ddd6fe;border-radius:14px;background:#f5f3ff;text-align:center">
                    <div style="font-size:11px;font-weight:700;letter-spacing:1.5px;color:#7c3aed">YOUR REFERRAL CODE</div>
                    <div style="margin-top:8px;font-family:monospace;font-size:25px;font-weight:800;color:#4c1d95">{{ $customer->referral_code }}</div>
                    <p style="margin:9px 0 0;font-size:13px;color:#6d28d9">Share this code with people you refer to {{ config('app.name', 'Laravel') }}.</p>
                </div>
                <div style="text-align:center"><a href="{{ route('login') }}" style="display:inline-block;padding:13px 24px;border-radius:10px;background:#4f46e5;color:#ffffff;font-weight:700;text-decoration:none">Open customer portal →</a></div>
                <p style="margin:28px 0 0;font-size:12px;line-height:1.6;color:#94a3b8">For your security, this email does not contain your password. If you did not create this account, contact the {{ config('app.name', 'Laravel') }} office.</p>
            </div>
        </div>
        <p style="margin:20px 0 0;text-align:center;font-size:12px;color:#94a3b8">© {{ date('Y') }} {{ config('app.name', 'Laravel') }}</p>
    </div>
</body>
</html>
