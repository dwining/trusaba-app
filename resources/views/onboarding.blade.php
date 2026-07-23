@extends('layouts.app', ['navActive' => 'home'])

@section('title', 'TruSaba · Trip Profile')

@section('content')
<div class="app-header">
    <div class="title-block">
        <p class="eyebrow">Step <span id="stepLabel">1</span>/4</p>
        <h1>Tell us about your trip</h1>
    </div>
</div>

<div class="steps" id="steps">
    <div class="step-dot current" data-step="0">1</div>
    <div class="step-line" data-line="0"></div>
    <div class="step-dot" data-step="1">2</div>
    <div class="step-line" data-line="1"></div>
    <div class="step-dot" data-step="2">3</div>
    <div class="step-line" data-line="2"></div>
    <div class="step-dot" data-step="3">4</div>
</div>

@php $profile = Auth::user()->travellerProfile; @endphp

<div class="app-body has-sticky" id="formBody" style="padding-bottom:calc(var(--nav-h) + var(--safe-b) + 90px)">
    {{-- Step 0: Destination --}}
    <section class="pad step-panel" data-panel="0">
        <p class="muted small" style="margin-bottom:16px">TruSaba AI needs a little info to make your itinerary feel personal.</p>
        
        {{-- Profile completion CTA --}}
        @if(!$profile || !$profile->birth_date)
        <div class="card" style="background:linear-gradient(145deg,oklch(0.58 0.22 27 / 0.08),oklch(0.85 0.17 87 / 0.1));border-color:oklch(0.58 0.22 27 / 0.2);margin-bottom:16px">
            <div class="row" style="gap:10px">
                <div style="flex:1;min-width:0">
                    <h3 style="font-size:14px">Fill in your birth date first</h3>
                    <p class="small muted">Your birth date helps AI craft an itinerary suited to your age.</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="btn btn-danger btn-sm" style="flex-shrink:0">Fill Profile</a>
            </div>
        </div>
        @endif

        <div class="field">
            <label class="field-label" for="dest">Destination <span class="req">*</span></label>
            <input class="input" id="dest" type="text" value="Bali" placeholder="City / destination" required />
        </div>

        <div class="field">
            <label class="field-label" for="travelers">Travelers <span class="req">*</span></label>
            <select class="select" id="travelers">
                @for($i = 1; $i <= 10; $i++)
                <option value="{{ $i }}" {{ $i == 2 ? 'selected' : '' }}>{{ $i }} {{ Str::plural('person', $i) }}</option>
                @endfor
            </select>
        </div>

        {{-- Hidden: birth_date from profile --}}
        <input type="hidden" id="dob" value="{{ $profile?->birth_date?->format('Y-m-d') ?? '' }}" />
    </section>

    {{-- Step 1: Hobbies --}}
    <section class="pad step-panel" data-panel="1" hidden>
        <p class="muted small" style="margin-bottom:16px">Choose hobbies you often do while traveling.</p>
        <label class="field-label">Hobbies</label>
        <div class="chips" id="hobbyChips">
            @php $profileHobbies = $profile?->hobbies ?? ['fotografi', 'snorkeling']; @endphp
            <button type="button" class="chip {{ in_array('fotografi', $profileHobbies) ? 'active' : '' }}" data-v="fotografi">Photography</button>
            <button type="button" class="chip {{ in_array('kuliner', $profileHobbies) ? 'active' : '' }}" data-v="kuliner">Culinary</button>
            <button type="button" class="chip {{ in_array('snorkeling', $profileHobbies) ? 'active' : '' }}" data-v="snorkeling">Snorkeling</button>
            <button type="button" class="chip {{ in_array('hiking', $profileHobbies) ? 'active' : '' }}" data-v="hiking">Hiking</button>
            <button type="button" class="chip {{ in_array('belanja', $profileHobbies) ? 'active' : '' }}" data-v="belanja">Shopping</button>
            <button type="button" class="chip {{ in_array('yoga', $profileHobbies) ? 'active' : '' }}" data-v="yoga">Yoga</button>
            <button type="button" class="chip {{ in_array('musik', $profileHobbies) ? 'active' : '' }}" data-v="musik">Music</button>
            <button type="button" class="chip {{ in_array('surfing', $profileHobbies) ? 'active' : '' }}" data-v="surfing">Surfing</button>
        </div>
    </section>

    {{-- Step 2: Interests --}}
    <section class="pad step-panel" data-panel="2" hidden>
        <p class="muted small" style="margin-bottom:16px">Interests help AI recommend the right spots.</p>
        <label class="field-label">Interests</label>
        <div class="chips" id="interestChips">
            @php $profileInterests = $profile?->interests ?? ['pantai', 'budaya', 'kuliner-lokal']; @endphp
            <button type="button" class="chip {{ in_array('pantai', $profileInterests) ? 'active' : '' }}" data-v="pantai">Beach</button>
            <button type="button" class="chip {{ in_array('budaya', $profileInterests) ? 'active' : '' }}" data-v="budaya">Culture</button>
            <button type="button" class="chip {{ in_array('alam', $profileInterests) ? 'active' : '' }}" data-v="alam">Nature</button>
            <button type="button" class="chip {{ in_array('nightlife', $profileInterests) ? 'active' : '' }}" data-v="nightlife">Nightlife</button>
            <button type="button" class="chip {{ in_array('wellness', $profileInterests) ? 'active' : '' }}" data-v="wellness">Wellness</button>
            <button type="button" class="chip {{ in_array('kuliner-lokal', $profileInterests) ? 'active' : '' }}" data-v="kuliner-lokal">Local cuisine</button>
            <button type="button" class="chip {{ in_array('adventure', $profileInterests) ? 'active' : '' }}" data-v="adventure">Adventure</button>
            <button type="button" class="chip {{ in_array('foto-spot', $profileInterests) ? 'active' : '' }}" data-v="foto-spot">Photo spots</button>
        </div>
    </section>

    {{-- Step 3: Budget + Dates --}}
    <section class="pad step-panel" data-panel="3" hidden>
        <p class="muted small" style="margin-bottom:16px">Budget estimate & trip duration for 1 person.</p>
        <div class="field">
            <div class="row-between">
                <label class="field-label" style="margin:0">Trip budget</label>
                <span class="mono" style="font-weight:600;color:var(--accent-hex)" id="budgetVal">
                    Rp {{ number_format(5000000, 0, ',', '.') }}
                </span>
            </div>
            <div class="range-wrap">
                <input type="range" id="budget" min="1000000" max="20000000" step="500000" value="5000000" />
            </div>
            <div class="row-between caption">
                <span>Rp 1M</span><span>Rp 20M</span>
            </div>
        </div>
        <div class="field">
            <label class="field-label">Trip duration</label>
            <div class="row" style="gap:10px">
                <div style="flex:1">
                    <span class="caption">Check-in</span>
                    <input class="input" type="date" id="dateStart" value="2026-08-12" />
                </div>
                <div style="flex:1">
                    <span class="caption">Check-out</span>
                    <input class="input" type="date" id="dateEnd" value="2026-08-15" />
                </div>
            </div>
            <p class="caption" style="margin-top:8px" id="durationHint">± 3 days · 2 nights</p>
        </div>
    </section>
</div>

{{-- Sticky CTA --}}
<div class="sticky-cta" style="bottom:calc(var(--nav-h) + var(--safe-b));z-index:21">
    <div class="row" style="gap:10px">
        <button type="button" class="btn btn-secondary" id="btnBack" style="display:none;flex:0 0 auto;padding:0 16px">Back</button>
        <button type="button" class="btn btn-primary btn-block" id="btnNext">Next</button>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        var step = 0;
        var total = 4;
        var panels = document.querySelectorAll('.step-panel');
        var dots = document.querySelectorAll('.step-dot');
        var lines = document.querySelectorAll('.step-line');
        var btnNext = document.getElementById('btnNext');
        var btnBack = document.getElementById('btnBack');
        var stepLabel = document.getElementById('stepLabel');

        function formatRp(n) {
            return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        function daysBetween(a, b) {
            var ms = new Date(b) - new Date(a);
            return Math.max(1, Math.round(ms / 86400000));
        }
        function updateDuration() {
            var s = document.getElementById('dateStart').value;
            var e = document.getElementById('dateEnd').value;
            if (!s || !e) return;
            var d = daysBetween(s, e);
            document.getElementById('durationHint').textContent = '± ' + d + ' days · ' + Math.max(0, d - 1) + ' nights';
        }
        document.getElementById('budget').addEventListener('input', function (e) {
            document.getElementById('budgetVal').textContent = formatRp(+e.target.value);
        });
        document.getElementById('dateStart').addEventListener('change', updateDuration);
        document.getElementById('dateEnd').addEventListener('change', updateDuration);

        document.querySelectorAll('.chips').forEach(function (wrap) {
            wrap.addEventListener('click', function (e) {
                var chip = e.target.closest('.chip');
                if (!chip) return;
                chip.classList.toggle('active');
            });
        });

        function render() {
            panels.forEach(function (p) {
                p.hidden = +p.getAttribute('data-panel') !== step;
            });
            dots.forEach(function (d, i) {
                d.classList.remove('current', 'done');
                if (i < step) { d.classList.add('done'); d.textContent = '✓'; }
                else if (i === step) { d.classList.add('current'); d.textContent = String(i + 1); }
                else { d.textContent = String(i + 1); }
            });
            lines.forEach(function (l, i) {
                l.classList.toggle('done', i < step);
            });
            stepLabel.textContent = String(step + 1);
            btnBack.style.display = step === 0 ? 'none' : 'inline-flex';
            btnNext.textContent = step === total - 1 ? 'Process' : 'Next';
        }

        function collectData() {
            var dest = document.getElementById('dest').value.trim();
            var dob = document.getElementById('dob').value;
            var travelers = document.getElementById('travelers').value;
            var hobbies = [];
            document.querySelectorAll('#hobbyChips .chip.active').forEach(function (c) {
                hobbies.push(c.getAttribute('data-v'));
            });
            var interests = [];
            document.querySelectorAll('#interestChips .chip.active').forEach(function (c) {
                interests.push(c.getAttribute('data-v'));
            });
            var budget = +document.getElementById('budget').value;
            var dateStart = document.getElementById('dateStart').value;
            var dateEnd = document.getElementById('dateEnd').value;
            return {
                destination: dest,
                birth_date: dob,
                travelers: travelers,
                hobbies: hobbies,
                interests: interests,
                budget: budget,
                date_start: dateStart,
                date_end: dateEnd,
                redirect_to: 'onboarding'
            };
        }

        btnNext.addEventListener('click', function () {
            if (step === 0) {
                var dest = document.getElementById('dest').value.trim();
                if (!dest) {
                    alert('Destination is required.');
                    return;
                }
            }
            if (step < total - 1) {
                step++;
                render();
            } else {
                // Submit directly to itinerary generation
                var payload = collectData();
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('itineraries.generate') }}';
                form.style.display = 'none';

                var tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = '{{ csrf_token() }}';
                form.appendChild(tokenInput);

                // Map onboarding fields to itinerary generate fields
                var fieldMap = {
                    destination: payload.destination,
                    start_date: payload.date_start,
                    end_date: payload.date_end,
                    budget: payload.budget,
                    birth_date: payload.birth_date,
                    travelers: payload.travelers,
                };

                Object.keys(fieldMap).forEach(function (key) {
                    if (fieldMap[key]) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = fieldMap[key];
                        form.appendChild(input);
                    }
                });

                // Hobbies as array
                if (payload.hobbies && payload.hobbies.length) {
                    payload.hobbies.forEach(function (h) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'hobbies[]';
                        input.value = h;
                        form.appendChild(input);
                    });
                }

                // Interests as array
                if (payload.interests && payload.interests.length) {
                    payload.interests.forEach(function (i) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'interests[]';
                        input.value = i;
                        form.appendChild(input);
                    });
                }

                document.body.appendChild(form);
                form.submit();
            }
        });
        btnBack.addEventListener('click', function () {
            if (step > 0) { step--; render(); }
        });
        render();
    })();
</script>
@endpush
@endsection
