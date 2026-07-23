@extends('layouts.app', ['navActive' => 'chat'])

@section('title', 'TruSaba · Chat')

@section('content')

<div class="app-header">
    <div class="title-block">
        <p class="eyebrow">Chat</p>
        <h1>TruSaba Chat</h1>
    </div>
</div>

<div class="app-body">
    <div class="pad" style="padding-top:16px">

        {{-- AI Chat --}}
        <a href="{{ route('chat.ai') }}" class="card" style="display:block;margin-bottom:12px;text-decoration:none">
            <div class="row" style="align-items:center;gap:14px">
                <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,oklch(0.55 0.12 255 / 0.2),oklch(0.75 0.1 200 / 0.3));display:flex;align-items:center;justify-content:center;font-size:22px">
                    🤖
                </div>
                <div style="flex:1;min-width:0">
                    <h3 style="font-size:15px;margin-bottom:2px">TruSaba AI Assistant</h3>
                    <p class="small muted">Get travel tips, destination recommendations, and 24/7 trip support.</p>
                </div>
                <svg viewBox="0 0 24 24" width="20" height="20" style="flex-shrink:0;color:var(--muted)"><path d="M9 18l6-6-6-6"/></svg>
            </div>
        </a>

        {{-- Community Chat --}}
        <a href="{{ route('chat.rooms') }}" class="card" style="display:block;text-decoration:none">
            <div class="row" style="align-items:center;gap:14px">
                <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,oklch(0.58 0.22 27 / 0.15),oklch(0.85 0.17 87 / 0.2));display:flex;align-items:center;justify-content:center;font-size:22px">
                    👥
                </div>
                <div style="flex:1;min-width:0">
                    <h3 style="font-size:15px;margin-bottom:2px">Traveler Community</h3>
                    <p class="small muted">Chat with fellow travelers, share tips, and connect.</p>
                </div>
                <svg viewBox="0 0 24 24" width="20" height="20" style="flex-shrink:0;color:var(--muted)"><path d="M9 18l6-6-6-6"/></svg>
            </div>
        </a>

        {{-- Direct Messages --}}
        <a href="{{ route('chat.requests') }}" class="card" style="display:block;text-decoration:none;margin-top:12px">
            <div class="row" style="align-items:center;gap:14px">
                <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,oklch(0.6 0.15 180 / 0.15),oklch(0.7 0.1 250 / 0.2));display:flex;align-items:center;justify-content:center;font-size:22px">
                    💬
                </div>
                <div style="flex:1;min-width:0">
                    <h3 style="font-size:15px;margin-bottom:2px">Direct Messages</h3>
                    <p class="small muted">Private conversations with fellow travelers.</p>
                </div>
                <svg viewBox="0 0 24 24" width="20" height="20" style="flex-shrink:0;color:var(--muted)"><path d="M9 18l6-6-6-6"/></svg>
            </div>
        </a>

    </div>
</div>

@endsection
