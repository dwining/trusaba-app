<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"></head>
<body style="font-family: 'Plus Jakarta Sans', sans-serif; background: #f5f7fa; padding: 20px;">
    <div style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ asset('logo.jpeg') }}" alt="TruSaba" style="width: 56px; height: 56px; object-fit: contain;">
            <h1 style="font-size: 20px; color: #066FDA; margin: 8px 0 0;">TruSaba</h1>
        </div>

        <h2 style="font-size: 18px; color: #333;">
            @if($daysUntil === 1)
            Besok berangkat, {{ $userName }}!
            @else
            {{ $daysUntil }} hari lagi, {{ $userName }}!
            @endif
        </h2>

        <p style="color: #666; line-height: 1.6;">
            Trip ke <strong>{{ $itinerary->destination }}</strong> tinggal {{ $daysUntil }} hari lagi.
            @if($daysUntil === 1)
            Saatnya packing dan pastikan semua voucher sudah disimpan.
            @else
            Yuk cek lagi itinerary-mu dan pastikan semuanya sudah siap.
            @endif
        </p>

        <table style="width: 100%; margin: 16px 0; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0; color: #888;">Destinasi</td>
                <td style="font-weight: 600;">{{ $itinerary->destination }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #888;">Tanggal</td>
                <td style="font-weight: 600;">{{ $itinerary->start_date->format('d M') }} – {{ $itinerary->end_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #888;">Durasi</td>
                <td style="font-weight: 600;">{{ $itinerary->duration_days }} hari · {{ $itinerary->total_participants }} orang</td>
            </tr>
        </table>

        <a href="{{ url('/today') }}" style="display: block; background: #066FDA; color: #fff; text-align: center; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: 600; margin: 16px 0;">
            Buka Dashboard Hari Ini
        </a>

        <p style="font-size: 12px; color: #aaa; text-align: center; margin-top: 16px;">
            TruSaba — Yakin, Senang, Nyaman dalam Bertraveling.
        </p>
    </div>
</body>
</html>
