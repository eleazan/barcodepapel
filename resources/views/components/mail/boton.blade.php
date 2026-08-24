@props([
    'url',
])

<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
    <tr>
        <td align="center" style="border-radius:999px; background-color:#0e7490;">
            <a href="{{ $url }}" style="display:inline-block; padding:13px 30px; font-size:15px; font-weight:600; color:#ffffff; text-decoration:none; border-radius:999px;">
                {{ $slot }}
            </a>
        </td>
    </tr>
</table>
