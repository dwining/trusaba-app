@extends('layouts.app', ['showNav' => false])
@section('title', 'TruSaba · Detail Hotel')
@section('content')

{{-- Hero image --}}
<div style="position:relative" data-od-id="hotel-hero">
    <div class="ph-img hero-img" style="height:220px;background:linear-gradient(160deg,oklch(0.55 0.12 255 / 0.45),oklch(0.75 0.1 200 / 0.5)),oklch(0.7 0.06 220);align-items:flex-end;padding:16px;color:#fff;font-size:12px;letter-spacing:0.06em;text-transform:uppercase">{{ $merchant->city ?? 'Bali' }} · {{ $merchant->type === 'hotel' ? 'Pool view' : 'Best spot' }}</div>
    <a class="icon-btn" href="{{ url()->previous() }}" aria-label="Kembali" style="position:absolute;top:48px;left:16px;background:oklch(1 0 0 / 0.9);z-index:6" data-od-id="btn-back-hotel">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
</div>

<div class="app-body has-sticky no-nav" style="padding-bottom:100px" data-od-id="hotel-body">
    <div class="pad" style="padding-top:16px">
        <div class="row-between" style="align-items:flex-start;margin-bottom:6px">
            <div>
                <span class="badge badge-gold">AI Recommend</span>
                <h1 style="margin-top:8px" data-od-id="hotel-name">{{ $merchant->name ?? 'Seminyak Palm Hotel' }}</h1>
                <p class="small muted" style="margin-top:4px">{{ $merchant->address ?? 'Jl. Kayu Aya No. 18' }} · 4.8 ★ (1.2rb ulasan)</p>
            </div>
        </div>
        <p class="mono" style="font-size:20px;font-weight:600;color:var(--accent-hex);margin:12px 0 4px" data-od-id="hotel-price">Rp 1.100.000 <span class="small muted" style="font-weight:500;font-size:13px;color:var(--muted)">/ malam</span></p>
        <p class="small muted">Termasuk sarapan · free cancel 24 jam</p>

        <h2 style="margin:20px 0 10px" data-od-id="room-title">Pilih jenis kamar</h2>
        <div class="stack" id="roomOptions" data-od-id="room-options">
            <label class="room-opt selected" onclick="selectRoom(this, 1100000)" data-od-id="room-superior">
                <input type="radio" name="room" value="superior" checked />
                <div style="flex:1">
                    <h3>Superior Twin</h3>
                    <p class="small muted">2 twin bed · 24 m² · balcony</p>
                    <p class="mono small" style="font-weight:600;margin-top:4px;color:var(--accent-hex)">Rp 1.100.000</p>
                </div>
            </label>
            <label class="room-opt" onclick="selectRoom(this, 1450000)" data-od-id="room-deluxe">
                <input type="radio" name="room" value="deluxe" />
                <div style="flex:1">
                    <h3>Deluxe King</h3>
                    <p class="small muted">1 king bed · 32 m² · pool view</p>
                    <p class="mono small" style="font-weight:600;margin-top:4px;color:var(--accent-hex)">Rp 1.450.000</p>
                </div>
            </label>
            <label class="room-opt" onclick="selectRoom(this, 1900000)" data-od-id="room-suite">
                <input type="radio" name="room" value="suite" />
                <div style="flex:1">
                    <h3>Garden Suite</h3>
                    <p class="small muted">1 king · 45 m² · private garden</p>
                    <p class="mono small" style="font-weight:600;margin-top:4px;color:var(--accent-hex)">Rp 1.900.000</p>
                </div>
            </label>
        </div>

        <h2 style="margin:20px 0 10px">Tanggal menginap</h2>
        <div class="row" style="gap:10px;margin-bottom:20px" data-od-id="stay-dates">
            <div style="flex:1">
                <span class="caption">Check-in</span>
                <input class="input" type="date" id="ci" value="2026-08-12" onchange="updateNights()" />
            </div>
            <div style="flex:1">
                <span class="caption">Check-out</span>
                <input class="input" type="date" id="co" value="2026-08-14" onchange="updateNights()" />
            </div>
        </div>
        <p class="caption" id="nights">2 malam</p>
        <p class="caption" style="margin-top:4px">Total: <span class="mono" style="font-weight:600;color:var(--accent-hex)" id="totalPrice">Rp 2.200.000</span></p>
    </div>
</div>

<div class="sticky-cta" data-od-id="hotel-cta">
    <button type="button" class="btn btn-primary btn-block" id="btnLanjut" onclick="goToPayment()" data-od-id="btn-lanjut-bayar">Lanjut ke Pembayaran</button>
</div>

@push('scripts')
<script>
    var selectedPrice = 1100000;
    var selectedRoom = 'Superior Twin';

    function selectRoom(el, price) {
        document.querySelectorAll('.room-opt').forEach(function(x) { x.classList.remove('selected'); });
        el.classList.add('selected');
        el.querySelector('input').checked = true;
        selectedPrice = price;
        selectedRoom = el.querySelector('h3').textContent;
        updateNights();
    }

    function updateNights() {
        var ci = new Date(document.getElementById('ci').value);
        var co = new Date(document.getElementById('co').value);
        var nights = Math.max(1, Math.round((co - ci) / 86400000));
        document.getElementById('nights').textContent = nights + ' malam';
        var total = selectedPrice * nights;
        document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    function goToPayment() {
        var ci = document.getElementById('ci').value;
        var co = document.getElementById('co').value;
        var nights = Math.max(1, Math.round((new Date(co) - new Date(ci)) / 86400000));
        var amount = selectedPrice * nights;

        var params = new URLSearchParams({
            merchant_id: '{{ $merchant->id ?? 1 }}',
            booking_type: 'hotel',
            check_in: ci,
            check_out: co,
            room_type: selectedRoom,
            amount: amount,
            nights: nights
        });
        window.location.href = '{{ route('bookings.create.payment') }}?' + params.toString();
    }
    updateNights();
</script>
@endpush
@endsection
