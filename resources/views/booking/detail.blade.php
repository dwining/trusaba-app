@extends('layouts.app', ['navActive' => 'home'])
@section('title', 'TruSaba · Booking Detail')
@section('content')

{{-- Type-specific labels --}}
@php
    $typeLabels = ['hotel' => 'Hotel', 'restaurant' => 'Dining', 'attraction' => 'Attraction', 'transport' => 'Transport', 'shopping' => 'Shopping'];
    $typeIcons = ['hotel' => 'act-hotel', 'restaurant' => 'act-food', 'attraction' => 'act-place', 'transport' => 'act-transport', 'shopping' => 'act-shop'];
    $typeLabel = $typeLabels[$type] ?? 'Detail';
    $typeIcon = $typeIcons[$type] ?? 'act-place';
    $isBookable = in_array($type, ['hotel', 'restaurant', 'attraction', 'transport']);
@endphp

@if(isset($availableMerchants))
    {{-- =============================================== --}}
    {{-- MODE: Pilih Penyedia (no merchant selected yet) --}}
    {{-- =============================================== --}}

    {{-- Hero --}}
    <div style="position:relative">
        <div class="ph-img hero-img" style="height:180px;background:linear-gradient(160deg,oklch(0.55 0.12 255 / 0.45),oklch(0.75 0.1 200 / 0.5)),oklch(0.7 0.06 220);align-items:flex-end;padding:16px;color:#fff;font-size:12px;letter-spacing:0.06em;text-transform:uppercase">
            {{ $typeLabel }} di {{ $itineraryItem?->itinerary?->destination ?? 'Bali' }}
        </div>
        <a class="icon-btn" href="{{ request('itinerary_id') ? route('itineraries.show', request('itinerary_id')) : url()->previous() }}" aria-label="Back" style="position:absolute;top:44px;left:16px;background:oklch(1 0 0 / 0.9);z-index:6">
            <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
    </div>

    <div class="app-body">
        <div class="pad" style="padding-top:16px">
            {{-- Heading --}}
            <div style="margin-bottom:16px;text-align:center">
                <h2>
                    Pilih {{ $typeLabel }} di {{ $itineraryItem?->itinerary?->destination ?? 'destinasi Anda' }}
                </h2>
            </div>

            {{-- Merchant list --}}
            <h2 style="margin:16px 0 10px">Penyedia tersedia</h2>
            <div class="stack" style="margin-bottom:20px">
                @forelse($availableMerchants as $m)
                    @php
                        $minPrice = match($type) {
                            'hotel' => $m->merchantRooms->min('price_per_night'),
                            'transport' => $m->merchantVehicles->min('price_per_day'),
                            default => null,
                        };
                    @endphp
                    <label class="room-opt" onclick="selectMerchant({{ $m->id }})">
                        <div style="flex:1">
                            <h3>{{ $m->name }}</h3>
                            <p class="small muted">{{ $m->address ?? $m->city }}, {{ $m->province ?? '' }}</p>
                            @if($minPrice)
                                <p class="mono small" style="font-weight:600;margin-top:4px;color:var(--accent-hex)">
                                    Mulai Rp {{ number_format($minPrice, 0, ',', '.') }}
                                    <span class="small muted">{{ $type === 'hotel' ? '/malam' : ($type === 'transport' ? '/hari' : '') }}</span>
                                </p>
                            @endif
                        </div>
                        <span class="btn btn-sm" style="background:var(--accent-hex);color:#fff;border:none;border-radius:8px;padding:6px 14px">Pilih</span>
                    </label>
                @empty
                    <div class="card" style="text-align:center;padding:24px">
                        <p class="muted">Tidak ada penyedia tersedia untuk kategori ini.</p>
                        <a href="{{ request('itinerary_id') ? route('itineraries.show', request('itinerary_id')) : url()->previous() }}" class="btn btn-secondary btn-sm" style="margin-top:12px">Back</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
    function selectMerchant(merchantId) {
        const base = '/bookings/create';
        const params = new URLSearchParams({
            merchant_id: merchantId,
            item_id: '{{ request("item_id") }}',
            type: '{{ $type }}',
            itinerary_id: '{{ request("itinerary_id") }}'
        });
        window.location.href = base + '?' + params.toString();
    }
    </script>

@else
{{-- Hero image --}}
<div style="position:relative" data-od-id="booking-hero">
    <div class="ph-img hero-img" style="height:220px;background:linear-gradient(160deg,oklch(0.55 0.12 255 / 0.45),oklch(0.75 0.1 200 / 0.5)),oklch(0.7 0.06 220);flex-direction:column;justify-content:flex-end;align-items:flex-start;padding:16px;color:#fff">
        @if(isset($itinerary))
        <span style="display:block;font-size:15px;font-weight:600;letter-spacing:0.03em;margin-bottom:4px">{{ $itinerary->title }} · {{ \Carbon\Carbon::parse($itinerary->start_date)->format('d M') }}–{{ \Carbon\Carbon::parse($itinerary->end_date)->format('d M Y') }}</span>
        @endif
        <span style="font-size:12px;letter-spacing:0.06em;text-transform:uppercase;opacity:0.85">{{ $merchant->province ?? $merchant->city ?? '' }} · {{ $typeLabel }}</span>
    </div>
    <a class="icon-btn" href="{{ request('itinerary_id') ? route('itineraries.show', request('itinerary_id')) : url()->previous() }}" aria-label="Back" style="position:absolute;top:48px;left:16px;background:oklch(1 0 0 / 0.9);z-index:6" data-od-id="btn-back-detail">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
</div>

<div class="app-body" style="padding-bottom:80px">
    <div class="pad" style="padding-top:16px">
        {{-- Merchant info --}}
        <div class="row-between" style="align-items:flex-start;margin-bottom:6px">
            <div>
                <span class="badge badge-gold">AI Recommend</span>
                <h1 style="margin-top:8px" data-od-id="merchant-name">{{ $merchant->name ?? 'Merchant' }}</h1>
                <p class="small muted" style="margin-top:4px">{{ $merchant->address ?? '' }}</p>
            </div>
        </div>

        {{-- Item info from itinerary --}}
        {{-- Only show item card when AI matched a real merchant (name already set to merchant name) --}}
        @if($itineraryItem && $itineraryItem->merchant_id)
        <div class="card" style="margin-top:16px;padding:12px">
            <div class="row-between" style="align-items:flex-start">
                <div style="flex:1">
                    <span class="badge" style="background:var(--accent-hex);color:#fff">{{ $typeLabel }}</span>
                    <h3 style="margin-top:8px">{{ $itineraryItem->name }}</h3>
                    @if($itineraryItem->description)
                    <p class="small muted" style="margin-top:4px">{{ $itineraryItem->description }}</p>
                    @endif
                </div>
                @if($itineraryItem->estimated_cost)
                <div style="text-align:right">
                    <span class="caption muted">Est. cost</span>
                    <p class="mono" style="font-weight:600;color:var(--accent-hex)">Rp {{ number_format($itineraryItem->estimated_cost, 0, ',', '.') }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ============================================ --}}
        {{-- HOTEL --}}
        {{-- ============================================ --}}
        @if($type === 'hotel')
        <p class="mono" style="font-size:20px;font-weight:600;color:var(--accent-hex);margin:12px 0 4px" data-od-id="hotel-price">Rp <span id="pricePerUnit">{{ number_format($merchant && $merchant->merchantRooms->isNotEmpty() ? $merchant->merchantRooms->first()->price_per_night : 1100000, 0, ',', '.') }}</span> <span class="small muted" style="font-weight:500;font-size:13px;color:var(--muted)">/ night</span></p>
        <p class="small muted">Breakfast included · free cancel 24 hrs</p>

        <h2 style="margin:20px 0 10px" data-od-id="room-title">Choose room type</h2>
        <div class="stack" id="roomOptions" data-od-id="room-options">
            @if($merchant && $merchant->merchantRooms->isNotEmpty())
                @foreach($merchant->merchantRooms as $room)
                <label class="room-opt {{ $loop->first ? 'selected' : '' }}" onclick="selectOption(this, {{ $room->price_per_night }}, '{{ $room->room_type }}')">
                    <input type="radio" name="room" value="{{ $room->id }}" {{ $loop->first ? 'checked' : '' }} />
                    <div style="flex:1">
                        <h3>{{ $room->room_type }}</h3>
                        @if($room->description)
                        <p class="small muted">{{ $room->description }}</p>
                        @endif
                        <p class="mono small" style="font-weight:600;margin-top:4px;color:var(--accent-hex)">Rp {{ number_format($room->price_per_night, 0, ',', '.') }}</p>
                    </div>
                </label>
                @endforeach
            @else
                <label class="room-opt selected" onclick="selectOption(this, 1100000, 'Superior Twin')" data-od-id="room-superior">
                    <input type="radio" name="room" value="superior" checked />
                    <div style="flex:1">
                        <h3>Superior Twin</h3>
                        <p class="small muted">2 twin beds · 24 m² · balcony</p>
                        <p class="mono small" style="font-weight:600;margin-top:4px;color:var(--accent-hex)">Rp 1,100,000</p>
                    </div>
                </label>
                <label class="room-opt" onclick="selectOption(this, 1450000, 'Deluxe King')" data-od-id="room-deluxe">
                    <input type="radio" name="room" value="deluxe" />
                    <div style="flex:1">
                        <h3>Deluxe King</h3>
                        <p class="small muted">1 king bed · 32 m² · pool view</p>
                        <p class="mono small" style="font-weight:600;margin-top:4px;color:var(--accent-hex)">Rp 1,450,000</p>
                    </div>
                </label>
            @endif
        </div>

        <h2 style="margin:20px 0 10px">Stay dates</h2>
        <div class="row" style="gap:10px;margin-bottom:20px" data-od-id="stay-dates">
            <div style="flex:1">
                <span class="caption">Check-in</span>
                <input class="input" type="date" id="ci" value="{{ $defaultCheckIn }}" onchange="updateTotal()" />
            </div>
            <div style="flex:1">
                <span class="caption">Check-out</span>
                <input class="input" type="date" id="co" value="{{ $defaultCheckOut }}" onchange="updateTotal()" />
            </div>
        </div>
        <p class="caption" id="unitLabel">2 nights</p>
        <p class="caption" style="margin-top:4px">Total: <span class="mono" style="font-weight:600;color:var(--accent-hex)" id="totalPrice">Rp 2,200,000</span></p>

        {{-- ============================================ --}}
        {{-- RESTAURANT --}}
        {{-- ============================================ --}}
        @elseif($type === 'restaurant')
        <h2 style="margin:20px 0 10px">Reservation details</h2>
        <div class="stack" style="gap:12px;margin-bottom:20px">
            <div class="field">
                <span class="field-label">Date</span>
                <input class="input" type="date" id="bookingDate" value="{{ $defaultCheckIn }}" onchange="updateTotal()" />
            </div>
            <div class="field">
                <span class="field-label">Time</span>
                <input class="input" type="time" id="bookingTime" value="{{ $itineraryItem?->schedule_time?->format('H:i') ?? '19:00' }}" />
            </div>
            <div class="field">
                <span class="field-label">Number of people</span>
                <input class="input" type="number" id="qty" min="1" value="{{ $itineraryItem?->itinerary?->total_participants ?? 2 }}" onchange="updateTotal()" />
            </div>
        </div>
        <p class="caption">Est. cost per person: <span class="mono" style="font-weight:600;color:var(--accent-hex)" id="pricePerUnit">Rp {{ number_format($itineraryItem->estimated_cost ?? 150000, 0, ',', '.') }}</span></p>
        <p class="caption" style="margin-top:4px">Total: <span class="mono" style="font-weight:600;color:var(--accent-hex)" id="totalPrice">Rp {{ number_format(($itineraryItem->estimated_cost ?? 150000) * ($itineraryItem?->itinerary?->total_participants ?? 2), 0, ',', '.') }}</span></p>

        {{-- ============================================ --}}
        {{-- ATTRACTION --}}
        {{-- ============================================ --}}
        @elseif($type === 'attraction')
        <h2 style="margin:20px 0 10px">Ticket details</h2>
        <div class="stack" style="gap:12px;margin-bottom:20px">
            <div class="field">
                <span class="field-label">Date</span>
                <input class="input" type="date" id="bookingDate" value="{{ $defaultCheckIn }}" onchange="updateTotal()" />
            </div>
            <div class="field">
                <span class="field-label">Number of tickets</span>
                <input class="input" type="number" id="qty" min="1" value="{{ $itineraryItem?->itinerary?->total_participants ?? 2 }}" onchange="updateTotal()" />
            </div>
        </div>
        <p class="caption">Est. cost per ticket: <span class="mono" style="font-weight:600;color:var(--accent-hex)">Rp {{ number_format($itineraryItem->estimated_cost ?? 100000, 0, ',', '.') }}</span></p>
        <p class="caption" style="margin-top:4px">Total: <span class="mono" style="font-weight:600;color:var(--accent-hex)" id="totalPrice">Rp {{ number_format(($itineraryItem->estimated_cost ?? 100000) * ($itineraryItem?->itinerary?->total_participants ?? 2), 0, ',', '.') }}</span></p>

        {{-- ============================================ --}}
        {{-- TRANSPORT --}}
        {{-- ============================================ --}}
        @elseif($type === 'transport')
        <h2 style="margin:20px 0 10px" data-od-id="vehicle-title">Choose vehicle</h2>
        <div class="stack" id="roomOptions" data-od-id="vehicle-options">
            @if($merchant && $merchant->merchantVehicles->isNotEmpty())
                @foreach($merchant->merchantVehicles as $vehicle)
                <label class="room-opt {{ $loop->first ? 'selected' : '' }}" onclick="selectOption(this, {{ $vehicle->price_per_day }}, '{{ $vehicle->vehicle_type }}')">
                    <input type="radio" name="vehicle" value="{{ $vehicle->id }}" {{ $loop->first ? 'checked' : '' }} />
                    <div style="flex:1">
                        <h3>{{ $vehicle->vehicle_type }}</h3>
                        @if($vehicle->vehicle_name)
                        <p class="small muted">{{ $vehicle->vehicle_name }}</p>
                        @endif
                        <p class="mono small" style="font-weight:600;margin-top:4px;color:var(--accent-hex)">Rp {{ number_format($vehicle->price_per_day, 0, ',', '.') }} <span class="small muted">/ day</span></p>
                    </div>
                </label>
                @endforeach
            @else
                <label class="room-opt selected" onclick="selectOption(this, 350000, 'Scooter')" data-od-id="vehicle-scooter">
                    <input type="radio" name="vehicle" value="scooter" checked />
                    <div style="flex:1">
                        <h3>Scooter</h3>
                        <p class="small muted">Honda Vario 125cc</p>
                        <p class="mono small" style="font-weight:600;margin-top:4px;color:var(--accent-hex)">Rp 350,000 <span class="small muted">/ day</span></p>
                    </div>
                </label>
                <label class="room-opt" onclick="selectOption(this, 650000, 'SUV')" data-od-id="vehicle-suv">
                    <input type="radio" name="vehicle" value="suv" />
                    <div style="flex:1">
                        <h3>SUV</h3>
                        <p class="small muted">Toyota Fortuner · 7 seats</p>
                        <p class="mono small" style="font-weight:600;margin-top:4px;color:var(--accent-hex)">Rp 650,000 <span class="small muted">/ day</span></p>
                    </div>
                </label>
            @endif
        </div>

        <h2 style="margin:20px 0 10px">Rental details</h2>
        <div class="row" style="gap:10px;margin-bottom:20px">
            <div style="flex:1">
                <span class="caption">Date</span>
                <input class="input" type="date" id="bookingDate" value="{{ $defaultCheckIn }}" onchange="updateTotal()" />
            </div>
            <div style="flex:1">
                <span class="caption">Days</span>
                <input class="input" type="number" id="qty" min="1" value="1" onchange="updateTotal()" />
            </div>
        </div>
        <p class="caption">Price per day: <span class="mono" style="font-weight:600;color:var(--accent-hex)">Rp <span id="pricePerUnit">350,000</span></span></p>
        <p class="caption" style="margin-top:4px">Total: <span class="mono" style="font-weight:600;color:var(--accent-hex)" id="totalPrice">Rp 350,000</span></p>

        {{-- ============================================ --}}
        {{-- SHOPPING / OTHER --}}
        {{-- ============================================ --}}
        @else
        <div class="card" style="margin-top:20px;padding:20px;text-align:center">
            <p class="muted" style="font-size:15px">This item is for reference only and cannot be booked via the app.</p>
            @if($itineraryItem && $itineraryItem->estimated_cost)
            <p class="mono" style="font-weight:600;color:var(--accent-hex);margin-top:8px">Estimated cost: Rp {{ number_format($itineraryItem->estimated_cost, 0, ',', '.') }}</p>
            @endif
            <a href="{{ route('itineraries.show', request('itinerary_id')) }}" class="btn btn-secondary btn-block" style="margin-top:16px">Back to Itinerary</a>
        </div>
        @endif

        {{-- Error display --}}
        @if($errors->any())
        <div class="card" style="background:#fff3f3;border:1px solid var(--danger);margin-top:12px;padding:8px 12px">
            @foreach($errors->all() as $e)<p class="small" style="color:var(--danger);margin:2px 0">{{ $e }}</p>@endforeach
        </div>
        @endif

        {{-- Booking form (only for bookable types) --}}
        @if($isBookable)
        <form method="POST" action="{{ route('cart.add') }}" id="cartForm" style="margin-top:16px;padding-bottom:80px">
            @csrf

            {{-- Common hidden fields --}}
            <input type="hidden" name="merchant_id" value="{{ $merchant->id ?? '' }}">
            <input type="hidden" name="itinerary_id" value="{{ request('itinerary_id', '') }}">
            <input type="hidden" name="itinerary_item_id" value="{{ request('item_id', '') }}">
            <input type="hidden" name="booking_type" value="{{ $type }}">
            <input type="hidden" name="amount" id="amountHidden" value="0">

            {{-- Hotel-specific hidden fields --}}
            @if($type === 'hotel')
            <input type="hidden" name="check_in_date" id="ciHidden" value="{{ $defaultCheckIn }}">
            <input type="hidden" name="check_out_date" id="coHidden" value="{{ $defaultCheckOut }}">
            <input type="hidden" name="room_type" id="optionNameHidden" value="{{ $merchant && $merchant->merchantRooms->isNotEmpty() ? $merchant->merchantRooms->first()->room_type : 'Superior Twin' }}">
            @endif

            {{-- Transport-specific hidden fields --}}
            @if($type === 'transport')
            <input type="hidden" name="booking_date" id="bookingDateHidden" value="{{ $defaultCheckIn }}">
            <input type="hidden" name="vehicle_type" id="optionNameHidden" value="Scooter">
            <input type="hidden" name="duration" id="durationHidden" value="1">
            @endif

            {{-- Restaurant/attraction hidden fields --}}
            @if(in_array($type, ['restaurant', 'attraction']))
            <input type="hidden" name="booking_date" id="bookingDateHidden" value="{{ $defaultCheckIn }}">
            <input type="hidden" name="quantity" id="qtyHidden" value="{{ $itineraryItem?->itinerary?->total_participants ?? 2 }}">
            @endif

            <button type="submit" class="btn btn-primary btn-block" id="btnAddCart">Add to Cart</button>
        </form>
        @endif
    </div>
</div>

<script>
var selectedPrice = @if($type === 'hotel')
    {{ $merchant && $merchant->merchantRooms->isNotEmpty() ? $merchant->merchantRooms->first()->price_per_night : 1100000 }};
@elseif($type === 'transport')
    {{ $merchant && $merchant->merchantVehicles->isNotEmpty() ? $merchant->merchantVehicles->first()->price_per_day : 350000 }};
@elseif($type === 'restaurant')
    {{ $itineraryItem->estimated_cost ?? 150000 }};
@elseif($type === 'attraction')
    {{ $itineraryItem->estimated_cost ?? 100000 }};
@else
    0;
@endif

var selectedName = @if($type === 'hotel')
    '{{ $merchant && $merchant->merchantRooms->isNotEmpty() ? $merchant->merchantRooms->first()->room_type : 'Superior Twin' }}';
@elseif($type === 'transport')
    '{{ $merchant && $merchant->merchantVehicles->isNotEmpty() ? $merchant->merchantVehicles->first()->vehicle_type : 'Scooter' }}';
@else
    '';
@endif

function selectOption(el, price, name) {
    var options = document.querySelectorAll('.room-opt');
    options.forEach(function(x) { x.classList.remove('selected'); });
    el.classList.add('selected');
    el.querySelector('input').checked = true;
    selectedPrice = price;
    selectedName = name || el.querySelector('h3').textContent;
    updateTotal();
}

function updateTotal() {
    var total = 0;
    var multiplier = 1;
    var unitLabel = '';

    @if($type === 'hotel')
        var ci = new Date(document.getElementById('ci').value);
        var co = new Date(document.getElementById('co').value);
        multiplier = Math.max(1, Math.round((co - ci) / 86400000));
        unitLabel = multiplier + ' nights';
        document.getElementById('unitLabel').textContent = unitLabel;
        total = selectedPrice * multiplier;
        document.getElementById('ciHidden').value = document.getElementById('ci').value;
        document.getElementById('coHidden').value = document.getElementById('co').value;
        document.getElementById('optionNameHidden').value = selectedName;
        document.getElementById('pricePerUnit').textContent = selectedPrice.toLocaleString('id-ID');
    @elseif($type === 'transport')
        multiplier = parseInt(document.getElementById('qty').value) || 1;
        total = selectedPrice * multiplier;
        document.getElementById('bookingDateHidden').value = document.getElementById('bookingDate').value;
        document.getElementById('optionNameHidden').value = selectedName;
        document.getElementById('durationHidden').value = multiplier;
    @elseif($type === 'restaurant' || $type === 'attraction')
        multiplier = parseInt(document.getElementById('qty').value) || 1;
        total = selectedPrice * multiplier;
        document.getElementById('bookingDateHidden').value = document.getElementById('bookingDate').value;
        document.getElementById('qtyHidden').value = multiplier;
    @endif

    document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('amountHidden').value = total;
}

// Initial price per unit display updates
updateTotal();
</script>

<style>
.vehicle-opt {
    display: flex;
    gap: 12px;
    padding: 12px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
}
.vehicle-opt.selected {
    border-color: var(--accent-hex);
    background: oklch(0.55 0.18 255 / 0.06);
}
.vehicle-opt input { accent-color: var(--accent-hex); margin-top: 4px; }
</style>
@endif
@endsection
