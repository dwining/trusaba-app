@php
    $activeItinerary = Auth::user()->itineraries()
        ->whereIn('status', ['confirmed', 'ongoing', 'draft'])
        ->latest()
        ->first();
    $upcomingTrips = Auth::user()->itineraries()
        ->whereIn('status', ['confirmed', 'draft'])
        ->whereDate('start_date', '>=', now())
        ->count();
    $activeBookings = Auth::user()->bookings()
        ->whereIn('status', ['confirmed', 'checked_in'])
        ->count();
    $profile = Auth::user()->travellerProfile;
@endphp

@extends('layouts.app', ['navActive' => 'home'])
@section('title', 'TruSaba')
@section('content')

<div class="app-header">
    <div class="title-block">
        <p class="eyebrow">Dashboard</p>
        <h1>Hello, {{ explode(' ', Auth::user()->name)[0] }}</h1>
    </div>
</div>

<div class="app-body">
    <div class="pad">
        {{-- Profile completion banner --}}
        @if(!$profile || !$profile->birth_date)
        <div class="card" style="background:linear-gradient(145deg,oklch(0.55 0.18 255 / 0.08),oklch(0.85 0.17 87 / 0.1));border-color:oklch(0.55 0.18 255 / 0.25);margin-bottom:16px">
            <div class="row" style="gap:10px">
                <div style="flex:1;min-width:0">
                    <h3 style="font-size:14px">Complete your profile first</h3>
                    <p class="small muted">So TruSaba AI can create the perfect itinerary for you.</p>
                </div>
                <a href="{{ route('onboarding') }}" class="btn btn-primary btn-sm">Fill Profile</a>
            </div>
        </div>
        @endif

        {{-- Active Trip --}}
        @if($activeItinerary)
        <a class="card" href="{{ route('itineraries.show', $activeItinerary->id) }}" style="display:block;text-decoration:none;color:inherit;margin-bottom:12px;border-color:oklch(0.55 0.18 255 / 0.3)">
            <div class="row-between" style="margin-bottom:8px">
                <span class="badge {{ $activeItinerary->status === 'ongoing' ? 'badge-success' : 'badge-gold' }}">
                    {{ $activeItinerary->status === 'ongoing' ? 'In progress' : 'Planned' }}
                </span>
                <span class="caption">{{ $activeItinerary->start_date->format('d M') }} – {{ $activeItinerary->end_date->format('d M Y') }}</span>
            </div>
            <h2>{{ $activeItinerary->title ?: 'Trip to ' . $activeItinerary->destination }}</h2>
            <p class="small muted" style="margin-top:4px">{{ $activeItinerary->destination }} · {{ $activeItinerary->duration_days }} days · {{ $activeItinerary->total_participants }} people</p>
            @if($activeItinerary->estimated_budget)
            <p class="mono small" style="font-weight:600;color:var(--accent-hex);margin-top:6px">Estimate: Rp {{ number_format($activeItinerary->estimated_budget, 0, ',', '.') }}</p>
            @endif
        </a>
        @endif

        {{-- Quick Stats --}}
        <div class="row" style="gap:10px;margin-bottom:16px">
            <div class="card" style="flex:1;text-align:center">
                <p class="mono" style="font-size:22px;font-weight:600;color:var(--accent-hex)">{{ $upcomingTrips }}</p>
                <p class="caption">Upcoming trips</p>
            </div>
            <div class="card" style="flex:1;text-align:center">
                <p class="mono" style="font-size:22px;font-weight:600;color:var(--success)">{{ $activeBookings }}</p>
                <p class="caption">Active bookings</p>
            </div>
        </div>

        {{-- Quick Actions --}}
        <h2 style="margin-bottom:10px">Start your journey</h2>
        <div class="stack">
            @if($activeItinerary && $activeItinerary->status === 'ongoing')
            <a href="{{ route('today') }}" class="btn btn-primary btn-block">View Today's Schedule</a>
            @endif
            <a href="{{ route('onboarding') }}" class="btn btn-primary btn-block">+ Create New Itinerary</a>
            <a href="{{ route('itineraries.index') }}" class="btn btn-secondary btn-block">My Itineraries</a>
            <a href="{{ route('chat') }}" class="btn btn-secondary btn-block">Ask AI Support</a>
        </div>
    </div>
</div>

@endsection
