@extends('layouts.app', ['navActive' => 'home'])
@section('title', 'TruSaba · Itinerary ' . $itinerary->destination)
@section('content')

@if($itinerary->status === 'processing')
<script>window.location.href = '{{ route('itineraries.loading', $itinerary->id) }}';</script>
@endif

<style>
.edit-actions { display: flex; }
.add-activity-row { display: block; }
.item-modal-overlay {
    transition: opacity 0.2s;
}
</style>

@if($itinerary->status === 'failed')
<div class="app-body" style="display:flex;align-items:center;justify-content:center">
    <div style="text-align:center;padding:40px 20px">
        <div class="modal-icon" style="margin:0 auto 12px">
            <svg viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.3 4.3L2.8 18a2 2 0 001.7 3h15a2 2 0 001.7-3L13.7 4.3a2 2 0 00-3.4 0z"/></svg>
        </div>
        <h2>Failed to create itinerary</h2>
        <p class="muted">AI couldn't create an itinerary this time. Please try again.</p>
        <a href="{{ route('onboarding') }}" class="btn btn-primary" style="margin-top:12px">Try Again</a>
    </div>
</div>
@else

<div class="app-header">
    <a class="icon-btn" href="{{ route('itineraries.index') }}" aria-label="Back">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">AI Itinerary</p>
        <h1>{{ $itinerary->destination }} · {{ $itinerary->duration_days }} Days</h1>
    </div>
    <span class="badge badge-gold">AI Pick</span>
</div>

<div class="app-body">
    <div class="pad">

        @php
            $typeDisplay = [
                'hotel' => 'Hotel',
                'restaurant' => 'Restaurant',
                'attraction' => 'Attraction',
                'transport' => 'Transport',
                'shopping' => 'Shopping',
                'other' => 'Activity',
            ];
        @endphp

        {{-- Budget Summary --}}
        <div class="card" style="margin-bottom:8px;margin-top:4px">
            <div class="row-between" style="margin-bottom:12px">
                <div>
                    <p class="caption">Total estimate</p>
                    <p class="mono" style="font-size:22px;font-weight:600;letter-spacing:-0.02em;color:var(--accent-hex)">
                        Rp {{ number_format($itinerary->estimated_budget, 0, ',', '.') }}
                    </p>
                </div>
                <div style="text-align:right">
                    <p class="caption">Your budget</p>
                    <p class="mono small" style="font-weight:600">
                        Rp {{ number_format($itinerary->budget_input, 0, ',', '.') }}
                    </p>
                    @php
                        $overBudget = $itinerary->estimated_budget > $itinerary->budget_input;
                    @endphp
                    <span class="badge {{ $overBudget ? 'badge-warn' : 'badge-success' }}" style="margin-top:4px">
                        {{ $overBudget ? 'Over budget' : 'Within budget' }}
                    </span>
                </div>
            </div>

            {{-- Budget breakdown bar chart --}}
            @php
                $types = ['hotel' => ['Accommodation', 'gold'], 'attraction' => ['Attractions', 'gold'],
                           'restaurant' => ['Food', 'green'], 'transport' => ['Transport', 'muted'],
                           'shopping' => ['Souvenirs', 'warn']];
                $maxBudget = max($itinerary->estimated_budget, 1);
                $totals = [];
                foreach ($itinerary->itineraryItems as $item) {
                    $cat = $item->type;
                    if (!isset($totals[$cat])) $totals[$cat] = 0;
                    $totals[$cat] += $item->estimated_cost;
                }
            @endphp
            <div class="bar-chart">
                @foreach($types as $type => [$label, $color])
                @php $val = $totals[$type] ?? 0; $pct = min(100, round(($val / $maxBudget) * 100)); @endphp
                <div class="bar-row">
                    <span class="label">{{ $label }}</span>
                    <div class="bar-track">
                        <div class="bar-fill {{ $color }}" style="width:{{ $pct }}%"></div>
                    </div>
                    <span class="val">Rp {{ number_format($val / 1000, 0, ',', '.') }}k</span>
                </div>
                @endforeach
            </div>
        </div>

        @php
            $cartItems = App\Models\CartItem::where('user_id', Auth::id())
                ->where('itinerary_id', $itinerary->id)
                ->get();
            $cartCount = $cartItems->count();
            $cartTotal = $cartItems->sum('amount');
            // Lookup: itinerary_item_id → cart_item_id
            $cartLookup = $cartItems->pluck('id', 'itinerary_item_id')->toArray();
        @endphp
        @if($cartCount > 0)
        <div class="card" style="background:var(--accent);color:#fff;margin-bottom:12px">
            <div class="row-between">
                <div>
                    <p style="font-weight:600">{{ $cartCount }} {{ Str::plural('item', $cartCount) }} in cart</p>
                    <p class="small" style="opacity:0.85">Rp {{ number_format($cartTotal, 0, ',', '.') }}</p>
                </div>
                <a class="btn btn-sm" href="{{ route('cart.index') }}" style="background:#fff;color:var(--accent);border:none">
                    View Cart →
                </a>
            </div>
        </div>
        @endif

        {{-- Budget Overview --}}
        @if($cartCount > 0 || $bookedTotal > 0 || $infoOnlyTotal > 0)
        <div class="card" style="margin-bottom:12px">
            <h3 style="font-size:14px;margin-bottom:8px">Budget Overview</h3>
            <div class="stack" style="gap:6px;font-size:13px">
                <div class="row-between">
                    <span class="muted">AI Estimate</span>
                    <span class="mono" style="font-weight:600">Rp {{ number_format($itinerary->estimated_budget, 0, ',', '.') }}</span>
                </div>
                @if($bookedTotal > 0)
                <div class="row-between">
                    <span class="muted">Paid</span>
                    <span class="mono" style="font-weight:600;color:var(--success)">Rp {{ number_format($bookedTotal, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($cartCount > 0)
                <div class="row-between">
                    <span class="muted">In Cart ({{ $cartCount }} {{ Str::plural('item', $cartCount) }})</span>
                    <span class="mono" style="font-weight:600">Rp {{ number_format($cartTotal, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($infoOnlyTotal > 0)
                <div class="row-between">
                    <span class="muted">Other Activities</span>
                    <span class="mono" style="font-weight:600">Rp {{ number_format($infoOnlyTotal, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="row-between" style="border-top:1px solid var(--border);padding-top:6px;margin-top:2px">
                    <span style="font-weight:600">Estimated Total</span>
                    <span class="mono" style="font-weight:600;color:var(--accent-hex)">Rp {{ number_format($bookedTotal + $cartTotal + $infoOnlyTotal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Hotel coverage section --}}
        @if($itinerary->itineraryItems->where('type', 'hotel')->count() > 0)
        <div class="card" style="margin-bottom:12px">
            <h3 style="font-size:14px;margin-bottom:8px">🏨 Accommodation</h3>

            {{-- Coverage bar --}}
            @php $coveredCount = count(array_filter($coveredNights)); @endphp
            <div style="margin:8px 0">
                <div style="display:flex;gap:3px;margin-bottom:4px">
                    @foreach($coveredNights as $date => $covered)
                    <div style="flex:1;height:20px;border-radius:3px;background:{{ $covered ? 'var(--accent-hex)' : 'var(--border)' }}"
                         title="{{ \Carbon\Carbon::parse($date)->format('d M') }}: {{ $covered ? 'Booked' : 'Available' }}"></div>
                    @endforeach
                </div>
                <div class="row-between caption">
                    <span>{{ $coveredCount }}/{{ $totalNights }} nights booked</span>
                    <span class="mono" style="color:var(--accent-hex)">{{ $totalNights }} nights</span>
                </div>
            </div>

            {{-- Gap booking buttons --}}
            @foreach($gaps as $gap)
            @php
                $gapStart = \Carbon\Carbon::parse($gap['start']);
                $gapEnd = \Carbon\Carbon::parse($gap['end']);
                $gapNights = (int) $gapStart->diffInDays($gapEnd);
            @endphp
            <a class="btn btn-secondary btn-sm btn-block" style="margin-top:8px"
               href="{{ route('bookings.create', ['type' => 'hotel', 'itinerary_id' => $itinerary->id, 'check_in' => $gap['start'], 'check_out' => $gap['end']]) }}">
                 Add Hotel · {{ $gapStart->format('d M') }} – {{ $gapEnd->subDay()->format('d M') }} ({{ $gapNights }} nights) →
            </a>
            @endforeach

            @if(count($gaps) === 0 && $totalNights > 0)
            <p class="small" style="text-align:center;color:var(--accent-hex);padding-top:4px">✓ All nights covered!</p>
            @endif
        </div>
        @endif

        {{-- Day sections --}}
        @foreach($days as $dayNumber => $items)
        @php
            $firstItem = $items->first();
            $date = \Carbon\Carbon::parse($itinerary->start_date)->addDays($dayNumber - 1);
            $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $dayLabel = $dayNames[$date->dayOfWeek] . ', ' . $date->format('d M');
        @endphp
        <div class="day-head">
            <h2>Day {{ $dayNumber }} · {{ $dayLabel }}</h2>
            <span class="badge badge-blue">{{ $itinerary->destination }}</span>
        </div>

        <div class="timeline">
            @foreach($items as $item)
            @php
                $typeIcons = [
                    'hotel' => 'act-hotel',
                    'restaurant' => 'act-food',
                    'attraction' => 'act-place',
                    'transport' => 'act-transport',
                    'shopping' => 'act-shop',
                    'other' => 'act-place',
                ];
                $typeSVG = [
                    'hotel' => '<path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-6h6v6"/><path d="M9 10h.01M15 10h.01"/>',
                    'restaurant' => '<path d="M4 11h16v2a6 6 0 01-6 6H10a6 6 0 01-6-6v-2z"/><path d="M8 11V5M12 11V3M16 11V6"/>',
                    'attraction' => '<path d="M12 21s-7-5.5-7-11a7 7 0 0114 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
                    'transport' => '<rect x="3" y="8" width="18" height="10" rx="2"/><path d="M6 18v2M18 18v2M3 12h18"/>',
                    'shopping' => '<path d="M6 7h12l1 13H5L6 7z"/><path d="M9 7a3 3 0 016 0"/>',
                    'other' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
                ];
                $actClass = $typeIcons[$item->type] ?? 'act-place';
                $svgContent = $typeSVG[$item->type] ?? $typeSVG['other'];

                $itemDate = \Carbon\Carbon::parse($itinerary->start_date)->addDays($item->day_number - 1)->format('Y-m-d');
                $timeOnly = substr((string) $item->schedule_time, -8); // extract HH:MM:SS from Carbon string
                $itemDateTime = $itemDate . ' ' . $timeOnly;
                $isFuture = \Carbon\Carbon::parse($itemDateTime)->isFuture();
            @endphp
            <div class="tl-item">
                <div class="tl-dot {{ $item->type === 'restaurant' ? 'gold' : '' }}"></div>
                <p class="tl-time">{{ \Carbon\Carbon::parse($item->schedule_time)->format('H:i') }}</p>
                <div class="card" style="position:relative">
                    @if($isFuture)
                    <div class="edit-actions" style="position:absolute;top:8px;right:8px;gap:6px;z-index:5">
                        <button type="button" class="icon-btn btn-edit-item" style="width:28px;height:28px;border-radius:6px;background:var(--surface);border:1px solid var(--border)"
                            onclick="openEditModal({{ json_encode([
                                'id' => $item->id,
                                'type' => $item->type,
                                'name' => $item->name,
                                'schedule_time' => \Carbon\Carbon::parse($item->schedule_time)->format('H:i'),
                                'description' => $item->description,
                                'location' => $item->location,
                                'latitude' => $item->latitude,
                                'longitude' => $item->longitude,
                                'estimated_cost' => $item->estimated_cost,
                                'day_number' => $item->day_number,
                            ]) }})">
                            <svg viewBox="0 0 24 24" width="14" height="14"><path d="M17 3a2.83 2.83 0 114 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('itinerary-items.destroy', [$itinerary->id, $item->id]) }}" onsubmit="return confirm('Delete this activity?')" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="icon-btn" style="width:28px;height:28px;border-radius:6px;background:var(--surface);border:1px solid var(--danger);color:var(--danger)">
                                <svg viewBox="0 0 24 24" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </form>
                    </div>
                    @endif
                    <div class="row" style="align-items:flex-start">
                        <div class="act-icon {{ $actClass }}">
                            <svg viewBox="0 0 24 24">{!! $svgContent !!}</svg>
                        </div>
                        <div style="flex:1;min-width:0">
                            <h3>{{ $item->name ?: ($typeDisplay[$item->type] ?? 'Activity') }}</h3>
                            @if($item->description)
                            <p class="small muted">{{ $item->description }}</p>
                            @elseif($item->is_bookable && !$item->merchant_id)
                            <p class="small muted">Choose {{ $typeDisplay[$item->type] ?? 'activity' }} in {{ $itinerary->destination }}</p>
                            @endif
                            @if($item->location)
                                @if($item->latitude && $item->longitude)
                                    <a href="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}" target="_blank"
                                       class="small" style="display:block;color:var(--accent-hex);margin-top:2px;text-decoration:none">
                                        📍 {{ $item->location }}
                                    </a>
                                @else
                                    <p class="small muted" style="margin-top:2px">📍 {{ $item->location }}</p>
                                @endif
                            @endif
                            @if(($item->merchant_id || !$item->is_bookable) && $item->type !== 'hotel')
                            <p class="mono small" style="font-weight:600;margin-top:4px">
                                Rp {{ number_format($item->estimated_cost, 0, ',', '.') }}
                            </p>
                            @endif
                        </div>
                    </div>
                    @if($item->is_bookable && $item->merchant_id && $item->type !== 'hotel')
                        {{-- MODE 1: Strict merchant match — direct booking (non-hotel only) --}}
                        @php $cartId = $cartLookup[$item->id] ?? null; @endphp
                        @if($cartId)
                        <div class="row" style="gap:8px;margin-top:12px">
                            <span class="badge badge-success" style="flex:1;text-align:center;padding:8px 0">✓ In Cart</span>
                            <form method="POST" action="{{ route('cart.remove', $cartId) }}" style="flex-shrink:0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="color:var(--danger);background:transparent;border:1px solid var(--border);border-radius:8px;padding:6px 12px" title="Remove from cart">✕</button>
                            </form>
                        </div>
                        @else
                        <a class="btn btn-primary btn-sm btn-block" style="margin-top:12px"
                           href="{{ route('bookings.create', ['merchant_id' => $item->merchant_id, 'itinerary_id' => $itinerary->id, 'item_id' => $item->id, 'type' => $item->type]) }}">
                            Add to Cart
                        </a>
                        @endif

                    @elseif($item->is_bookable && $item->type !== 'hotel')
                        {{-- MODE 2: No strict match — choose merchant (non-hotel only) --}}
                        @php $lowestPrice = $lowestPrices[$item->type] ?? 0; @endphp
                        @if($lowestPrice > 0)
                        <p class="mono small" style="font-weight:600;margin-bottom:4px">Mulai Rp {{ number_format($lowestPrice, 0, ',', '.') }}</p>
                        @endif
                        <a class="btn btn-secondary btn-sm btn-block" style="margin-top:8px"
                           href="{{ route('bookings.create', ['item_id' => $item->id, 'type' => $item->type, 'itinerary_id' => $itinerary->id]) }}">
                             Choose {{ $typeDisplay[$item->type] ?? 'Activity' }} →
                        </a>

                    @else
                        {{-- MODE 3: Not bookable --}}
                        <span class="badge badge-muted">Info only</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Add Activity button (edit mode only) --}}
        <div class="add-activity-row" style="text-align:center;margin-top:8px">
            <button type="button" class="btn btn-sm" onclick="openAddModal({{ $dayNumber }})" style="border:1px dashed var(--border);border-radius:8px;padding:10px;width:100%;color:var(--muted)">
                + Add Activity — Day {{ $dayNumber }}
            </button>
        </div>

        @endforeach

        {{-- Build itinerary text for copy --}}
        @php
            $lines = [];
            $lines[] = $itinerary->title;
            $lines[] = $itinerary->start_date->format('d M') . ' – ' . $itinerary->end_date->format('d M Y') . ' · ' . $itinerary->duration_days . ' days';
            foreach ($days as $dayNumber => $items) {
                $date = \Carbon\Carbon::parse($itinerary->start_date)->addDays($dayNumber - 1);
                $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                $label = $dayNames[$date->dayOfWeek] . ', ' . $date->format('d M');
                $lines[] = '';
                $lines[] = '── Day ' . $dayNumber . ' · ' . $label . ' ──';
                foreach ($items as $item) {
                    $time = \Carbon\Carbon::parse($item->schedule_time)->format('H:i');
                    $typeLabel = $typeDisplay[$item->type] ?? 'Activity';
                    $line = $time . '  ' . $item->name . ' (' . $typeLabel . ')';
                    if ($item->description) $line .= ' — ' . $item->description;
                    $lines[] = $line;
                    if ($item->location) $lines[] = '📍 ' . $item->location;
                }
            }
            $itineraryText = implode("\n", $lines);
        @endphp
        <textarea id="itineraryText" style="display:none">{{ trim($itineraryText) }}</textarea>

        {{-- Action buttons --}}
        <div class="stack" style="margin:24px 0">
            <button type="button" class="btn btn-sm btn-block" onclick="copyItinerary()" style="border:1px solid var(--border);border-radius:8px;padding:8px;font-size:13px;background:var(--surface)">
                📋 Copy Itinerary
            </button>
            <form method="POST" action="{{ route('itineraries.update', $itinerary->id) }}" style="display:inline">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="confirmed">
                <button type="submit" class="btn btn-primary btn-block">Confirm & Save</button>
            </form>
            @if($itinerary->status !== 'cancelled')
            <form method="POST" action="{{ route('itineraries.destroy', $itinerary->id) }}"
                  onsubmit="return confirm('Delete this itinerary? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-block" style="background:var(--danger);color:#fff">Delete Itinerary</button>
            </form>
            @endif
        </div>
    </div>
</div>

<nav class="bottom-nav">
    <a class="nav-item active" href="{{ route('itineraries.show', $itinerary->id) }}">
        <svg viewBox="0 0 24 24"><path d="M4 10.5L12 4l8 6.5V20a1 1 0 01-1 1h-5v-6H10v6H5a1 1 0 01-1-1v-9.5z"/></svg>
        Home
    </a>
    <a class="nav-item" href="{{ route('bookings.index') }}">
        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 5V3M16 5V3"/></svg>
        Booking
    </a>
    <a class="nav-item" href="{{ route('chat') }}">
        <svg viewBox="0 0 24 24"><path d="M5 18l-1 3 3-1h9a3 3 0 003-3V7a3 3 0 00-3-3H8a3 3 0 00-3 3v11z"/></svg>
        Chat
    </a>
    <a class="nav-item" href="{{ route('history') }}">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0116 0"/></svg>
        Profile
    </a>
</nav>

{{-- Item Edit/Add Modal --}}
<div id="itemModal" class="item-modal-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:50;align-items:center;justify-content:center">
    <div class="card" style="width:90%;max-width:420px;max-height:90vh;overflow-y:auto;position:relative">
        <div class="row-between" style="margin-bottom:12px">
            <h2 id="modalTitle">Add Activity</h2>
            <button type="button" class="icon-btn" onclick="closeItemModal()">
                <svg viewBox="0 0 24 24" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form method="POST" id="itemForm" action="{{ route('itinerary-items.store', $itinerary->id) }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="day_number" id="formDayNumber" value="">

            <div class="field">
                <label class="field-label">Category</label>
                <select class="select" name="type" id="formType">
                    <option value="hotel">Hotel</option>
                    <option value="restaurant">Restaurant</option>
                    <option value="attraction">Attraction</option>
                    <option value="transport">Transport</option>
                    <option value="shopping">Shopping</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="field">
                <label class="field-label">Activity Name</label>
                <input class="input" type="text" name="name" id="formName" required maxlength="200" placeholder="e.g. Sunset at Tanah Lot">
            </div>

            <div class="field">
                <label class="field-label">Time</label>
                <input class="input" type="time" name="schedule_time" id="formTime" required>
            </div>

            <div class="field">
                <label class="field-label">Description (optional)</label>
                <input class="input" type="text" name="description" id="formDesc" placeholder="Brief description...">
            </div>

            <div class="field">
                <label class="field-label">Est. Cost (optional)</label>
                <input class="input" type="number" name="estimated_cost" id="formCost" min="0" placeholder="0">
            </div>

            <div class="field">
                <label class="field-label">Location</label>
                <div class="row" style="gap:8px">
                    <input class="input" type="text" name="location" id="formLocation" placeholder="Place name or address" style="flex:1">
                    <button type="button" class="btn btn-sm" id="btnOpenMap" onclick="openMapModal()" style="border:1px solid var(--accent-hex);color:var(--accent-hex);border-radius:8px;padding:6px 12px;white-space:nowrap">
                        Pick on Map
                    </button>
                </div>
            </div>
            <input type="hidden" name="latitude" id="formLat" value="">
            <input type="hidden" name="longitude" id="formLng" value="">

            <div id="currentLocation" style="display:none;margin-bottom:12px">
                <p class="caption">Selected: <span id="selectedLocationText" class="mono small" style="color:var(--accent-hex)"></span></p>
                <button type="button" class="btn btn-sm" onclick="clearLocation()" style="margin-top:4px;color:var(--danger);border:1px solid var(--danger);background:transparent;border-radius:6px;padding:4px 10px">Clear Location</button>
            </div>

            <div class="row" style="gap:8px;margin-top:16px">
                <button type="submit" class="btn btn-primary" style="flex:1">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeItemModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Google Maps Location Picker Modal --}}
<div id="mapModal" class="item-modal-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:51;align-items:center;justify-content:center">
    <div class="card" style="width:95%;max-width:500px;position:relative">
        <div class="row-between" style="margin-bottom:8px">
            <h2 style="font-size:16px">Pick Location</h2>
            <button type="button" class="icon-btn" onclick="closeMapModal()">
                <svg viewBox="0 0 24 24" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <input id="mapSearch" class="input" type="text" placeholder="Search for a place..." style="margin-bottom:8px">

        <div id="mapContainer" style="width:100%;height:300px;border-radius:8px;margin-bottom:8px">
            <div id="mapFallback" style="display:none;width:100%;height:100%;background:var(--surface);border-radius:8px;text-align:center;padding-top:100px;color:var(--muted)">
                <p>Map not available</p>
                <p class="small" style="margin-top:4px">Enter coordinates manually or fix your Google Maps API key.</p>
            </div>
        </div>

        <p class="small muted" id="mapSelectedInfo">Click on the map or search to select a location.</p>

        <div class="row" style="gap:8px;margin-top:8px">
            <button type="button" class="btn btn-primary" onclick="confirmMapLocation()" style="flex:1">Confirm Location</button>
            <button type="button" class="btn btn-secondary" onclick="closeMapModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
// --- Item Modal ---
var editItemId = null;

function openAddModal(dayNumber) {
    editItemId = null;
    document.getElementById('modalTitle').textContent = 'Add Activity';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('itemForm').action = '{{ route('itinerary-items.store', $itinerary->id) }}';
    document.getElementById('formDayNumber').value = dayNumber;
    clearForm();
    document.getElementById('itemModal').style.display = 'flex';
}

function openEditModal(data) {
    editItemId = data.id;
    document.getElementById('modalTitle').textContent = 'Edit Activity';
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('itemForm').action = '{{ url('/itineraries/'.$itinerary->id.'/items') }}/' + data.id;
    document.getElementById('formDayNumber').value = data.day_number;
    document.getElementById('formType').value = data.type;
    document.getElementById('formName').value = data.name;
    document.getElementById('formTime').value = data.schedule_time || '09:00';
    document.getElementById('formDesc').value = data.description || '';
    document.getElementById('formCost').value = data.estimated_cost || '';
    document.getElementById('formLocation').value = data.location || '';
    document.getElementById('formLat').value = data.latitude || '';
    document.getElementById('formLng').value = data.longitude || '';

    updateLocationDisplay();
    document.getElementById('itemModal').style.display = 'flex';
}

function closeItemModal() {
    document.getElementById('itemModal').style.display = 'none';
    clearForm();
}

function clearForm() {
    document.getElementById('formType').value = 'attraction';
    document.getElementById('formName').value = '';
    document.getElementById('formTime').value = '09:00';
    document.getElementById('formDesc').value = '';
    document.getElementById('formCost').value = '';
    document.getElementById('formLocation').value = '';
    document.getElementById('formLat').value = '';
    document.getElementById('formLng').value = '';
    document.getElementById('currentLocation').style.display = 'none';
}

// --- Map Modal ---
var map;
var marker;
var selectedLat = null;
var selectedLng = null;
var selectedAddress = null;

function openMapModal() {
    selectedLat = parseFloat(document.getElementById('formLat').value) || null;
    selectedLng = parseFloat(document.getElementById('formLng').value) || null;

    document.getElementById('mapModal').style.display = 'flex';

    // Google Maps init
    if (!map) {
        var defaultPos = { lat: -8.4095, lng: 115.1889 }; // Bali center
        if (selectedLat && selectedLng) {
            defaultPos = { lat: selectedLat, lng: selectedLng };
        }

        map = new google.maps.Map(document.getElementById('mapContainer'), {
            center: defaultPos,
            zoom: 13,
            mapTypeControl: false
        });

        marker = new google.maps.Marker({
            map: map,
            position: defaultPos,
            draggable: true
        });

        // Click to place marker
        map.addListener('click', function(e) {
            marker.setPosition(e.latLng);
            updateMapSelection(e.latLng);
        });

        // Drag marker
        marker.addListener('dragend', function() {
            updateMapSelection(marker.getPosition());
        });

        // Search autocomplete
        var searchInput = document.getElementById('mapSearch');
        var autocomplete = new google.maps.places.Autocomplete(searchInput);
        autocomplete.addListener('place_changed', function() {
            var place = autocomplete.getPlace();
            if (place.geometry) {
                map.setCenter(place.geometry.location);
                map.setZoom(15);
                marker.setPosition(place.geometry.location);
                updateMapSelection(place.geometry.location, place.formatted_address);
            }
        });

        // Fallback: geocode on Enter key (handles when autocomplete dropdown doesn't appear)
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var query = this.value.trim();
                if (query) {
                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode({ address: query }, function(results, status) {
                        if (status === 'OK' && results[0]) {
                            map.setCenter(results[0].geometry.location);
                            map.setZoom(15);
                            marker.setPosition(results[0].geometry.location);
                            updateMapSelection(results[0].geometry.location, results[0].formatted_address);
                        } else {
                            alert('Location not found. Try a different search term or click on the map.');
                        }
                    });
                }
            }
        });
    } else {
        // Recenter if reopening
        var pos = { lat: selectedLat || -8.4095, lng: selectedLng || 115.1889 };
        map.setCenter(pos);
        marker.setPosition(pos);
        google.maps.event.trigger(map, 'resize');
    }
}

function updateMapSelection(latLng, address) {
    selectedLat = latLng.lat();
    selectedLng = latLng.lng();
    selectedAddress = address || latLng.lat().toFixed(6) + ', ' + latLng.lng().toFixed(6);

    // Reverse geocode if no address provided
    if (!address) {
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: latLng }, function(results, status) {
            if (status === 'OK' && results[0]) {
                selectedAddress = results[0].formatted_address;
            }
            document.getElementById('mapSelectedInfo').textContent = selectedAddress;
        });
    } else {
        document.getElementById('mapSelectedInfo').textContent = selectedAddress;
    }
}

function confirmMapLocation() {
    if (selectedLat && selectedLng) {
        document.getElementById('formLat').value = selectedLat.toFixed(7);
        document.getElementById('formLng').value = selectedLng.toFixed(7);
        document.getElementById('formLocation').value = selectedAddress || (selectedLat.toFixed(6) + ', ' + selectedLng.toFixed(6));
        updateLocationDisplay();
    }
    closeMapModal();
}

function closeMapModal() {
    document.getElementById('mapModal').style.display = 'none';
}

function updateLocationDisplay() {
    var lat = document.getElementById('formLat').value;
    var lng = document.getElementById('formLng').value;
    var loc = document.getElementById('formLocation').value;
    var el = document.getElementById('currentLocation');
    var text = document.getElementById('selectedLocationText');

    if (lat && lng) {
        el.style.display = 'block';
        text.textContent = loc || (parseFloat(lat).toFixed(6) + ', ' + parseFloat(lng).toFixed(6));
    } else {
        el.style.display = 'none';
    }
}

function clearLocation() {
    document.getElementById('formLat').value = '';
    document.getElementById('formLng').value = '';
    document.getElementById('formLocation').value = '';
    document.getElementById('currentLocation').style.display = 'none';
}

// Close modals on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('item-modal-overlay')) {
        e.target.style.display = 'none';
    }
});

// Copy itinerary text to clipboard
function copyItinerary() {
    var ta = document.getElementById('itineraryText');
    ta.style.display = 'block';
    ta.select();
    navigator.clipboard.writeText(ta.value).then(function() {
        alert('Itinerary copied to clipboard!');
    }).catch(function() {
        ta.select();
        document.execCommand('copy');
        alert('Copied!');
    });
    ta.style.display = 'none';
}
</script>

@endif

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBTs206d5ZJ09vDLApUBn5W1pXtNN_xeMc&libraries=places" async defer></script>

@endsection
