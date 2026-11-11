@extends('emails.layout')

@php
    $emailStatus = match ($type) {
        \App\Enums\BuyerOrderUpdateType::PaymentRequired => ['Menunggu pembayaran', '#fff7ed', '#c2410c'],
        \App\Enums\BuyerOrderUpdateType::PaymentSubmitted => ['Bukti pembayaran diterima', '#eff6ff', '#1d4ed8'],
        \App\Enums\BuyerOrderUpdateType::PaymentConfirmed => ['Pembayaran berhasil', '#ecfdf5', '#047857'],
        \App\Enums\BuyerOrderUpdateType::DeliveryReady => ['Pesanan selesai', '#ecfdf5', '#047857'],
    };

    $emailHeading = match ($type) {
        \App\Enums\BuyerOrderUpdateType::PaymentRequired => 'Selesaikan pembayaranmu',
        \App\Enums\BuyerOrderUpdateType::PaymentSubmitted => 'Pembayaran sedang diverifikasi',
        \App\Enums\BuyerOrderUpdateType::PaymentConfirmed => 'Pembayaran berhasil',
        \App\Enums\BuyerOrderUpdateType::DeliveryReady => 'Pesananmu sudah siap',
    };

    $customerName = $order->user?->name ?? 'Pelanggan Meoxstore';
    $formattedTotal = $currencySymbol.' '.number_format(
        (float) $order->total / $currencyRate,
        $currencySymbol === 'Rp' ? 0 : 2,
        ',',
        '.',
    );
@endphp

@section('title', $emailHeading.' | Meoxstore')
@section('preheader', 'Pembaruan pesanan '.$order->publicNumber().': '.$emailStatus[0].'.')

@section('content')
    <p style="margin:0 0 8px; color:#172033; font-size:15px; line-height:23px;">Hai {{ $customerName }},</p>
    <h1 style="margin:0 0 12px; color:#111827; font-size:25px; line-height:32px;">{{ $emailHeading }}</h1>
    <p style="margin:0 0 24px; color:#64748b; font-size:14px; line-height:22px;">
        Terima kasih sudah berbelanja di Meoxstore. Berikut ringkasan terbaru pesananmu.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:18px; border:1px solid #e2e8f0; border-radius:12px;">
        <tr>
            <td style="padding:16px 18px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="color:#64748b; font-size:12px; line-height:18px;">NOMOR PESANAN</td>
                        <td align="right" style="color:#64748b; font-size:12px; line-height:18px;">TANGGAL</td>
                    </tr>
                    <tr>
                        <td style="padding-top:5px; color:#172033; font-size:14px; font-weight:bold; line-height:20px;">{{ $order->publicNumber() }}</td>
                        <td align="right" style="padding-top:5px; color:#172033; font-size:13px; line-height:20px;">{{ $order->created_at?->format('d M Y, H:i') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:22px; background-color:{{ $emailStatus[1] }}; border-radius:12px;">
        <tr>
            <td align="center" style="padding:18px 16px;">
                <div style="color:#64748b; font-size:11px; font-weight:bold; letter-spacing:1px; line-height:17px;">STATUS PESANAN</div>
                <div style="padding-top:4px; color:{{ $emailStatus[2] }}; font-size:18px; font-weight:bold; line-height:25px;">{{ $emailStatus[0] }}</div>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:22px; border:1px solid #e2e8f0; border-radius:12px;">
        <tr>
            <td style="padding:20px;">
                <h2 style="margin:0 0 16px; color:#172033; font-size:16px; line-height:22px;">Ringkasan pembayaran</h2>
                @foreach ($order->items as $item)
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="padding:10px 0; border-bottom:1px solid #edf1f6;">
                        <tr>
                            <td style="color:#334155; font-size:13px; line-height:19px;">
                                <strong>{{ $item->product_name }}</strong><br>
                                <span style="color:#64748b;">{{ $item->variant_name }} &times; {{ $item->quantity }}</span>
                            </td>
                            <td align="right" style="color:#334155; font-size:13px; line-height:19px; white-space:nowrap;">
                                {{ $currencySymbol }} {{ number_format(((float) $item->unit_price * $item->quantity) / $currencyRate, $currencySymbol === 'Rp' ? 0 : 2, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                @endforeach
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:14px;">
                    <tr>
                        <td style="color:#64748b; font-size:13px; line-height:20px;">Total pembayaran</td>
                        <td align="right" style="color:#1d4ed8; font-size:18px; font-weight:bold; line-height:24px;">{{ $formattedTotal }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if ($type === \App\Enums\BuyerOrderUpdateType::PaymentRequired)
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:22px; background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
            <tr>
                <td style="padding:20px;">
                    <h2 style="margin:0 0 14px; color:#172033; font-size:16px; line-height:22px;">Instruksi pembayaran</h2>
                    <p style="margin:0 0 9px; color:#334155; font-size:13px; line-height:20px;">
                        <strong>Metode:</strong> {{ $order->payment_details['name'] ?? $order->payment_details['bank_name'] ?? '—' }}
                    </p>
                    @if ($order->payment_details['account_name'] ?? null)
                        <p style="margin:0 0 9px; color:#334155; font-size:13px; line-height:20px;"><strong>Atas nama:</strong> {{ $order->payment_details['account_name'] }}</p>
                    @endif
                    @if ($order->payment_details['account_number'] ?? null)
                        <p style="margin:0 0 9px; color:#334155; font-size:13px; line-height:20px;"><strong>Nomor rekening:</strong> {{ $order->payment_details['account_number'] }}</p>
                    @endif
                    @if ($order->payment_details['instructions'] ?? null)
                        <p style="margin:0; color:#64748b; font-size:13px; line-height:21px;">{!! nl2br(e($order->payment_details['instructions'])) !!}</p>
                    @endif
                    @if ($order->payment_details['qr_code_path'] ?? null)
                        <p style="margin:14px 0 0; font-size:13px; line-height:20px;">
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->payment_details['qr_code_path']) }}" style="color:#2563eb; font-weight:bold; text-decoration:none;">Lihat kode QR pembayaran</a>
                        </p>
                    @endif
                    <p style="margin:14px 0 0; color:#64748b; font-size:12px; line-height:19px;">Setelah transfer, buka detail pesanan untuk mengirim bukti pembayaran.</p>
                </td>
            </tr>
        </table>
    @elseif ($type === \App\Enums\BuyerOrderUpdateType::PaymentSubmitted)
        <p style="margin:0 0 22px; color:#475569; font-size:14px; line-height:22px;">Bukti pembayaranmu sudah kami terima dan sedang menunggu verifikasi admin.</p>
    @elseif ($type === \App\Enums\BuyerOrderUpdateType::PaymentConfirmed)
        <p style="margin:0 0 22px; color:#475569; font-size:14px; line-height:22px;">Pembayaranmu telah dikonfirmasi. Pesanan sedang diproses.</p>
    @elseif ($type === \App\Enums\BuyerOrderUpdateType::DeliveryReady)
        <p style="margin:0 0 16px; color:#475569; font-size:14px; line-height:22px;">Pesanan sudah selesai diproses. Berikut informasi pesananmu:</p>
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:22px; background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
            <tr>
                <td style="padding:20px;">
                    <h2 style="margin:0 0 12px; color:#172033; font-size:16px; line-height:22px;">Informasi pesanan</h2>
                    @foreach ($order->delivery_details ?? [] as $detail)
                        <p style="margin:0 0 10px; color:#334155; font-size:13px; line-height:20px;">
                            <strong>{{ $detail['label'] }}</strong><br>
                            <span style="display:inline-block; margin-top:4px; padding:9px 11px; background-color:#ffffff; border:1px solid #e2e8f0; border-radius:7px;">{{ $detail['value'] }}</span>
                        </p>
                    @endforeach
                    <p style="margin:12px 0 0; color:#b45309; font-size:12px; line-height:19px;">Simpan informasi ini dengan aman dan segera ubah kata sandi jika diperlukan.</p>
                </td>
            </tr>
        </table>
    @endif

    <table role="presentation" align="center" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto;">
        <tr>
            <td align="center" bgcolor="#2563eb" style="border-radius:8px;">
                <a href="{{ $orderLookupUrl }}" style="display:inline-block; padding:13px 24px; color:#ffffff; font-size:14px; font-weight:bold; line-height:20px; text-decoration:none;">Lacak pesanan</a>
            </td>
        </tr>
    </table>
@endsection
