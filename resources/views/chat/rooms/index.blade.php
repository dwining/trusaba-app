@extends('layouts.app', ['navActive' => 'chat'])

@section('title', 'TruSaba · Community')

@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ route('chat') }}" aria-label="Back">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">👥 Community</p>
        <h1>Traveler Community</h1>
    </div>
</div>

<div class="app-body">
    <div class="pad" style="padding-top:8px">

        @forelse($rooms as $room)
        @php $canSend = in_array($room->id, $memberRoomIds); @endphp
        <a href="{{ route('chat.rooms.show', $room->id) }}" class="card" style="display:block;margin-bottom:8px;text-decoration:none">
            <div class="row" style="align-items:center;gap:12px">
                <div style="width:44px;height:44px;border-radius:10px;background:{{ $canSend ? 'var(--accent-hex)' : 'var(--muted)' }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:700;flex-shrink:0;opacity:{{ $canSend ? '1' : '0.5' }}">
                    {{ strtoupper(substr($room->destination ?? '?', 0, 2)) }}
                </div>
                <div style="flex:1;min-width:0">
                    <h3 style="font-size:14px">{{ $room->name }}</h3>
                    <p class="small muted">{{ $canSend ? 'You can chat here' : 'View only' }}</p>
                </div>
                @if($canSend)
                <span class="badge badge-success">Joined</span>
                @else
                <svg viewBox="0 0 24 24" width="18" height="18" style="flex-shrink:0;color:var(--muted)"><path d="M9 18l6-6-6-6"/></svg>
                @endif
            </div>
        </a>
        @empty
        <div class="card" style="text-align:center;padding:30px">
            <p class="muted">No rooms available.</p>
        </div>
        @endforelse

    </div>
</div>

@endsection
