@extends('layouts.app', ['navActive' => 'booking'])
@section('title', 'TruSaba · My Bookings')
@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ route('itineraries.index') }}" aria-label="Back">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">Booking</p>
        <h1>My Orders</h1>
    </div>
</div>

<div class="app-body">
    <div class="pad" style="padding-top:16px">
        @forelse($bookings as $booking)
        <a class="list-card" href="{{ route('bookings.show', $booking->id) }}" style="margin-bottom:12px">
            <div class="ph-img thumb" style="background:linear-gradient(145deg,oklch(0.55 0.1 255 / 0.25),oklch(0.85 0.12 87 / 0.3))"></div>
            <div class="meta">
                <h3>{{ $booking->merchant->name }}</h3>
                <p class="small muted">{{ $booking->voucher_code }} ·
                    @if($booking->check_in_date)
                    {{ $booking->check_in_date->format('d M') }}
                    @endif
                </p>
                @php
                    $statusBadges = [
                        'pending' => ['badge-warn', 'Pending'],
                        'confirmed' => ['badge-gold', 'Confirmed'],
                        'checked_in' => ['badge-blue', 'Checked in'],
                        'completed' => ['badge-success', 'Completed'],
                        'cancelled' => ['badge-danger', 'Cancelled'],
                    ];
                    [$badgeClass, $badgeLabel] = $statusBadges[$booking->status] ?? ['badge-blue', $booking->status];
                @endphp
                <span class="badge {{ $badgeClass }}" style="margin-top:4px">{{ $badgeLabel }}</span>
            </div>
            <span class="amount" style="color:var(--fg)">Rp {{ number_format($booking->amount, 0, ',', '.') }}</span>
        </a>
        @empty
        <div class="card" style="text-align:center;padding:40px 20px">
            <h2>No bookings yet</h2>
            <p class="muted small" style="margin:8px 0 16px">Book hotels, attractions, and transport from your itinerary.</p>
            <a href="{{ route('itineraries.index') }}" class="btn btn-primary">View Itinerary</a>
        </div>
        @endforelse
    </div>
</div>

<nav class="bottom-nav">
    <a class="nav-item" href="{{ route('dashboard') }}">
        <svg viewBox="0 0 24 24"><path d="M4 10.5L12 4l8 6.5V20a1 1 0 01-1 1h-5v-6H10v6H5a1 1 0 01-1-1v-9.5z"/></svg>
        Home
    </a>
    <a class="nav-item active" href="{{ route('bookings.index') }}">
        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 5V3M16 5V3"/></svg>
        Booking
    </a>
    <a class="nav-item" href="{{ route('chat') }}">
        <svg viewBox="0 0 24 24"><path d="M5 18l-1 3 3-1h9a3 3 0 003-3V7a3 3 0 00-3-3H8a3 3 0 00-3 3v11z"/></svg>
        Chat
    </a>
    <a class="nav-item" href="{{ route('history') }}">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0116 0"/></svg>
        Profile
    </a>
</nav>

@endsection
