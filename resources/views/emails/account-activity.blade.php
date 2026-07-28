<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#eef2f7;font-family:Arial,Helvetica,sans-serif;color:#0f172a">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#eef2f7">
    <tr>
        <td align="center" style="padding:32px 14px">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background:#ffffff;border:1px solid #dbe3ee;border-radius:18px;overflow:hidden">
                <tr>
                    <td style="padding:26px 30px;background:#07172f;border-bottom:4px solid {{ $accent }}">
                        @include('emails.partials.header', ['eyebrow' => $category.' update'])
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px">
                        <p style="margin:0 0 12px;font-size:17px;font-weight:700;color:#0f172a">Dear {{ $name }},</p>
                        <p style="margin:0;color:#475569;font-size:15px;line-height:1.75">{{ $notificationMessage }}</p>

                        @if(count($details))
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:24px 0;border:1px solid #dbe3ee;border-radius:12px;overflow:hidden">
                                @foreach($details as $label => $value)
                                    <tr>
                                        <td style="padding:13px 15px;background:{{ $loop->odd ? '#f8fafc' : '#ffffff' }};border-bottom:{{ $loop->last ? '0' : '1px solid #e2e8f0' }};color:#64748b;font-size:13px;width:40%">{{ $label }}</td>
                                        <td align="right" style="padding:13px 15px;background:{{ $loop->odd ? '#f8fafc' : '#ffffff' }};border-bottom:{{ $loop->last ? '0' : '1px solid #e2e8f0' }};color:#0f172a;font-size:14px;font-weight:700">{{ $value }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif

                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:26px">
                            <tr>
                                <td style="border-radius:10px;background:{{ $accent }}">
                                    <a href="{{ $actionUrl }}" style="display:inline-block;padding:14px 22px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none">Open customer portal&nbsp; →</a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:28px 0 0;padding-top:20px;border-top:1px solid #e2e8f0;color:#94a3b8;font-size:12px;line-height:1.65">
                            This is an automated account update. You can manage email and WhatsApp notifications from your profile.
                        </p>
                    </td>
                </tr>
            </table>
            <p style="margin:18px 0 0;color:#94a3b8;font-size:11px">© {{ date('Y') }} {{ config('app.name', 'MMS Group') }}. All rights reserved.</p>
        </td>
    </tr>
</table>
</body>
</html>
