@extends('emails.layout')

@section('title', 'Bukti pembayaran masuk | Meoxstore')
@section('preheader', 'Pembeli mengirim bukti pembayaran untuk pesanan '.$order->publicNumber().'.')

@section('content')
    <p style="margin:0 0 8px; color:#64748b; font-size:12px; font-weight:bold; letter-spacing:1px; line-height:18px;">NOTIFIKASI ADMIN</p>
    <h1 style="margin:0 0 10px; color:#111827; font-size:25px; line-height:32px;">Bukti pembayaran baru</h1>
    <p style="margin:0 0 24px; color:#64748b; font-size:14px; line-height:22px;">Pembeli telah mengirim bukti pembayaran. Periksa transaksi ini melalui panel admin.</p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:20px; background-color:#eff6ff; border-radius:12px;">
        <tr>
            <td style="padding:18px 20px;">
                <div style="color:#64748b; font-size:12px; line-height:18px;">TOTAL PESANAN</div>
                <div style="padding-top:4px; color:#1d4ed8; font-size:24px; font-weight:bold; line-height:31px;">{{ $currencySymbol }} {{ number_format((float) $order->total / $currencyRate, $currencySymbol === 'Rp' ? 0 : 2, ',', '.') }}</div>
            </td>
            <td align="right" style="padding:18px 20px;">
                <div style="color:#64748b; font-size:12px; line-height:18px;">STATUS</div>
                <div style="padding-top:4px; color:#1d4ed8; font-size:14px; font-weight:bold; line-height:20px;">Menunggu verifikasi</div>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:20px; border:1px solid #e2e8f0; border-radius:12px;">
        <tr>
            <td style="padding:20px;">
                <h2 style="margin:0 0 14px; color:#172033; font-size:16px; line-height:22px;">Detail transaksi</h2>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="padding:6px 0; color:#64748b; font-size:13px; line-height:20px;">Nomor pesanan</td>
                        <td align="right" style="padding:6px 0; color:#172033; font-size:13px; font-weight:bold; line-height:20px;">{{ $order->publicNumber() }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; color:#64748b; font-size:13px; line-height:20px;">Pembeli</td>
                        <td align="right" style="padding:6px 0; color:#172033; font-size:13px; line-height:20px;">{{ $order->user?->name ?? 'Pembeli tamu' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; color:#64748b; font-size:13px; line-height:20px;">Email</td>
                        <td align="right" style="padding:6px 0; color:#172033; font-size:13px; line-height:20px;">{{ $order->contact_email ?? $order->user?->email ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; color:#64748b; font-size:13px; line-height:20px;">Telepon</td>
                        <td align="right" style="padding:6px 0; color:#172033; font-size:13px; line-height:20px;">{{ $order->contact_phone ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 0; color:#64748b; font-size:13px; line-height:20px;">Metode pembayaran</td>
                        <td align="right" style="padding:6px 0; color:#172033; font-size:13px; line-height:20px;">{{ $order->payment_details['name'] ?? $order->payment_details['bank_name'] ?? '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:24px; border:1px solid #e2e8f0; border-radius:12px;">
        <tr>
            <td style="padding:20px;">
                <h2 style="margin:0 0 12px; color:#172033; font-size:16px; line-height:22px;">Produk yang dipesan</h2>
                @foreach ($order->items as $item)
                    <p style="margin:0; padding:10px 0; border-bottom:1px solid #edf1f6; color:#334155; font-size:13px; line-height:20px;">
                        <strong>{{ $item->product_name }}</strong><br>
                        <span style="color:#64748b;">{{ $item->variant_name }} &times; {{ $item->quantity }}</span>
                    </p>
                @endforeach
            </td>
        </tr>
    </table>

    <table role="presentation" align="center" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto;">
        <tr>
            <td align="center" bgcolor="#2563eb" style="border-radius:8px;">
                <a href="{{ route('admin.orders.show', $order) }}" style="display:inline-block; padding:13px 24px; color:#ffffff; font-size:14px; font-weight:bold; line-height:20px; text-decoration:none;">Periksa pesanan di admin</a>
            </td>
        </tr>
    </table>
@endsection
