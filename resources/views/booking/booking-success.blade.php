@extends('layouts.app', ['navActive' => 'home'])
@section('title', 'TruSaba · Voucher Booking')
@section('content')

<div class="app-body no-nav" data-od-id="voucher-body">
    <div class="pad" style="padding-top:24px;text-align:center">
        <div class="modal-icon success" style="margin:0 auto 12px" data-od-id="success-icon">
            <svg viewBox="0 0 24 24"><path d="M5 12l5 5L20 7"/></svg>
        </div>
        <h1 data-od-id="success-title">Booking berhasil!</h1>
        <p class="muted small" style="margin:6px 0 20px">Voucher digital sudah siap. Tunjukkan saat check-in.</p>

        <div class="voucher" data-od-id="digital-voucher">
            <p class="eyebrow" style="color:var(--accent-hex)">{{ $booking->booking_type === 'hotel' ? 'Hotel Voucher' : 'Booking Voucher' }}</p>
            <h2 style="margin:6px 0 2px">{{ $booking->merchant->name }}</h2>
            <p class="small muted">{{ $booking->resource_detail['room_type'] ?? $booking->booking_type }}</p>
            <p class="caption" style="margin-top:4px">
                @if($booking->check_in_date)
                {{ $booking->check_in_date->format('d') }}–{{ $booking->check_out_date?->format('d M Y') }}
                @else
                {{ $booking->booking_date?->format('d M Y') }}
                @endif
            </p>
            <div class="qr-box" data-od-id="qr-placeholder" aria-label="QR code">
                <svg viewBox="0 0 96 96" xmlns="http://www.w3.org/2000/svg">
                    <rect x="8" y="8" width="28" height="28" fill="none" stroke="#1a1a1a" stroke-width="4"/>
                    <rect x="14" y="14" width="16" height="16" fill="#1a1a1a"/>
                    <rect x="60" y="8" width="28" height="28" fill="none" stroke="#1a1a1a" stroke-width="4"/>
                    <rect x="66" y="14" width="16" height="16" fill="#1a1a1a"/>
                    <rect x="8" y="60" width="28" height="28" fill="none" stroke="#1a1a1a" stroke-width="4"/>
                    <rect x="14" y="66" width="16" height="16" fill="#1a1a1a"/>
                    <rect x="44" y="8" width="8" height="8" fill="#1a1a1a"/>
                    <rect x="44" y="24" width="8" height="8" fill="#1a1a1a"/>
                    <rect x="8" y="44" width="8" height="8" fill="#1a1a1a"/>
                    <rect x="24" y="44" width="8" height="8" fill="#1a1a1a"/>
                    <rect x="44" y="44" width="16" height="16" fill="#1a1a1a"/>
                    <rect x="68" y="44" width="8" height="8" fill="#1a1a1a"/>
                    <rect x="84" y="44" width="8" height="8" fill="#1a1a1a"/>
                    <rect x="44" y="68" width="8" height="8" fill="#1a1a1a"/>
                    <rect x="60" y="60" width="12" height="12" fill="#1a1a1a"/>
                    <rect x="76" y="68" width="12" height="12" fill="#1a1a1a"/>
                    <rect x="60" y="80" width="8" height="8" fill="#1a1a1a"/>
                    <rect x="44" y="84" width="8" height="8" fill="#1a1a1a"/>
                </svg>
            </div>
            <p class="caption">Kode booking</p>
            <p class="booking-code" data-od-id="booking-code">{{ $booking->voucher_code }}</p>
        </div>

        <div class="stack" style="margin-top:20px;text-align:left">
            <a class="btn btn-primary btn-block" href="{{ route('today') }}" data-od-id="btn-to-today">Buka Dashboard Hari Ini</a>
            <a class="btn btn-secondary btn-block" href="{{ route('itineraries.index') }}" data-od-id="btn-back-itinerary">Kembali ke Itinerary</a>
        </div>
    </div>
</div>

@endsection
