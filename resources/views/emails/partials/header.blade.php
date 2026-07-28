<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td width="165" valign="middle" style="padding-right:20px">
            @include('emails.partials.logo')
        </td>
        <td valign="middle" align="right">
            <p style="margin:0 0 8px;color:{{ $mutedColor ?? '#bfdbfe' }};font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase">
                {{ config('app.name', 'MMS Group') }} · {{ $eyebrow }}
            </p>
            <h1 style="margin:0;color:#ffffff;font-size:24px;line-height:1.3;font-weight:800">{{ $title }}</h1>
        </td>
    </tr>
</table>
