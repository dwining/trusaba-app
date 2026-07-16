@extends('layouts.app', ['showNav' => false])
@section('title', 'TruSaba · Login')
@section('content')
<div class="app-body no-nav" style="display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:40px 20px">
    <img src="{{ asset('logo.jpeg') }}" alt="TruSaba" style="width:88px;height:88px;object-fit:contain;margin-bottom:20px" />
    <h1>Selamat datang</h1>
    <p class="muted">Auth page — akan diisi di Fase 1</p>
</div>
@endsection
