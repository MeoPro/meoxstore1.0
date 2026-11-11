<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>@yield('title', 'Meoxstore')</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; color:#172033; font-family:Arial,Helvetica,sans-serif;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent;">
        @yield('preheader', 'Informasi terbaru mengenai pesanan Anda di Meoxstore.')
    </div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f1f5f9; width:100%;">
        <tr>
            <td align="center" style="padding:32px 14px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:600px; background-color:#ffffff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden;">
                    <tr>
                        <td style="padding:26px 32px; border-bottom:1px solid #edf1f6;">
                            <a href="{{ route('home') }}" style="display:inline-block; text-decoration:none;">
                                <img src="{{ $message->embed(public_path('images/logo.png')) }}" width="64" alt="Meoxstore" style="display:block; width:64px; max-width:100%; height:auto; border:0;">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:22px 32px; background-color:#f8fafc; border-top:1px solid #edf1f6; color:#64748b; font-size:12px; line-height:19px; text-align:center;">
                            Email ini dikirim otomatis, mohon tidak membalas langsung.<br>
                            Butuh bantuan? Kunjungi <a href="{{ route('contact') }}" style="color:#2563eb; text-decoration:none;">halaman kontak Meoxstore</a>.<br><br>
                            &copy; {{ now()->year }} Meoxstore. Hak cipta dilindungi.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
