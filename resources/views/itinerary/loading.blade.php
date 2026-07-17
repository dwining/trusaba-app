@extends('layouts.app', ['navActive' => 'home'])
@section('title', 'TruSaba · AI Menyusun Itinerary')
@section('content')
<div class="app-body no-nav" style="display:flex;flex-direction:column">
    <div class="loading-wrap">
        <div class="plane-orbit">
            <div class="ring"></div>
            <div class="core">
                <svg viewBox="0 0 24 24" style="width:36px;height:36px;stroke:var(--accent-hex);fill:none;stroke-width:2"><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/><circle cx="12" cy="12" r="4"/></svg>
            </div>
        </div>
        <p class="eyebrow" style="color:var(--accent-hex);margin-bottom:8px">AI TruSaba</p>
        <h1 style="margin-bottom:8px">Sedang menyusun itinerary</h1>
        <p class="muted small" style="max-width:280px;margin:0 auto">
            Trip {{ $itinerary->destination }} · {{ $itinerary->duration_days }} hari disesuaikan profil, budget, dan minatmu.
        </p>
        <div class="progress-soft">
            <div class="fill"></div>
        </div>
        <p class="microcopy" id="micro">Sedang mencari tempat terbaik untukmu…</p>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var itineraryId = {{ $itinerary->id }};
    var microEl = document.getElementById('micro');
    var lines = [
        'Sedang mencari tempat terbaik untukmu…',
        'Menyesuaikan budget akomodasi…',
        'Mencari restoran lokal yang cocok dengan seleramu…',
        'Menyusun jadwal harian biar tidak kelelahan…',
        'Hampir siap — packing virtual dulu ya…'
    ];
    var lineIndex = 0;
    var microInterval = setInterval(function () {
        lineIndex = (lineIndex + 1) % lines.length;
        microEl.style.opacity = '0';
        setTimeout(function () {
            microEl.textContent = lines[lineIndex];
            microEl.style.opacity = '1';
        }, 220);
    }, 1100);

    // Poll status every 2 seconds
    var pollInterval = setInterval(function () {
        fetch('/itineraries/' + itineraryId + '/status')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.status === 'draft' || data.status === 'completed') {
                    clearInterval(microInterval);
                    clearInterval(pollInterval);
                    window.location.href = '/itineraries/' + itineraryId;
                } else if (data.status === 'failed') {
                    clearInterval(microInterval);
                    clearInterval(pollInterval);
                    microEl.textContent = 'Maaf, terjadi kesalahan. Silakan coba lagi.';
                    microEl.style.color = 'var(--danger)';
                }
            })
            .catch(function () {
                // Retry on network error
            });
    }, 2000);

    // Safety timeout: redirect after 60 seconds even if not complete
    setTimeout(function () {
        window.location.href = '/itineraries/' + itineraryId;
    }, 60000);
})();
</script>
@endpush
@endsection
