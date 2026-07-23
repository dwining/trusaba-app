@extends('layouts.app', ['navActive' => 'cart'])

@section('title', 'TruSaba · Cart')

@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ route('itineraries.index') }}" aria-label="Back">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">Checkout</p>
        <h1>Your Cart</h1>
    </div>
</div>

<div class="app-body" style="padding-bottom:calc(var(--nav-h) + 90px)">
    @if($cartItems->isEmpty())
        <div class="card" style="text-align:center;padding:40px 20px">
            <p class="muted">Your cart is empty.</p>
            <a class="btn btn-primary btn-sm" href="{{ route('itineraries.index') }}" style="margin-top:12px">
                Browse Itineraries
            </a>
        </div>
    @else
        @foreach($cartItems as $itinId => $items)
        @php $itin = $items->first()->itinerary; @endphp
        <div style="margin-bottom:16px">
            <p class="caption" style="margin-bottom:8px">{{ $itin?->destination ?? 'Trip' }} — {{ $itin?->title ?? 'Itinerary' }}</p>
            @foreach($items as $item)
            <div class="card" style="margin-bottom:8px;position:relative">
                <div class="row-between" style="align-items:flex-start">
                    <div style="flex:1;min-width:0">
                        <h3>{{ $item->merchant?->name ?? 'Item' }}</h3>
                        <p class="small muted">
                            {{ ucfirst($item->booking_type) }}
                            @if($item->check_in_date)
                                · {{ $item->check_in_date->format('d M') }}
                            @endif
                            @if($item->check_out_date && $item->check_out_date->ne($item->check_in_date))
                                – {{ $item->check_out_date->format('d M') }}
                            @endif
                            @if(!empty($item->resource_detail['room_type'] ?? null))
                                · {{ $item->resource_detail['room_type'] }}
                            @endif
                            @if(!empty($item->resource_detail['nights'] ?? null))
                                · {{ $item->resource_detail['nights'] }} nights
                            @endif
                        </p>
                    </div>
                    <form method="POST" action="{{ route('cart.remove', $item->id) }}" style="flex-shrink:0">
                        @csrf @method('DELETE')
                        <button type="submit" class="icon-btn" aria-label="Remove" style="color:var(--danger)">
                            <svg viewBox="0 0 24 24" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </form>
                </div>
                <p class="mono small" style="font-weight:600;margin-top:6px;color:var(--accent-hex)">
                    Rp {{ number_format($item->amount, 0, ',', '.') }}
                </p>
            </div>
            @endforeach
        </div>
        @endforeach

        {{-- Subtotal & Checkout --}}
        <div class="card" style="margin-bottom:16px">
            <div class="row-between" style="margin-bottom:8px">
                <span>Subtotal</span>
                <span class="mono" style="font-weight:600">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <div class="field">
                <label class="field-label" for="travelers">Travelers</label>
                <select class="select" id="travelers" name="travelers" form="checkoutForm">
                    @for($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}" {{ $i == 2 ? 'selected' : '' }}>{{ $i }} {{ Str::plural('person', $i) }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <form id="checkoutForm" method="POST" action="{{ route('cart.checkout') }}">
            @csrf
            <button type="submit" class="btn btn-primary btn-block" style="font-size:16px">
                Checkout — Rp {{ number_format($total, 0, ',', '.') }}
            </button>
        </form>
    @endif
</div>

@endsection
