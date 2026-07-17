@extends('layouts.app', ['navActive' => 'chat'])
@section('title', 'TruSaba · Chat AI')
@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ route('today') }}" aria-label="Kembali">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">Customer Service</p>
        <h1>Chat AI TruSaba</h1>
    </div>
    <span class="badge badge-success">Online</span>
</div>

<div class="app-body" style="padding-bottom:calc(var(--nav-h) + 72px)">
    <div class="chat-thread" id="thread">
        <div class="bubble bubble-ai">
            Halo! Aku asisten TruSaba. Ada yang bisa dibantu untuk trip-mu?
            <span class="time">--:--</span>
        </div>
    </div>
</div>

<div class="chat-input-bar">
    <input class="input" id="chatInput" type="text" placeholder="Tulis pesan…" autocomplete="off" />
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
        if (!msg) return;
        addBubble(msg, 'bubble-user');
        input.value = '';
        sendBtn.disabled = true;

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
        })
        .catch(function() {
            addBubble('Maaf, ada gangguan. Coba lagi ya.', 'bubble-ai');
            sendBtn.disabled = false;
        });
    }

    sendBtn.addEventListener('click', send);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') send();
    });
})();
</script>
@endpush
@endsection
