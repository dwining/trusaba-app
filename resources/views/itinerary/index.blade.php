@extends('layouts.app', ['navActive' => 'home'])
@section('title', 'TruSaba · My Itineraries')
@section('content')

<div class="app-header">
    <div class="title-block">
        <p class="eyebrow">Itinerary</p>
        <h1>My Trips</h1>
    </div>
</div>

<div class="app-body">
    <div class="pad" style="padding-top:16px">
        @forelse($itineraries as $itinerary)
        <div class="list-card-wrapper" style="position:relative;margin-bottom:12px">
            <a class="list-card" href="{{ route('itineraries.show', $itinerary->id) }}">
                <div class="ph-img thumb" style="background:linear-gradient(145deg,oklch(0.55 0.12 255 / 0.35),oklch(0.8 0.1 160 / 0.4))"></div>
                <div class="meta">
                    <h3>{{ $itinerary->title ?: $itinerary->destination }}</h3>
                    <p class="small muted">{{ $itinerary->start_date->format('d M') }} – {{ $itinerary->end_date->format('d M Y') }} · {{ $itinerary->duration_days }} days</p>
                    @php
                        $statusBadges = [
                            'draft' => ['badge-blue', 'Draft'],
                            'confirmed' => ['badge-gold', 'Confirmed'],
                            'ongoing' => ['badge-success', 'Ongoing'],
                            'completed' => ['badge-success', 'Completed'],
                            'cancelled' => ['badge-danger', 'Cancelled'],
                            'processing' => ['badge-warn', 'Processing'],
                            'failed' => ['badge-danger', 'Failed'],
                        ];
                        [$badgeClass, $badgeLabel] = $statusBadges[$itinerary->status] ?? ['badge-blue', $itinerary->status];
                    @endphp
                    <span class="badge {{ $badgeClass }}" style="margin-top:4px">{{ $badgeLabel }}</span>
                </div>
                @if($itinerary->estimated_budget)
                <span class="amount muted small">Rp {{ number_format($itinerary->estimated_budget / 1000000, 1, ',', '.') }}M</span>
                @endif
            </a>
            @if($itinerary->status === 'draft')
            <form method="POST" action="{{ route('itineraries.destroy', $itinerary->id) }}"
                  onsubmit="return confirm('Delete this draft itinerary?')"
                  style="position:absolute;bottom:8px;right:8px">
                @csrf @method('DELETE')
                <button type="submit" class="icon-btn" aria-label="Delete" style="color:var(--danger);background:rgba(255,255,255,0.9);border-radius:50%;width:32px;height:32px">
                    <svg viewBox="0 0 24 24" width="16" height="16"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </form>
            @endif
        </div>
        @empty
        <div class="card" style="text-align:center;padding:40px 20px">
            <h2>No itineraries yet</h2>
            <p class="muted small" style="margin:8px 0 16px">Create your first itinerary with TruSaba AI.</p>
            <a href="{{ route('onboarding') }}" class="btn btn-primary">Create New Itinerary</a>
        </div>
        @endforelse

        @if($itineraries->isNotEmpty())
        <a href="{{ route('onboarding') }}" class="btn btn-primary btn-block" style="margin-top:8px">+ Create New Itinerary</a>
        @endif
    </div>
</div>

<nav class="bottom-nav">
    <a class="nav-item active" href="{{ route('itineraries.index') }}">
        <svg viewBox="0 0 24 24"><path d="M4 10.5L12 4l8 6.5V20a1 1 0 01-1 1h-5v-6H10v6H5a1 1 0 01-1-1v-9.5z"/></svg>
        Home
    </a>
    <a class="nav-item" href="{{ route('bookings.index') }}">
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
