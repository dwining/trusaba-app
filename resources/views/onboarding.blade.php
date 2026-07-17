@extends('layouts.app', ['navActive' => 'home'])

@section('title', 'TruSaba · Profil Trip')

@section('content')
<div class="app-header">
    <img class="logo" src="{{ asset('logo.jpeg') }}" alt="TruSaba" />
    <div class="title-block">
        <p class="eyebrow">Langkah <span id="stepLabel">1</span>/4</p>
        <h1>Ceritakan trip-mu</h1>
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

<div class="app-body has-sticky" id="formBody" style="padding-bottom:100px">
    {{-- Step 0: Destinasi + DOB --}}
    <section class="pad step-panel" data-panel="0">
        <p class="muted small" style="margin-bottom:16px">AI TruSaba butuh sedikit info agar itinerary terasa personal.</p>
        <div class="field">
            <label class="field-label" for="dest">Destinasi <span class="req">*</span></label>
            <input class="input" id="dest" type="text" value="Bali" placeholder="Kota / destinasi" required />
        </div>
        <div class="field">
            <label class="field-label" for="dob">Tanggal lahir <span class="req">*</span></label>
            <input class="input" id="dob" type="date" value="{{ old('birth_date', Auth::user()->travellerProfile?->birth_date?->format('Y-m-d') ?? '1998-06-15') }}" required />
        </div>
    </section>

    {{-- Step 1: Hobby --}}
    <section class="pad step-panel" data-panel="1" hidden>
        <p class="muted small" style="margin-bottom:16px">Pilih hobi yang sering kamu lakukan saat traveling.</p>
        <label class="field-label">Hobby</label>
        <div class="chips" id="hobbyChips">
            <button type="button" class="chip active" data-v="fotografi">Fotografi</button>
            <button type="button" class="chip" data-v="kuliner">Kuliner</button>
            <button type="button" class="chip active" data-v="snorkeling">Snorkeling</button>
            <button type="button" class="chip" data-v="hiking">Hiking</button>
            <button type="button" class="chip" data-v="belanja">Belanja</button>
            <button type="button" class="chip" data-v="yoga">Yoga</button>
            <button type="button" class="chip" data-v="musik">Musik</button>
            <button type="button" class="chip" data-v="surfing">Surfing</button>
        </div>
    </section>

    {{-- Step 2: Interest --}}
    <section class="pad step-panel" data-panel="2" hidden>
        <p class="muted small" style="margin-bottom:16px">Minat membantu AI merekomendasikan spot yang pas.</p>
        <label class="field-label">Interest</label>
        <div class="chips" id="interestChips">
            <button type="button" class="chip active" data-v="pantai">Pantai</button>
            <button type="button" class="chip active" data-v="budaya">Budaya</button>
            <button type="button" class="chip" data-v="alam">Alam</button>
            <button type="button" class="chip" data-v="nightlife">Nightlife</button>
            <button type="button" class="chip" data-v="wellness">Wellness</button>
            <button type="button" class="chip active" data-v="kuliner-lokal">Kuliner lokal</button>
            <button type="button" class="chip" data-v="adventure">Adventure</button>
            <button type="button" class="chip" data-v="foto-spot">Foto spot</button>
        </div>
    </section>

    {{-- Step 3: Budget + Dates --}}
    <section class="pad step-panel" data-panel="3" hidden>
        <p class="muted small" style="margin-bottom:16px">Estimasi budget & lama traveling untuk 1 orang.</p>
        <div class="field">
            <div class="row-between">
                <label class="field-label" style="margin:0">Budget trip</label>
                <span class="mono" style="font-weight:600;color:var(--accent-hex)" id="budgetVal">Rp 5.000.000</span>
            </div>
            <div class="range-wrap">
                <input type="range" id="budget" min="1000000" max="20000000" step="500000" value="5000000" />
            </div>
            <div class="row-between caption">
                <span>Rp 1jt</span><span>Rp 20jt</span>
            </div>
        </div>
        <div class="field">
            <label class="field-label">Lama traveling</label>
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
            <p class="caption" style="margin-top:8px" id="durationHint">± 3 hari · 2 malam</p>
        </div>
    </section>
</div>

{{-- Sticky CTA --}}
<div class="sticky-cta" style="bottom:var(--safe-b);z-index:21">
    <div class="row" style="gap:10px">
        <button type="button" class="btn btn-secondary" id="btnBack" style="display:none;flex:0 0 auto;padding:0 16px">Kembali</button>
        <button type="button" class="btn btn-primary btn-block" id="btnNext">Lanjut</button>
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
            document.getElementById('durationHint').textContent = '± ' + d + ' hari · ' + Math.max(0, d - 1) + ' malam';
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
            btnNext.textContent = step === total - 1 ? 'Proses' : 'Lanjut';
        }

        function collectData() {
            var dest = document.getElementById('dest').value.trim();
            var dob = document.getElementById('dob').value;
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
                hobbies: hobbies,
                interests: interests,
                budget: budget,
                date_start: dateStart,
                date_end: dateEnd
            };
        }

        btnNext.addEventListener('click', function () {
            if (step === 0) {
                var dest = document.getElementById('dest').value.trim();
                var dob = document.getElementById('dob').value;
                if (!dest || !dob) {
                    alert('Destinasi dan tanggal lahir wajib diisi.');
                    return;
                }
            }
            if (step < total - 1) {
                step++;
                render();
            } else {
                // Submit profile data then redirect
                var payload = collectData();
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('profile.update') }}';
                form.style.display = 'none';

                var methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);

                var tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = '{{ csrf_token() }}';
                form.appendChild(tokenInput);

                Object.keys(payload).forEach(function (key) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = Array.isArray(payload[key]) ? JSON.stringify(payload[key]) : payload[key];
                    form.appendChild(input);
                });

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
