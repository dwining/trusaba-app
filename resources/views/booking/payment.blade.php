@extends('layouts.app', ['showNav' => false])
@section('title', 'TruSaba · Pembayaran')
@section('content')

<div class="app-header" data-od-id="payment-header">
    <a class="icon-btn" href="javascript:history.back()" aria-label="Kembali">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">Checkout</p>
        <h1>Ringkasan bayar</h1>
    </div>
</div>

<div class="app-body no-nav has-sticky" data-od-id="payment-body">
    <div class="pad">
        <div class="card" style="display:flex;gap:12px;margin-bottom:16px" data-od-id="payment-hotel-card">
            <div class="ph-img thumb" style="width:72px;height:72px;border-radius:12px;background:linear-gradient(145deg,oklch(0.55 0.1 255 / 0.3),oklch(0.8 0.08 200 / 0.4))"></div>
            <div style="flex:1;min-width:0">
                <h3>{{ request('room_type', 'Superior Twin') }}</h3>
                <p class="small muted">
                    @if(isset($booking))
                        {{ $booking->merchant->name }}
                    @else
                        {{ request('booking_type') === 'hotel' ? 'Seminyak Palm Hotel' : 'Booking' }}
                    @endif
                    · {{ request('nights', 2) }} malam
                </p>
                <p class="caption" style="margin-top:4px">
                    @if(isset($booking) && $booking->check_in_date)
                        {{ $booking->check_in_date->format('d M') }} – {{ $booking->check_out_date?->format('d M Y') }}
                    @else
                        {{ request('check_in', '12 Agu') }} – {{ request('check_out', '14 Agu 2026') }}
                    @endif
                </p>
            </div>
        </div>

        @php
            $amount = (int) request('amount', isset($booking) ? $booking->amount : 2200000);
            $tax = (int) ($amount * 0.10);
            $fee = 25000;
            $discount = 100000;
            $total = $amount + $tax + $fee - $discount;
            $merchantId = (int) request('merchant_id', isset($booking) ? $booking->merchant_id : 1);
            $bookingType = request('booking_type', isset($booking) ? $booking->booking_type : 'hotel');
            $itemId = request('item_id');
            $checkIn = request('check_in', isset($booking) ? $booking->check_in_date?->toDateString() : now()->toDateString());
            $checkOut = request('check_out', isset($booking) ? $booking->check_out_date?->toDateString() : now()->addDays(2)->toDateString());
        @endphp

        <div class="card-soft stack-sm" data-od-id="payment-breakdown">
            <div class="row-between small"><span class="muted">Harga kamar ({{ request('nights', 2) }}×)</span><span class="mono">Rp {{ number_format($amount, 0, ',', '.') }}</span></div>
            <div class="row-between small"><span class="muted">Pajak & service</span><span class="mono">Rp {{ number_format($tax, 0, ',', '.') }}</span></div>
            <div class="row-between small"><span class="muted">Biaya platform</span><span class="mono">Rp {{ number_format($fee, 0, ',', '.') }}</span></div>
            <div class="row-between small"><span class="muted">Diskon AI first book</span><span class="mono" style="color:var(--success)">− Rp {{ number_format($discount, 0, ',', '.') }}</span></div>
            <div style="height:1px;background:var(--border);margin:6px 0"></div>
            <div class="row-between">
                <span style="font-weight:600">Total bayar</span>
                <span class="mono" style="font-size:18px;font-weight:600;color:var(--accent-hex)" data-od-id="payment-total">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
        </div>

        <h2 style="margin:20px 0 10px">Metode pembayaran</h2>
        <div class="stack" id="payMethods" data-od-id="pay-methods">
            <label class="room-opt selected" onclick="selectPay(this)">
                <input type="radio" name="pay" value="E-Wallet" checked />
                <div>
                    <h3>E-Wallet</h3>
                    <p class="small muted">GoPay · OVO · Dana · ShopeePay</p>
                </div>
            </label>
            <label class="room-opt" onclick="selectPay(this)">
                <input type="radio" name="pay" value="Transfer Bank" />
                <div>
                    <h3>Transfer bank</h3>
                    <p class="small muted">VA BCA · Mandiri · BNI</p>
                </div>
            </label>
            <label class="room-opt" onclick="selectPay(this)">
                <input type="radio" name="pay" value="Kartu Kredit" />
                <div>
                    <h3>Kartu kredit / debit</h3>
                    <p class="small muted">Visa · Mastercard · JCB</p>
                </div>
            </label>
        </div>
    </div>
</div>

<div class="sticky-cta" data-od-id="payment-cta">
    <form method="POST" action="{{ route('bookings.store') }}">
        @csrf
        <input type="hidden" name="merchant_id" value="{{ $merchantId }}">
        <input type="hidden" name="booking_type" value="{{ $bookingType }}">
        <input type="hidden" name="check_in_date" value="{{ $checkIn }}">
        <input type="hidden" name="check_out_date" value="{{ $checkOut }}">
        <input type="hidden" name="amount" value="{{ $total }}">
        <input type="hidden" name="payment_method" id="payMethodInput" value="E-Wallet">
        <input type="hidden" name="resource_detail[room_type]" value="{{ request('room_type', 'Superior Twin') }}">
        @if($itemId)
        <input type="hidden" name="itinerary_item_id" value="{{ $itemId }}">
        @endif
        <button type="submit" class="btn btn-primary btn-block" data-od-id="btn-bayar">Bayar Sekarang</button>
    </form>
</div>

@push('scripts')
<script>
    function selectPay(el) {
        el.parentElement.querySelectorAll('.room-opt').forEach(function(x) { x.classList.remove('selected'); });
        el.classList.add('selected');
        el.querySelector('input').checked = true;
        document.getElementById('payMethodInput').value = el.querySelector('input').value;
    }
</script>
@endpush
@endsection
