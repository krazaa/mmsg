<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;background:#f1f5f9;font-family:Arial,sans-serif;color:#0f172a">
<div style="max-width:620px;margin:auto;padding:32px 16px">
    <div style="overflow:hidden;border-radius:20px;background:#ffffff;box-shadow:0 10px 30px rgba(15,23,42,.08)">
        <div style="padding:30px;background:{{ $color }};color:#ffffff">
            @include('emails.partials.header', [
                'eyebrow' => $eyebrow,
                'title' => $title,
                'mutedColor' => '#ffffff',
            ])
        </div>
        <div style="padding:30px">
            <p style="margin:0 0 10px;font-size:17px;font-weight:700">Dear {{ $name }},</p>
            <p style="margin:0;line-height:1.7;color:#475569">{{ $notificationMessage }}</p>
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:24px 0;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">
                @foreach($details as $label => $value)
                    <tr>
                        <td style="padding:12px 15px;border-bottom:{{ $loop->last ? '0' : '1px solid #e2e8f0' }};color:#64748b">{{ $label }}</td>
                        <td align="right" style="padding:12px 15px;border-bottom:{{ $loop->last ? '0' : '1px solid #e2e8f0' }};font-weight:700">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
            <div style="text-align:center">
                <a href="{{ route('dashboard') }}#payments" style="display:inline-block;padding:13px 22px;border-radius:10px;background:{{ $color }};color:#ffffff;font-weight:700;text-decoration:none">{{ $button }} →</a>
            </div>
            <p style="margin:26px 0 0;font-size:12px;line-height:1.6;color:#94a3b8">This is an automated account update. Sign in to your secure portal for the latest record.</p>
        </div>
    </div>
    <p style="text-align:center;font-size:12px;color:#94a3b8">© {{ date('Y') }} {{ config('app.name') }}</p>
</div>
</body>
</html>
