@extends('layouts.app')
@section('title', 'TruSaba · Splash')
@section('content')
<div style="background:var(--accent-hex);flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;position:relative">
    <div style="text-align:center;padding:40px">
        <img src="{{ asset('logo.jpeg') }}" alt="TruSaba" style="width:88px;height:88px;object-fit:contain;margin-bottom:20px" />
        <h1 style="font-size:32px;font-weight:600;color:#fff">TruSaba</h1>
        <p style="color:rgba(255,255,255,0.7);margin-top:8px">AI travel companion kamu</p>
    </div>
</div>
<script>setTimeout(function(){window.location.href='{{ route('auth') }}'},2600)</script>
@endsection
