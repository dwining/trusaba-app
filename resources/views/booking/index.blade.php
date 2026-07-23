@extends('layouts.app', ['navActive' => 'booking'])
@section('title', 'TruSaba · My Bookings')
@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ isset($itinerary) ? route('itineraries.show', $itinerary->id) : route('itineraries.index') }}" aria-label="Back">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        @if(isset($itinerary))
        <p class="eyebrow">Booking</p>
        <h1>{{ $itinerary->title }}</h1>
        @else
        <p class="eyebrow">Booking</p>
        <h1>My Orders</h1>
        @endif
    </div>
</div>

<div class="app-body">
    <div class="pad" style="padding-top:16px">
         @forelse($bookings as $booking)
         <a class="list-card" href="{{ route('bookings.show', $booking->id) }}" style="margin-bottom:12px">
             <div class="qr-thumb" style="width:52px;height:52px;border-radius:8px;background:var(--surface);border:1px solid var(--border);flex-shrink:0;display:flex;align-items:center;justify-content:center;overflow:hidden">
                 <svg viewBox="0 0 40 40" width="40" height="40" xmlns="http://www.w3.org/2000/svg">
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
             <div class="meta">
                 <h3>{{ ucfirst($booking->booking_type) }} · {{ $booking->merchant?->name ?? $booking->itineraryItem?->name ?? 'Booking' }}</h3>
                 <p class="small muted">{{ $booking->voucher_code }} ·
                     @if($booking->check_in_date)
                     {{ $booking->check_in_date->format('d M') }}
                     @elseif($booking->booking_date)
                     {{ $booking->booking_date->format('d M') }}
                     @endif
                     @if(!empty($booking->resource_detail['room_type'] ?? null))
                     · {{ $booking->resource_detail['room_type'] }}
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
