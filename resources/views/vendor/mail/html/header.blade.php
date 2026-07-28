@props(['url'])
<tr>
<td class="header">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td width="170" valign="middle" align="left">
    <a href="{{ $url }}" style="display:inline-block">
        <img src="{{ asset('email-logo.png') }}" width="150" alt="{{ config('app.name', 'MMS Group') }}" style="display:block;width:150px;max-width:100%;height:auto;border:0">
    </a>
</td>
<td valign="middle" align="right" style="color:#ffffff;font-size:18px;font-weight:700">
    {{ $slot }}
</td>
</tr>
</table>
</td>
</tr>
