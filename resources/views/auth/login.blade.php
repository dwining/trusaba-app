@extends('layouts.app', ['navActive' => 'home'])

@section('title', 'TruSaba · Login & Sign Up')

@section('content')

{{-- Toast --}}
<div class="auth-toast @if(session('toast')) show @endif" id="toast" role="status">
    {{ session('toast') }}
</div>

<div class="app-body no-nav">
    {{-- Hero --}}
    <div class="auth-hero">
        <h1>Selamat datang</h1>
        <p id="authSubtitle">Masuk untuk menyimpan itinerary AI & booking trip-mu.</p>
    </div>

    {{-- Tabs --}}
    <div class="auth-tabs" role="tablist">
        <button type="button" class="auth-tab active" role="tab" aria-selected="true" data-mode="login">Masuk</button>
        <button type="button" class="auth-tab" role="tab" aria-selected="false" data-mode="signup">Daftar</button>
    </div>

    <div class="pad" style="padding-top:12px;padding-bottom:28px">
        {{-- Google Button --}}
        <a href="{{ route('google.redirect') }}" class="btn-google">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Lanjutkan dengan Google
        </a>

        <div class="auth-divider">atau email</div>

        {{-- Login Form --}}
        <form id="formLogin" method="POST" action="{{ route('login') }}" novalidate>
            @csrf
            <div class="field">
                <label class="field-label" for="loginEmail">Email</label>
                <input class="input" id="loginEmail" name="email" type="email" autocomplete="email" placeholder="nama@email.com" required />
                @error('email')<p class="caption" style="color:var(--danger)">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label class="field-label" for="loginPass">Password</label>
                <div class="password-wrap">
                    <input class="input" id="loginPass" name="password" type="password" autocomplete="current-password" placeholder="Minimal 8 karakter" required />
                    <button type="button" class="password-toggle" data-target="loginPass" aria-label="Tampilkan password">
                        <svg viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <div class="row-between" style="margin-top:8px">
                    <label class="small muted" style="display:flex;align-items:center;gap:6px;cursor:pointer">
                        <input type="checkbox" name="remember" style="accent-color:var(--accent-hex)" /> Ingat saya
                    </label>
                    <button type="button" class="btn btn-ghost btn-sm" style="min-height:32px;padding:0 4px">Lupa password?</button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            <p class="auth-footer">Belum punya akun? <a href="#" id="linkToSignup">Daftar gratis</a></p>
        </form>

        {{-- Sign Up Form --}}
        <form id="formSignup" method="POST" action="{{ route('register') }}" hidden novalidate>
            @csrf
            <div class="field">
                <label class="field-label" for="signupName">Nama lengkap</label>
                <input class="input" id="signupName" name="name" type="text" autocomplete="name" placeholder="Nama kamu" required />
            </div>
            <div class="field">
                <label class="field-label" for="signupEmail">Email</label>
                <input class="input" id="signupEmail" name="email" type="email" autocomplete="email" placeholder="nama@email.com" required />
            </div>
            <div class="field">
                <label class="field-label" for="signupPass">Password</label>
                <div class="password-wrap">
                    <input class="input" id="signupPass" name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter" required minlength="8" />
                    <button type="button" class="password-toggle" data-target="signupPass" aria-label="Tampilkan password">
                        <svg viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <p class="field-hint">Gunakan kombinasi huruf & angka agar lebih aman.</p>
            </div>
            <div class="field">
                <label class="small muted" style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;line-height:1.4">
                    <input type="checkbox" name="terms" required style="accent-color:var(--accent-hex);margin-top:3px" />
                    <span>Saya setuju dengan <strong style="color:var(--fg)">Syarat & Kebijakan Privasi</strong> TruSaba.</span>
                </label>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Buat akun</button>
            <p class="auth-footer">Sudah punya akun? <a href="#" id="linkToLogin">Masuk</a></p>
        </form>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        var formLogin = document.getElementById('formLogin');
        var formSignup = document.getElementById('formSignup');
        var tabs = document.querySelectorAll('.auth-tab');
        var subtitle = document.getElementById('authSubtitle');
        var mode = 'login';

        function setMode(next) {
            mode = next;
            formLogin.hidden = next !== 'login';
            formSignup.hidden = next !== 'signup';
            tabs.forEach(function (t) {
                var on = t.getAttribute('data-mode') === next;
                t.classList.toggle('active', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
            });
            subtitle.textContent = next === 'login'
                ? 'Masuk untuk menyimpan itinerary AI & booking trip-mu.'
                : 'Buat akun gratis — AI TruSaba siap jadi travel companion-mu.';
            document.title = next === 'login' ? 'TruSaba · Login' : 'TruSaba · Sign Up';
        }

        tabs.forEach(function (t) {
            t.addEventListener('click', function () { setMode(t.getAttribute('data-mode')); });
        });
        document.getElementById('linkToSignup').addEventListener('click', function (e) {
            e.preventDefault();
            setMode('signup');
        });
        document.getElementById('linkToLogin').addEventListener('click', function (e) {
            e.preventDefault();
            setMode('login');
        });

        document.querySelectorAll('.password-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-target'));
                if (!input) return;
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });

        // Show validation errors from Laravel via toast
        @if($errors->any())
        var toast = document.getElementById('toast');
        toast.textContent = '{{ $errors->first() }}';
        toast.classList.add('show');
        setTimeout(function () { toast.classList.remove('show'); }, 2500);
        @endif

        // Show toast from session
        var toastEl = document.getElementById('toast');
        if (toastEl && toastEl.textContent.trim()) {
            setTimeout(function () { toastEl.classList.remove('show'); }, 2500);
        }
    })();
</script>
@endpush
@endsection
