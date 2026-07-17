@extends('layouts.app', ['navActive' => 'chat'])
@section('title', 'TruSaba · Chat AI')
@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ route('today') }}" aria-label="Kembali">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">Travel Companion</p>
        <h1>Chat AI TruSaba</h1>
    </div>
    <span class="badge badge-success" id="statusBadge">Online</span>
</div>

<div class="app-body" style="padding-bottom:calc(var(--nav-h) + 72px)">
    <div class="chat-thread" id="thread">
        <div class="bubble bubble-ai">
            Halo! Aku asisten perjalanan TruSaba. Tanya apa saja — rekomendasi wisata, itinerary, booking, atau tips traveling 😊
            <span class="time">--:--</span>
        </div>
    </div>
</div>

<div class="chat-input-bar">
    <input class="input" id="chatInput" type="text" placeholder="Tanya soal perjalananmu…" autocomplete="off" />
    <button type="button" class="chat-send" id="chatSend" aria-label="Kirim">
        <svg viewBox="0 0 24 24"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
    </button>
</div>

@push('scripts')
<script>
(function () {
    var input = document.getElementById('chatInput');
    var thread = document.getElementById('thread');
    var sendBtn = document.getElementById('chatSend');
    var statusBadge = document.getElementById('statusBadge');
    var idleTimer = null;
    var idlePromptTimer = null;
    var idlePromptShown = false;
    var IDLE_TIMEOUT = 10 * 60 * 1000; // 10 menit
    var IDLE_PROMPT_GRACE = 30 * 1000;  // 30 detik setelah prompt

    function resetIdleTimer() {
        clearTimeout(idleTimer);
        clearTimeout(idlePromptTimer);
        idlePromptShown = false;
        statusBadge.textContent = 'Online';
        statusBadge.className = 'badge badge-success';

        idleTimer = setTimeout(function () {
            // User idle for 10 minutes — ask if still there
            statusBadge.textContent = 'Idle';
            statusBadge.className = 'badge badge-warn';
            idlePromptShown = true;

            addBubble('Kamu masih di sini? Kalau tidak ada balasan dalam 30 detik, sesi chat akan diakhiri ya.', 'bubble-ai');

            idlePromptTimer = setTimeout(function () {
                // No response after prompt — end session
                endSession();
            }, IDLE_PROMPT_GRACE);
        }, IDLE_TIMEOUT);
    }

    function endSession() {
        fetch('{{ route('chat.end') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        });
        addBubble('Sesi chat diakhiri karena tidak ada aktivitas. Refresh halaman untuk memulai sesi baru.', 'bubble-ai');
        statusBadge.textContent = 'Offline';
        statusBadge.className = 'badge badge-danger';
        sendBtn.disabled = true;
        input.disabled = true;
    }

    function addBubble(text, cls) {
        var now = new Date();
        var time = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
        var div = document.createElement('div');
        div.className = 'bubble ' + cls;
        div.innerHTML = text.replace(/</g,'&lt;') + '<span class="time">' + time + '</span>';
        thread.appendChild(div);
        thread.scrollTop = thread.scrollHeight;
    }

    function send() {
        var msg = input.value.trim();
        if (!msg || sendBtn.disabled) return;

        if (idlePromptShown) {
            // User responded to idle prompt — reset everything
            idlePromptShown = false;
            clearTimeout(idlePromptTimer);
        }

        addBubble(msg, 'bubble-user');
        input.value = '';
        sendBtn.disabled = true;
        resetIdleTimer();

        fetch('{{ route('chat.send') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: msg }),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            addBubble(data.reply, 'bubble-ai');
            sendBtn.disabled = false;
            input.focus();
            resetIdleTimer();
        })
        .catch(function() {
            addBubble('Maaf, ada gangguan. Coba lagi ya.', 'bubble-ai');
            sendBtn.disabled = false;
            resetIdleTimer();
        });
    }

    sendBtn.addEventListener('click', send);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') send();
    });

    // Start idle timer on page load
    resetIdleTimer();

    // Reset timer on any user interaction
    document.addEventListener('click', resetIdleTimer);
    document.addEventListener('keydown', resetIdleTimer);
})();
</script>
@endpush
@endsection
