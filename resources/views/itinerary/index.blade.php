@extends('layouts.app', ['navActive' => 'home'])
@section('title', 'TruSaba · Itinerary Saya')
@section('content')

<div class="app-header">
    <div class="title-block">
        <p class="eyebrow">Itinerary</p>
        <h1>Perjalanan Saya</h1>
    </div>
</div>

<div class="app-body">
    <div class="pad" style="padding-top:16px">
        @forelse($itineraries as $itinerary)
        <a class="list-card" href="{{ route('itineraries.show', $itinerary->id) }}" style="margin-bottom:12px">
            <div class="ph-img thumb" style="background:linear-gradient(145deg,oklch(0.55 0.12 255 / 0.35),oklch(0.8 0.1 160 / 0.4))"></div>
            <div class="meta">
                <h3>{{ $itinerary->title ?: $itinerary->destination }}</h3>
                <p class="small muted">{{ $itinerary->start_date->format('d M') }} – {{ $itinerary->end_date->format('d M Y') }} · {{ $itinerary->duration_days }} hari</p>
                @php
                    $statusBadges = [
                        'draft' => ['badge-blue', 'Draft'],
                        'confirmed' => ['badge-gold', 'Dikonfirmasi'],
                        'ongoing' => ['badge-success', 'Berjalan'],
                        'completed' => ['badge-success', 'Selesai'],
                        'cancelled' => ['badge-danger', 'Dibatalkan'],
                        'processing' => ['badge-warn', 'Memproses'],
                        'failed' => ['badge-danger', 'Gagal'],
                    ];
                    [$badgeClass, $badgeLabel] = $statusBadges[$itinerary->status] ?? ['badge-blue', $itinerary->status];
                @endphp
                <span class="badge {{ $badgeClass }}" style="margin-top:4px">{{ $badgeLabel }}</span>
            </div>
            @if($itinerary->estimated_budget)
            <span class="amount muted small">Rp {{ number_format($itinerary->estimated_budget / 1000000, 1, ',', '.') }}jt</span>
            @endif
        </a>
        @empty
        <div class="card" style="text-align:center;padding:40px 20px">
            <h2>Belum ada itinerary</h2>
            <p class="muted small" style="margin:8px 0 16px">Buat itinerary pertamamu dengan AI TruSaba.</p>
            <a href="{{ route('onboarding') }}" class="btn btn-primary">Buat Itinerary Baru</a>
        </div>
        @endforelse

        @if($itineraries->isNotEmpty())
        <a href="{{ route('onboarding') }}" class="btn btn-primary btn-block" style="margin-top:8px">+ Buat Itinerary Baru</a>
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
        Profil
    </a>
</nav>

@endsection
