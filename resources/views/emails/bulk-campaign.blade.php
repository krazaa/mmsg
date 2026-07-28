<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f1f5f9;font-family:Arial,sans-serif;color:#1e293b">
    <div style="max-width:680px;margin:0 auto;padding:30px 15px">
        <div style="background:linear-gradient(135deg,#4338ca,#6d28d9);padding:24px 28px;border-radius:18px 18px 0 0;color:#fff">
            <div style="margin-bottom:18px">@include('emails.partials.logo')</div>
            <div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;opacity:.75">{{ config('app.name', 'Laravel') }}</div>
            <h1 style="margin:8px 0 0;font-size:24px">{{ $recipient->campaign->subject }}</h1>
        </div>
        <div style="background:#fff;padding:30px 28px;border-radius:0 0 18px 18px">
            <p style="margin-top:0">Dear {{ $recipient->name }},</p>
            <div style="font-size:15px;line-height:1.75">{!! $recipient->campaign->body !!}</div>
            <p style="margin:30px 0 0;padding-top:20px;border-top:1px solid #e2e8f0;font-size:11px;line-height:1.6;color:#94a3b8">
                You received this email because you are registered with {{ config('app.name', 'Laravel') }}.
                <a href="{{ route('email-unsubscribe.show', $recipient->unsubscribe_token) }}" style="color:#6366f1">Unsubscribe from promotional emails</a>.
            </p>
        </div>
    </div>
</body>
</html>
