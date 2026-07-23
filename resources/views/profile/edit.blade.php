@extends('layouts.app', ['navActive' => 'home'])
@section('title', 'TruSaba · Edit Profile')
@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ route('history') }}" aria-label="Back">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">My Profile</p>
        <h1>Edit Personal Data</h1>
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
                <label class="field-label" for="name">Full name</label>
                <input class="input" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" placeholder="Your name" />
            </div>
            <div class="field">
                <label class="field-label" for="email">Email</label>
                <input class="input" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="name@email.com" />
            </div>

            <div style="height:1px;background:var(--border);margin:16px 0"></div>

            {{-- Profile Info --}}
            <div class="field">
                <label class="field-label" for="birthDate">Date of birth</label>
                <input class="input" id="birthDate" name="birth_date" type="date" value="{{ old('birth_date', $user->travellerProfile?->birth_date?->format('Y-m-d')) }}" />
            </div>

            <div class="field">
                <label class="field-label" for="phone">Phone number</label>
                <input class="input" id="phone" name="phone" type="text" value="{{ old('phone', $user->travellerProfile?->phone) }}" placeholder="0812xxxx" />
            </div>

            <div class="field">
                <label class="field-label">Hobbies</label>
                <div class="chips" id="hobbyChips">
                    @php $selectedHobbies = old('hobbies', $user->travellerProfile?->hobbies ?? []); @endphp
                    @foreach(['Fotografi' => 'Photography', 'Kuliner' => 'Culinary', 'Snorkeling' => 'Snorkeling', 'Hiking' => 'Hiking', 'Belanja' => 'Shopping', 'Yoga' => 'Yoga', 'Musik' => 'Music', 'Surfing' => 'Surfing', 'Diving' => 'Diving', 'Camping' => 'Camping'] as $key => $label)
                    <button type="button" class="chip {{ in_array($key, $selectedHobbies) ? 'active' : '' }}" data-v="{{ $key }}" onclick="this.classList.toggle('active')">{{ $label }}</button>
                    @endforeach
                </div>
                @foreach($selectedHobbies as $h)
                <input type="hidden" name="hobbies[]" value="{{ $h }}" class="hobby-input">
                @endforeach
            </div>

            <div class="field">
                <label class="field-label">Interests</label>
                <div class="chips" id="interestChips">
                    @php $selectedInterests = old('interests', $user->travellerProfile?->interests ?? []); @endphp
                    @foreach(['Pantai' => 'Beach', 'Budaya' => 'Culture', 'Alam' => 'Nature', 'Nightlife' => 'Nightlife', 'Wellness' => 'Wellness', 'Kuliner lokal' => 'Local cuisine', 'Adventure' => 'Adventure', 'Foto spot' => 'Photo spots'] as $key => $label)
                    <button type="button" class="chip {{ in_array($key, $selectedInterests) ? 'active' : '' }}" data-v="{{ $key }}" onclick="this.classList.toggle('active')">{{ $label }}</button>
                    @endforeach
                </div>
                @foreach($selectedInterests as $i)
                <input type="hidden" name="interests[]" value="{{ $i }}" class="interest-input">
                @endforeach
            </div>

        <div style="padding:20px 20px calc(var(--nav-h) + var(--safe-b) + 20px)">
            <button type="submit" class="btn btn-primary btn-block">Save Profile</button>
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
</script>
@endpush
@endsection
