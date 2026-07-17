@extends('layouts.app', ['showNav' => false])
@section('title', 'TruSaba · Edit Profil')
@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ route('history') }}" aria-label="Kembali">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">Profil Saya</p>
        <h1>Edit Data Diri</h1>
    </div>
</div>

<div class="app-body no-nav" style="padding-bottom:24px">
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="redirect_to" value="history">

        <div class="pad" style="padding-top:16px">
            {{-- User Info --}}
            <div class="field">
                <label class="field-label" for="name">Nama lengkap</label>
                <input class="input" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" placeholder="Nama kamu" />
            </div>
            <div class="field">
                <label class="field-label" for="email">Email</label>
                <input class="input" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="nama@email.com" />
            </div>

            <div style="height:1px;background:var(--border);margin:16px 0"></div>

            {{-- Profile Info --}}
            <div class="field">
                <label class="field-label" for="birthDate">Tanggal lahir</label>
                <input class="input" id="birthDate" name="birth_date" type="date" value="{{ old('birth_date', $user->travellerProfile?->birth_date?->format('Y-m-d')) }}" />
            </div>

            <div class="field">
                <label class="field-label" for="phone">No. Telepon</label>
                <input class="input" id="phone" name="phone" type="text" value="{{ old('phone', $user->travellerProfile?->phone) }}" placeholder="0812xxxx" />
            </div>

            <div class="field">
                <label class="field-label">Hobby</label>
                <div class="chips" id="hobbyChips">
                    @php $selectedHobbies = old('hobbies', $user->travellerProfile?->hobbies ?? []); @endphp
                    @foreach(['Fotografi', 'Kuliner', 'Snorkeling', 'Hiking', 'Belanja', 'Yoga', 'Musik', 'Surfing', 'Diving', 'Camping'] as $h)
                    <button type="button" class="chip {{ in_array($h, $selectedHobbies) ? 'active' : '' }}" data-v="{{ $h }}" onclick="this.classList.toggle('active')">{{ $h }}</button>
                    @endforeach
                </div>
                @foreach($selectedHobbies as $h)
                <input type="hidden" name="hobbies[]" value="{{ $h }}" class="hobby-input">
                @endforeach
            </div>

            <div class="field">
                <label class="field-label">Minat</label>
                <div class="chips" id="interestChips">
                    @php $selectedInterests = old('interests', $user->travellerProfile?->interests ?? []); @endphp
                    @foreach(['Pantai', 'Budaya', 'Alam', 'Nightlife', 'Wellness', 'Kuliner lokal', 'Adventure', 'Foto spot'] as $i)
                    <button type="button" class="chip {{ in_array($i, $selectedInterests) ? 'active' : '' }}" data-v="{{ $i }}" onclick="this.classList.toggle('active')">{{ $i }}</button>
                    @endforeach
                </div>
                @foreach($selectedInterests as $i)
                <input type="hidden" name="interests[]" value="{{ $i }}" class="interest-input">
                @endforeach
            </div>

            <div class="field">
                <label class="field-label">Budget default (opsional)</label>
                <div class="range-wrap">
                    <input type="range" id="budget" name="default_budget" min="1000000" max="20000000" step="500000" value="{{ old('default_budget', $user->travellerProfile?->default_budget ?? 5000000) }}" />
                </div>
                <div class="row-between caption">
                    <span>Rp 1jt</span><span>Rp 20jt</span>
                </div>
                <p class="mono small" style="font-weight:600;color:var(--accent-hex);margin-top:4px" id="budgetVal">
                    Rp {{ number_format(old('default_budget', $user->travellerProfile?->default_budget ?? 5000000), 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div style="padding:20px">
            <button type="submit" class="btn btn-primary btn-block">Simpan Profil</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Update hidden inputs when chips are toggled
function syncChips(containerId, inputClass) {
    document.getElementById(containerId).addEventListener('click', function(e) {
        var chip = e.target.closest('.chip');
        if (!chip) return;
        // Remove existing hidden inputs and rebuild
        document.querySelectorAll('.' + inputClass).forEach(function(el) { el.remove(); });
        document.querySelectorAll('#' + containerId + ' .chip.active').forEach(function(active) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = containerId === 'hobbyChips' ? 'hobbies[]' : 'interests[]';
            input.value = active.getAttribute('data-v');
            input.className = inputClass;
            active.parentElement.appendChild(input);
        });
    });
}
syncChips('hobbyChips', 'hobby-input');
syncChips('interestChips', 'interest-input');

// Budget display
document.getElementById('budget').addEventListener('input', function(e) {
    var n = parseInt(e.target.value);
    document.getElementById('budgetVal').textContent = 'Rp ' + n.toLocaleString('id-ID');
});
</script>
@endpush
@endsection
