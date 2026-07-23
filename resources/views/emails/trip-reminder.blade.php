<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="font-family: 'Plus Jakarta Sans', sans-serif; background: #f5f7fa; padding: 20px;">
    <div style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ asset('logo.jpeg') }}" alt="TruSaba" style="width: 56px; height: 56px; object-fit: contain;">
            <h1 style="font-size: 20px; color: #066FDA; margin: 8px 0 0;">TruSaba</h1>
        </div>

        <h2 style="font-size: 18px; color: #333;">
            @if($daysUntil === 1)
            Leaving tomorrow, {{ $userName }}!
            @else
            {{ $daysUntil }} days to go, {{ $userName }}!
            @endif
        </h2>

        <p style="color: #666; line-height: 1.6;">
            Trip to <strong>{{ $itinerary->destination }}</strong> is {{ $daysUntil }} days away.
            @if($daysUntil === 1)
            Time to pack and make sure all vouchers are saved.
            @else
            Check your itinerary again and make sure everything is ready.
            @endif
        </p>

        <table style="width: 100%; margin: 16px 0; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0; color: #888;">Destination</td>
                <td style="font-weight: 600;">{{ $itinerary->destination }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #888;">Dates</td>
                <td style="font-weight: 600;">{{ $itinerary->start_date->format('d M') }} – {{ $itinerary->end_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 0; color: #888;">Duration</td>
                <td style="font-weight: 600;">{{ $itinerary->duration_days }} days · {{ $itinerary->total_participants }} people</td>
            </tr>
        </table>

        <a href="{{ url('/today') }}" style="display: block; background: #066FDA; color: #fff; text-align: center; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: 600; margin: 16px 0;">
            Open Today Dashboard
        </a>

        <p style="font-size: 12px; color: #aaa; text-align: center; margin-top: 16px;">
            TruSaba — Confident, Happy, Comfortable in Traveling.
        </p>
    </div>
</body>
</html>
