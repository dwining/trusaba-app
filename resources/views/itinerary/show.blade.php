@extends('layouts.app', ['navActive' => 'home'])
@section('title', 'TruSaba · Itinerary ' . $itinerary->destination)
@section('content')

@if($itinerary->status === 'processing')
<script>window.location.href = '{{ route('itineraries.loading', $itinerary->id) }}';</script>
@endif

@if($itinerary->status === 'failed')
<div class="app-body" style="display:flex;align-items:center;justify-content:center">
    <div style="text-align:center;padding:40px 20px">
        <div class="modal-icon" style="margin:0 auto 12px">
            <svg viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.3 4.3L2.8 18a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 4.3a2 2 0 00-3.4 0z"/></svg>
        </div>
        <h2>Gagal membuat itinerary</h2>
        <p class="muted">AI tidak bisa menyusun itinerary kali ini. Coba lagi ya.</p>
        <a href="{{ route('onboarding') }}" class="btn btn-primary" style="margin-top:12px">Coba Lagi</a>
    </div>
</div>
@else

<div class="app-header">
    <a class="icon-btn" href="{{ route('itineraries.index') }}" aria-label="Kembali">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">Itinerary AI</p>
        <h1>{{ $itinerary->destination }} · {{ $itinerary->duration_days }} Hari</h1>
    </div>
    <span class="badge badge-gold">AI Pick</span>
</div>

<div class="app-body">
    <div class="pad">
        {{-- Budget Summary --}}
        <div class="card" style="margin-bottom:8px">
            <div class="row-between" style="margin-bottom:12px">
                <div>
                    <p class="caption">Total estimasi</p>
                    <p class="mono" style="font-size:22px;font-weight:600;letter-spacing:-0.02em;color:var(--accent-hex)">
                        Rp {{ number_format($itinerary->estimated_budget, 0, ',', '.') }}
                    </p>
                </div>
                <div style="text-align:right">
                    <p class="caption">Budgetmu</p>
                    <p class="mono small" style="font-weight:600">
                        Rp {{ number_format($itinerary->budget_input, 0, ',', '.') }}
                    </p>
                    @php
                        $overBudget = $itinerary->estimated_budget > $itinerary->budget_input;
                    @endphp
                    <span class="badge {{ $overBudget ? 'badge-warn' : 'badge-success' }}" style="margin-top:4px">
                        {{ $overBudget ? 'Over budget' : 'Dalam budget' }}
                    </span>
                </div>
            </div>

            {{-- Budget breakdown bar chart --}}
            @php
                $types = ['hotel' => ['Akomodasi', 'gold'], 'attraction' => ['Wisata', 'gold'],
                           'restaurant' => ['Makan', 'green'], 'transport' => ['Transport', 'muted'],
                           'shopping' => ['Oleh-oleh', 'warn']];
                $maxBudget = max($itinerary->estimated_budget, 1);
                $totals = [];
                foreach ($itinerary->itineraryItems as $item) {
                    $cat = $item->type;
                    if (!isset($totals[$cat])) $totals[$cat] = 0;
                    $totals[$cat] += $item->estimated_cost;
                }
            @endphp
            <div class="bar-chart">
                @foreach($types as $type => [$label, $color])
                @php $val = $totals[$type] ?? 0; $pct = min(100, round(($val / $maxBudget) * 100)); @endphp
                <div class="bar-row">
                    <span class="label">{{ $label }}</span>
                    <div class="bar-track">
                        <div class="bar-fill {{ $color }}" style="width:{{ $pct }}%"></div>
                    </div>
                    <span class="val">Rp {{ number_format($val / 1000, 0, ',', '.') }}rb</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Day sections --}}
        @foreach($days as $dayNumber => $items)
        @php
            $firstItem = $items->first();
            $date = \Carbon\Carbon::parse($itinerary->start_date)->addDays($dayNumber - 1);
            $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $dayLabel = $dayNames[$date->dayOfWeek] . ', ' . $date->format('d M');
        @endphp
        <div class="day-head">
            <h2>Day {{ $dayNumber }} · {{ $dayLabel }}</h2>
            <span class="badge badge-blue">{{ $itinerary->destination }}</span>
        </div>

        <div class="timeline">
            @foreach($items as $item)
            @php
                $typeIcons = [
                    'hotel' => 'act-hotel',
                    'restaurant' => 'act-food',
                    'attraction' => 'act-place',
                    'transport' => 'act-transport',
                    'shopping' => 'act-shop',
                    'other' => 'act-place',
                ];
                $typeSVG = [
                    'hotel' => '<path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-6h6v6"/><path d="M9 10h.01M15 10h.01"/>',
                    'restaurant' => '<path d="M4 11h16v2a6 6 0 01-6 6H10a6 6 0 01-6-6v-2z"/><path d="M8 11V5M12 11V3M16 11V6"/>',
                    'attraction' => '<path d="M12 21s-7-5.5-7-11a7 7 0 0114 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
                    'transport' => '<rect x="3" y="8" width="18" height="10" rx="2"/><path d="M6 18v2M18 18v2M3 12h18"/>',
                    'shopping' => '<path d="M6 7h12l1 13H5L6 7z"/><path d="M9 7a3 3 0 016 0"/>',
                    'other' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
                ];
                $actClass = $typeIcons[$item->type] ?? 'act-place';
                $svgContent = $typeSVG[$item->type] ?? $typeSVG['other'];
            @endphp
            <div class="tl-item">
                <div class="tl-dot {{ $item->type === 'restaurant' ? 'gold' : '' }}"></div>
                <p class="tl-time">{{ \Carbon\Carbon::parse($item->schedule_time)->format('H:i') }}</p>
                <div class="card">
                    <div class="row" style="align-items:flex-start">
                        <div class="act-icon {{ $actClass }}">
                            <svg viewBox="0 0 24 24">{!! $svgContent !!}</svg>
                        </div>
                        <div style="flex:1;min-width:0">
                            <h3>{{ $item->name }}</h3>
                            @if($item->description)
                            <p class="small muted">{{ $item->description }}</p>
                            @endif
                            <p class="mono small" style="font-weight:600;margin-top:4px">
                                Rp {{ number_format($item->estimated_cost, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    @if($item->is_bookable)
                    <a class="btn btn-primary btn-sm btn-block" style="margin-top:12px" href="{{ route('bookings.index') }}">
                        Booking Sekarang
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endforeach

        {{-- Action buttons --}}
        <div class="stack" style="margin:24px 0">
            <form method="POST" action="{{ route('itineraries.update', $itinerary->id) }}" style="display:inline">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="confirmed">
                <button type="submit" class="btn btn-primary btn-block">Konfirmasi & Simpan</button>
            </form>
            <button type="button" class="btn btn-secondary btn-block" onclick="history.back()">Edit Itinerary</button>
        </div>
    </div>
</div>

<nav class="bottom-nav">
    <a class="nav-item active" href="{{ route('itineraries.show', $itinerary->id) }}">
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
@endif

@endsection
