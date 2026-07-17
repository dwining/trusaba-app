@extends('layouts.app', ['navActive' => 'profile'])
@section('title', 'TruSaba · History')
@section('content')

<div class="app-header">
    <img class="logo" src="{{ asset('logo.jpeg') }}" alt="TruSaba" />
    <div class="title-block">
        <p class="eyebrow">Profil</p>
        <h1>Riwayat</h1>
    </div>
    <a class="icon-btn" href="{{ route('expenses.upload') }}" aria-label="Upload">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
    </a>
</div>

<div class="tabs">
    <button type="button" class="tab {{ $tab === 'trips' ? 'active' : '' }}" onclick="showTab('trips')">Riwayat Traveling</button>
    <button type="button" class="tab {{ $tab === 'tx' ? 'active' : '' }}" onclick="showTab('tx')">Riwayat Transaksi</button>
</div>

<div class="app-body">
    <div class="pad stack" id="panelTrips" {{ $tab !== 'trips' ? 'hidden' : '' }}>
        @forelse($itineraries as $itin)
        <a class="list-card" href="{{ route('itineraries.show', $itin->id) }}">
            <div class="ph-img thumb" style="background:linear-gradient(145deg,oklch(0.55 0.12 255 / 0.35),oklch(0.8 0.1 160 / 0.4))"></div>
            <div class="meta">
                <h3>{{ $itin->title ?: $itin->destination }}</h3>
                <p class="small muted">{{ $itin->start_date->format('d M') }}–{{ $itin->end_date->format('d M Y') }} · {{ $itin->duration_days }} hari</p>
                @php
                    $badges = [
                        'draft' => ['badge-blue', 'Draft'],
                        'confirmed' => ['badge-gold', 'Dikonfirmasi'],
                        'ongoing' => ['badge-success', 'On trip'],
                        'completed' => ['badge-blue', 'Selesai'],
                        'cancelled' => ['badge-danger', 'Dibatalkan'],
                    ];
                    [$bc, $bl] = $badges[$itin->status] ?? ['badge-blue', $itin->status];
                @endphp
                <span class="badge {{ $bc }}" style="margin-top:4px">{{ $bl }}</span>
            </div>
            @if($itin->estimated_budget)
            <span class="amount muted small">Rp {{ number_format($itin->estimated_budget / 1000000, 1, ',', '.') }}jt</span>
            @endif
        </a>
        @empty
        <div class="card" style="text-align:center;padding:32px">
            <p class="muted">Belum ada riwayat perjalanan.</p>
        </div>
        @endforelse
    </div>

    <div class="pad stack" id="panelTx" {{ $tab !== 'tx' ? 'hidden' : '' }}>
        @forelse($transactions as $tx)
        <div class="list-card">
            <div class="meta">
                <h3>{{ $tx->booking?->merchant?->name ?? 'Transaksi' }}</h3>
                <p class="small muted">{{ $tx->created_at->format('d M') }} · {{ $tx->booking?->booking_type ?? 'Lainnya' }}</p>
                @php
                    $txb = ['pending' => ['badge-warn', 'Pending'], 'paid' => ['badge-success', 'Lunas'], 'failed' => ['badge-danger', 'Gagal'], 'refunded' => ['badge-blue', 'Refund']];
                    [$txbc, $txbl] = $txb[$tx->status] ?? ['badge-blue', $tx->status];
                @endphp
                <span class="badge {{ $txbc }}" style="margin-top:4px">{{ $txbl }}</span>
            </div>
            <span class="amount">Rp {{ number_format($tx->amount, 0, ',', '.') }}</span>
        </div>
        @empty
        <div class="card" style="text-align:center;padding:32px">
            <p class="muted">Belum ada transaksi.</p>
        </div>
        @endforelse
        <a class="btn btn-secondary btn-block" href="{{ route('expenses.upload') }}" style="margin-top:8px">+ Upload bukti baru</a>
    </div>
</div>

@push('scripts')
<script>
function showTab(id) {
    document.getElementById('panelTrips').hidden = id !== 'trips';
    document.getElementById('panelTx').hidden = id !== 'tx';
    document.querySelectorAll('.tab').forEach(function(t) {
        t.classList.toggle('active', t.textContent.includes(id === 'trips' ? 'Traveling' : 'Transaksi'));
    });
}
</script>
@endpush
@endsection
