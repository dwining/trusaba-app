@extends('layouts.app', ['navActive' => 'chat'])

@section('title', 'TruSaba · Messages')

@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ route('chat') }}" aria-label="Back">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">💬 Messages</p>
        <h1>Direct Messages</h1>
    </div>
</div>

<div class="app-body">
    <div class="pad" style="padding-top:8px">

        {{-- Active chats --}}
        @if($active->isNotEmpty())
        <p class="caption" style="margin-bottom:8px">Active Chats</p>
        @foreach($active as $req)
        @php
            $other = $req->from_user_id === Auth::id() ? $req->toUser : $req->fromUser;
        @endphp
        <a href="{{ route('chat.private.show', $req->chat_room_id) }}" class="card" style="display:block;margin-bottom:8px;text-decoration:none">
            <div class="row" style="align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:50%;background:var(--accent-hex);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700">
                    {{ strtoupper(mb_substr(explode(' ', trim($other->name))[0], 0, 2)) }}
                </div>
                <div style="flex:1">
                    <h3 style="font-size:14px">{{ $other->name }}</h3>
                </div>
                <svg viewBox="0 0 24 24" width="16" height="16" style="color:var(--muted)"><path d="M9 18l6-6-6-6"/></svg>
            </div>
        </a>
        @endforeach
        @endif

        {{-- Pending requests --}}
        @if($incoming->isNotEmpty())
        <p class="caption" style="margin-bottom:8px;margin-top:12px">Requests</p>
        @foreach($incoming as $req)
        <div class="card" style="margin-bottom:8px">
            <div class="row" style="align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:50%;background:var(--muted);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0">
                    {{ strtoupper(mb_substr(explode(' ', trim($req->fromUser->name))[0], 0, 2)) }}
                </div>
                <div style="flex:1;min-width:0">
                    <h3 style="font-size:14px">{{ $req->fromUser->name }}</h3>
                    <p class="small muted">Wants to chat</p>
                </div>
                <div class="row" style="gap:6px;flex-shrink:0">
                    <form method="POST" action="{{ route('chat.requests.accept', $req->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm" style="background:var(--accent-hex);color:#fff;border:none;border-radius:6px;padding:6px 12px">Accept</button>
                    </form>
                    <form method="POST" action="{{ route('chat.requests.reject', $req->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm" style="border:1px solid var(--danger);color:var(--danger);background:transparent;border-radius:6px;padding:6px 10px">Reject</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
        @endif

        {{-- Sent requests --}}
        @if($sent->isNotEmpty())
        <p class="caption" style="margin-bottom:8px;margin-top:12px">Sent</p>
        @foreach($sent as $req)
        <div class="card" style="margin-bottom:8px;opacity:0.7">
            <div class="row" style="align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:50%;background:var(--muted);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700">
                    {{ strtoupper(mb_substr(explode(' ', trim($req->toUser->name))[0], 0, 2)) }}
                </div>
                <div style="flex:1">
                    <h3 style="font-size:14px">{{ $req->toUser->name }}</h3>
                    <p class="small muted">Pending...</p>
                </div>
            </div>
        </div>
        @endforeach
        @endif

        @if($incoming->isEmpty() && $active->isEmpty() && $sent->isEmpty())
        <div class="card" style="text-align:center;padding:30px">
            <p class="muted">No messages yet. Join a community room and click on a user to start a DM.</p>
        </div>
        @endif

    </div>
</div>

@endsection
