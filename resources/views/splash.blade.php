@extends('layouts.app', ['navActive' => 'home', 'showNav' => false])

@section('title', 'TruSaba')

@section('content')
<div class="splash-bg" aria-hidden="true"></div>
<div class="splash-body">
    <div class="splash-aura">
        <div class="splash-ring"></div>
        <div class="splash-ring-inner"></div>
        <img class="splash-logo" src="{{ asset('logo.jpeg') }}" alt="TruSaba" />
    </div>
    <h1 class="splash-title">TruSaba</h1>
    <p class="splash-tagline">Your AI travel companion — personal trips, from planning to return</p>
    <div class="splash-accent-line" aria-hidden="true"></div>
</div>
<p class="splash-version">v1.0</p>
@push('scripts')
<style>
    .status-bar { color: oklch(1 0 0 / 0.55); }
</style>
<script>
    setTimeout(function () {
        window.location.href = '{{ route('auth') }}';
    }, 2600);
</script>
@endpush
@endsection
