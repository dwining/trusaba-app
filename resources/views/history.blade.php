@extends('layouts.app', ['navActive' => 'profile'])
@section('title', 'TruSaba · History')
@section('content')

<div class="app-header">
    <div class="title-block">
        <p class="eyebrow">Profile</p>
        <h1>History</h1>
    </div>
    <a class="icon-btn" href="{{ route('expenses.upload') }}" aria-label="Upload">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
    </a>
</div>

<div class="tabs">
    <button type="button" class="tab {{ $tab === 'trips' ? 'active' : '' }}" onclick="showTab('trips')">Travel History</button>
    <button type="button" class="tab {{ $tab === 'tx' ? 'active' : '' }}" onclick="showTab('tx')">Transaction History</button>
    <button type="button" class="tab {{ $tab === 'profile' ? 'active' : '' }}" onclick="showTab('profile')">My Profile</button>
</div>

{{-- Profile completion banner --}}
@php $profile = Auth::user()->travellerProfile; @endphp
@if(!$profile || !$profile->birth_date)
<div class="pad" style="margin-bottom:12px">
    <div class="card" style="background:linear-gradient(145deg,oklch(0.55 0.18 255 / 0.08),oklch(0.85 0.17 87 / 0.1));border-color:oklch(0.55 0.18 255 / 0.25)">
        <div class="row" style="gap:12px">
            <div class="act-icon act-hotel" style="flex-shrink:0">
                <svg viewBox="0 0 24 24" style="width:20px;height:20px"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div style="flex:1;min-width:0">
                <h3 style="font-size:14px">Complete your profile</h3>
                <p class="small muted">Birth date & preferences help TruSaba AI make more personal itineraries.</p>
            </div>
            <a href="{{ route('onboarding') }}" class="btn btn-primary btn-sm" style="flex-shrink:0">Fill Now</a>
        </div>
    </div>
</div>
@endif

<div class="app-body">
    <div class="pad stack" id="panelTrips" {{ $tab !== 'trips' ? 'hidden' : '' }}>
        @forelse($itineraries as $itin)
        <a class="list-card" href="{{ route('itineraries.show', $itin->id) }}">
            <div class="ph-img thumb" style="background:linear-gradient(145deg,oklch(0.55 0.12 255 / 0.35),oklch(0.8 0.1 160 / 0.4))"></div>
            <div class="meta">
                <h3>{{ $itin->title ?: $itin->destination }}</h3>
                <p class="small muted">{{ $itin->start_date->format('d M') }}–{{ $itin->end_date->format('d M Y') }} · {{ $itin->duration_days }} days</p>
                @php
                    $badges = [
                        'draft' => ['badge-blue', 'Draft'],
                        'confirmed' => ['badge-gold', 'Confirmed'],
                        'ongoing' => ['badge-success', 'On trip'],
                        'completed' => ['badge-blue', 'Completed'],
                        'cancelled' => ['badge-danger', 'Cancelled'],
                    ];
                    [$bc, $bl] = $badges[$itin->status] ?? ['badge-blue', $itin->status];
                @endphp
                <span class="badge {{ $bc }}" style="margin-top:4px">{{ $bl }}</span>
            </div>
            @if($itin->estimated_budget)
            <span class="amount muted small">Rp {{ number_format($itin->estimated_budget / 1000000, 1, ',', '.') }}M</span>
            @endif
        </a>
        @empty
        <div class="card" style="text-align:center;padding:32px">
            <p class="muted">No travel history yet.</p>
        </div>
        @endforelse
    </div>

    <div class="pad stack" id="panelTx" {{ $tab !== 'tx' ? 'hidden' : '' }}>
        @forelse($transactions as $tx)
        <div class="list-card">
            <div class="meta">
                <h3>{{ $tx->booking?->merchant?->name ?? 'Transaction' }}</h3>
                <p class="small muted">{{ $tx->created_at->format('d M') }} · {{ $tx->booking?->booking_type ?? 'Other' }}</p>
                @php
                    $txb = ['pending' => ['badge-warn', 'Pending'], 'paid' => ['badge-success', 'Paid'], 'failed' => ['badge-danger', 'Failed'], 'refunded' => ['badge-blue', 'Refunded']];
                    [$txbc, $txbl] = $txb[$tx->status] ?? ['badge-blue', $tx->status];
                @endphp
                <span class="badge {{ $txbc }}" style="margin-top:4px">{{ $txbl }}</span>
            </div>
            <span class="amount">Rp {{ number_format($tx->amount, 0, ',', '.') }}</span>
        </div>
        @empty
        <div class="card" style="text-align:center;padding:32px">
            <p class="muted">No transactions yet.</p>
        </div>
        @endforelse
        <a class="btn btn-secondary btn-block" href="{{ route('expenses.upload') }}" style="margin-top:8px">+ Upload new receipt</a>
    </div>

    <div class="pad stack" id="panelProfile" {{ $tab !== 'profile' ? 'hidden' : '' }}>
        @php $profile = Auth::user()->travellerProfile; @endphp
        <div class="card-soft stack-sm" style="margin-bottom:12px">
            <div class="row-between">
                <span class="muted small">Name</span>
                <span class="small" style="font-weight:600">{{ Auth::user()->name }}</span>
            </div>
            <div class="row-between">
                <span class="muted small">Email</span>
                <span class="small" style="font-weight:600">{{ Auth::user()->email }}</span>
            </div>
            <div class="row-between">
                <span class="muted small">Date of birth</span>
                <span class="small" style="font-weight:600">{{ $profile?->birth_date?->format('d M Y') ?? 'Not filled' }}</span>
            </div>
            <div class="row-between">
                <span class="muted small">Phone</span>
                <span class="small" style="font-weight:600">{{ $profile?->phone ?? 'Not filled' }}</span>
            </div>
            <div class="row-between">
                <span class="muted small">Hobbies</span>
                <span class="small" style="font-weight:600">{{ ! empty($profile?->hobbies) ? implode(', ', $profile->hobbies) : 'Not filled' }}</span>
            </div>
            <div class="row-between">
                <span class="muted small">Interests</span>
                <span class="small" style="font-weight:600">{{ ! empty($profile?->interests) ? implode(', ', $profile->interests) : 'Not filled' }}</span>
            </div>

        </div>
        <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-block">Edit Profile</a>
    </div>
</div>

{{-- Logout --}}
<div class="pad" style="margin-top:8px;margin-bottom:16px">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-ghost btn-block" style="color:var(--danger)">
            <svg viewBox="0 0 24 24" style="width:16px;height:16px"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
            Logout
        </button>
    </form>
</div>

@push('scripts')
<script>
function showTab(id) {
    document.getElementById('panelTrips').hidden = id !== 'trips';
    document.getElementById('panelTx').hidden = id !== 'tx';
    document.getElementById('panelProfile').hidden = id !== 'profile';
    document.querySelectorAll('.tab').forEach(function(t) {
        var text = t.textContent.trim();
        var match = (id === 'trips' && text.includes('Travel'))
                    || (id === 'tx' && text.includes('Transaction'))
                    || (id === 'profile' && text.includes('Profile'));
        t.classList.toggle('active', match);
    });
}
</script>
@endpush
@endsection
