@extends('layouts.app', ['showSos' => true, 'navActive' => 'home'])
@section('title', 'TruSaba · Today')
@section('content')

<div class="app-header">
    <div class="title-block">
        <p class="eyebrow">On-trip · Day {{ $dayNumber ?? '-' }}</p>
        <h1>Today</h1>
    </div>
    <span class="badge badge-success">Live</span>
</div>

<div class="app-body">
    <div class="pad">
        <div class="card-soft" style="margin-bottom:16px">
            <p class="small muted">{{ $today->translatedFormat('D, d M Y') }} · {{ $itinerary?->destination ?? 'No trip yet' }}</p>
            <h2 style="margin-top:4px">{{ $itinerary ? 'Stay excited, traveller!' : 'Ready to explore?' }}</h2>
            <p class="small muted" style="margin-top:4px">
                @if($todayItems->isNotEmpty())
                {{ $todayItems->count() }} activities · sunny 29°
                @else
                No schedule for today
                @endif
            </p>
        </div>

        <div class="row-between" style="margin-bottom:10px">
            <h2>Today's schedule</h2>
            @if($itinerary)
            <a href="{{ route('itineraries.show', $itinerary->id) }}" class="btn btn-ghost btn-sm">View all</a>
            @endif
        </div>

        @if($todayItems->isNotEmpty())
        <div class="timeline">
            @foreach($todayItems as $item)
            @php
                $now = now()->format('H:i');
                $itemTime = \Carbon\Carbon::parse($item->schedule_time)->format('H:i');
                $isPast = $itemTime < $now;
                $isCurrent = $itemTime >= $now && ($loop->first || \Carbon\Carbon::parse($todayItems[$loop->index - 1]->schedule_time)->format('H:i') < $now);
                $types = ['hotel' => 'act-hotel', 'restaurant' => 'act-food', 'attraction' => 'act-place', 'transport' => 'act-transport', 'shopping' => 'act-shop'];
                $svgPaths = [
                    'hotel' => '<path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-6h6v6"/><path d="M9 10h.01M15 10h.01"/>',
                    'restaurant' => '<path d="M4 11h16v2a6 6 0 01-6 6H10a6 6 0 01-6-6v-2z"/><path d="M8 11V5M12 11V3M16 11V6"/>',
                    'attraction' => '<path d="M12 21s-7-5.5-7-11a7 7 0 0114 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
                    'transport' => '<rect x="3" y="8" width="18" height="10" rx="2"/><path d="M6 18v2M18 18v2M3 12h18"/>',
                    'shopping' => '<path d="M6 7h12l1 13H5L6 7z"/><path d="M9 7a3 3 0 016 0"/>',
                ];
                $actClass = $types[$item->type] ?? 'act-place';
                $svg = $svgPaths[$item->type] ?? '<circle cx="12" cy="12" r="4"/>';
            @endphp
            <div class="tl-item">
                <div class="tl-dot {{ $isPast ? 'muted' : '' }}{{ $item->type === 'restaurant' && !$isPast ? ' gold' : '' }}"></div>
                <p class="tl-time">{{ $itemTime }} · {{ $isPast ? 'Done' : ($isCurrent ? 'Now' : '') }}</p>
                <div class="card" style="{{ $isPast ? 'opacity:0.72' : '' }}{{ $isCurrent ? 'border-color:var(--accent-hex)' : '' }}">
                    <div class="row">
                        <div class="act-icon {{ $actClass }}">
                            <svg viewBox="0 0 24 24">{!! $svg !!}</svg>
                        </div>
                        <div style="flex:1">
                            <h3>{{ $item->name }}</h3>
                            @if($item->description)
                            <p class="small muted">{{ $item->description }}</p>
                            @endif
                        </div>
                        @if($isPast)
                        <span class="badge badge-success">Done</span>
                        @elseif($isCurrent)
                        <span class="badge badge-blue">Next</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="card" style="text-align:center;padding:32px 20px">
            <p class="muted">No schedule for today.</p>
            <a href="{{ route('itineraries.index') }}" class="btn btn-primary btn-sm" style="margin-top:12px">View Itinerary</a>
        </div>
        @endif

        @if($vouchers->isNotEmpty())
        <h2 style="margin:20px 0 10px">Active vouchers</h2>
        @foreach($vouchers as $voucher)
        <a class="list-card" href="{{ route('bookings.success', $voucher->id) }}" style="margin-bottom:12px">
            <div class="ph-img thumb" style="background:linear-gradient(145deg,oklch(0.55 0.1 255 / 0.25),oklch(0.85 0.12 87 / 0.3))"></div>
            <div class="meta">
                <h3>{{ $voucher->merchant->name }}</h3>
                <p class="small muted">{{ $voucher->voucher_code }} · 
                    @if($voucher->check_out_date)
                    Check-out {{ $voucher->check_out_date->format('d M') }}
                    @endif
                </p>
                <span class="badge badge-gold" style="margin-top:4px">{{ $voucher->booking_type === 'hotel' ? 'Hotel' : 'Booking' }}</span>
            </div>
        </a>
        @endforeach
        @endif
    </div>
</div>

@push('scripts')
<script>
document.getElementById('btnSendSos')?.addEventListener('click', function() {
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('sos.send') }}';
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="message" value="Emergency signal">';
    document.body.appendChild(form);
    form.submit();

    // Show the "sent" state
    document.getElementById('sosConfirm').hidden = true;
    document.getElementById('sosSent').hidden = false;
});
</script>
@endpush
@endsection
