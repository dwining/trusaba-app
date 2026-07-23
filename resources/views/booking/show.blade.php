@extends('layouts.app', ['navActive' => 'booking'])
@section('title', 'TruSaba · Booking Detail')
@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ url()->previous() }}" aria-label="Back">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">Booking</p>
        <h1>{{ ucfirst($booking->booking_type) }}</h1>
    </div>
</div>

<div class="app-body no-nav">
    <div class="pad" style="padding-top:16px">

        {{-- Voucher card --}}
        <div class="voucher" style="text-align:center;margin-bottom:16px">
            <p class="eyebrow" style="color:var(--accent-hex)">{{ $booking->booking_type === 'hotel' ? 'Hotel Voucher' : 'Booking Voucher' }}</p>
            <h2 style="margin:6px 0 2px">{{ $booking->merchant?->name ?? $booking->itineraryItem?->name ?? 'Booking' }}</h2>
            <p class="small muted">{{ $booking->resource_detail['room_type'] ?? ucfirst($booking->booking_type) }}</p>
            <p class="caption" style="margin-top:4px">
                @if($booking->check_in_date)
                {{ $booking->check_in_date->format('d') }}–{{ $booking->check_out_date?->format('d M Y') }}
                @else
                {{ $booking->booking_date?->format('d M Y') }}
                @endif
            </p>

            {{-- QR --}}
            <div class="qr-box" style="margin:12px auto;width:100px;height:100px">
                <svg viewBox="0 0 40 40" width="100" height="100" xmlns="http://www.w3.org/2000/svg">
                    <rect x="4" y="4" width="12" height="12" fill="none" stroke="var(--fg)" stroke-width="2"/>
                    <rect x="7" y="7" width="6" height="6" fill="var(--fg)"/>
                    <rect x="24" y="4" width="12" height="12" fill="none" stroke="var(--fg)" stroke-width="2"/>
                    <rect x="27" y="7" width="6" height="6" fill="var(--fg)"/>
                    <rect x="4" y="24" width="12" height="12" fill="none" stroke="var(--fg)" stroke-width="2"/>
                    <rect x="7" y="27" width="6" height="6" fill="var(--fg)"/>
                    <rect x="20" y="4" width="4" height="4" fill="var(--fg)"/>
                    <rect x="20" y="12" width="4" height="4" fill="var(--fg)"/>
                    <rect x="20" y="20" width="4" height="4" fill="var(--fg)"/>
                    <rect x="20" y="28" width="4" height="4" fill="var(--fg)"/>
                    <rect x="20" y="36" width="4" height="4" fill="var(--fg)"/>
                    <rect x="4" y="20" width="4" height="4" fill="var(--fg)"/>
                    <rect x="12" y="20" width="4" height="4" fill="var(--fg)"/>
                    <rect x="28" y="20" width="4" height="4" fill="var(--fg)"/>
                    <rect x="36" y="20" width="4" height="4" fill="var(--fg)"/>
                </svg>
            </div>

            <p class="caption">Booking Code</p>
            <p class="booking-code">{{ $booking->voucher_code }}</p>
        </div>

        {{-- Status --}}
        <div class="card" style="margin-bottom:12px">
            <div class="row-between">
                <span>Status</span>
                @php
                    $badges = ['pending'=>['badge-warn','Pending'],'confirmed'=>['badge-gold','Confirmed'],
                    'checked_in'=>['badge-blue','Checked In'],'completed'=>['badge-success','Completed'],
                    'cancelled'=>['badge-danger','Cancelled']];
                    [$bc, $bl] = $badges[$booking->status] ?? ['badge-blue', $booking->status];
                @endphp
                <span class="badge {{ $bc }}">{{ $bl }}</span>
            </div>
        </div>

        {{-- Details --}}
        <div class="card" style="margin-bottom:12px">
            <h3 style="font-size:14px;margin-bottom:8px">Booking Details</h3>
            <div class="stack" style="gap:6px;font-size:13px">
                <div class="row-between"><span class="muted">Merchant</span><span>{{ $booking->merchant?->name ?? '-' }}</span></div>
                <div class="row-between"><span class="muted">Type</span><span>{{ ucfirst($booking->booking_type) }}</span></div>
                @if($booking->check_in_date)
                <div class="row-between"><span class="muted">Check-in</span><span>{{ $booking->check_in_date->format('d M Y') }}</span></div>
                <div class="row-between"><span class="muted">Check-out</span><span>{{ $booking->check_out_date?->format('d M Y') }}</span></div>
                @endif
                @if($booking->booking_date)
                <div class="row-between"><span class="muted">Date</span><span>{{ $booking->booking_date->format('d M Y') }}</span></div>
                @endif
                @if($booking->quantity > 1)
                <div class="row-between"><span class="muted">Quantity</span><span>{{ $booking->quantity }}</span></div>
                @endif
                @if($booking->itinerary)
                <div class="row-between"><span class="muted">Itinerary</span><span>{{ $booking->itinerary->title }}</span></div>
                @endif
                <div class="row-between" style="border-top:1px solid var(--border);padding-top:6px">
                    <span style="font-weight:600">Amount</span>
                    <span class="mono" style="font-weight:600;color:var(--accent-hex)">Rp {{ number_format($booking->amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-block">Back</a>
    </div>
</div>

@endsection
