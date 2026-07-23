@extends('layouts.app', ['navActive' => 'chat'])
@section('title', 'TruSaba · AI Chat')
@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ route('chat') }}" aria-label="Back">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">Travel Companion</p>
        <h1>TruSaba AI Chat</h1>
    </div>
    <span class="badge badge-success" id="statusBadge">Online</span>
</div>

<div class="app-body" style="padding-bottom:calc(var(--nav-h) + 72px)">
    <div class="chat-thread" id="thread">
        {{-- Populated by JS from /chat/history — shows greeting only if no history --}}
    </div>
</div>

<div class="chat-input-bar">
    <input class="input" id="chatInput" type="text" placeholder="Ask about your trip…" autocomplete="off" />
    <button type="button" class="chat-send" id="chatSend" aria-label="Send">
        <svg viewBox="0 0 24 24"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
    </button>
</div>

@push('styles')
<style>
    .bubble-ai p { margin: 0 0 6px 0; }
    .bubble-ai p:last-child { margin-bottom: 0; }
    .bubble-ai ul, .bubble-ai ol { margin: 4px 0 6px 0; padding-left: 18px; }
    .bubble-ai ul li, .bubble-ai ol li { margin-bottom: 3px; }
    .bubble-ai ul li:last-child, .bubble-ai ol li:last-child { margin-bottom: 0; }
    .bubble-ai strong { font-weight: 600; }
    .bubble-ai br + br { display: none; }
</style>
@endpush

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
    var IDLE_TIMEOUT = 10 * 60 * 1000; // 10 minutes
    var IDLE_PROMPT_GRACE = 30 * 1000;  // 30 seconds after prompt

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

            addBubble('Are you still there? If there\'s no response in 30 seconds, the chat session will end.', 'bubble-ai');

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
        addBubble('Chat session ended due to inactivity. Refresh the page to start a new session.', 'bubble-ai');
        statusBadge.textContent = 'Offline';
        statusBadge.className = 'badge badge-danger';
        sendBtn.disabled = true;
        input.disabled = true;
    }

    function addBubble(text, cls, html) {
        var now = new Date();
        var time = String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
        var div = document.createElement('div');
        div.className = 'bubble ' + cls;
        // Auto-detect HTML: beautified replies contain <p> or <ul> tags
        var isHtml = html || /<\/?(p|ul|ol|li|strong|em|br)\b/.test(text);
        var content = isHtml ? text : text.replace(/</g,'&lt;');
        div.innerHTML = content + '<span class="time">' + time + '</span>';
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
            addBubble(data.reply, 'bubble-ai', true);
            sendBtn.disabled = false;
            input.focus();
            resetIdleTimer();
        })
        .catch(function() {
            addBubble('Sorry, something went wrong. Please try again.', 'bubble-ai');
            sendBtn.disabled = false;
            resetIdleTimer();
        });
    }

    sendBtn.addEventListener('click', send);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') send();
    });

    // Load chat history on page load
    function loadHistory() {
        fetch('{{ route('chat.history') }}', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(function(m) {
                    var cls = m.role === 'user' ? 'bubble-user' : 'bubble-ai';
                    renderHistoryBubble(m.content, cls, m.time);
                });
            } else {
                // No history — show initial greeting
                renderHistoryBubble(
                    'Hello! I\'m your TruSaba travel assistant. Ask anything — travel recommendations, itinerary, booking, or travel tips \u{1F60A}',
                    'bubble-ai',
                    '--:--'
                );
            }
        })
        .catch(function() {
            renderHistoryBubble(
                'Hello! I\'m your TruSaba travel assistant. Ask anything — travel recommendations, itinerary, booking, or travel tips \u{1F60A}',
                'bubble-ai',
                '--:--'
            );
        });
    }

    function renderHistoryBubble(text, cls, time) {
        var div = document.createElement('div');
        div.className = 'bubble ' + cls;
        var isHtml = /<\/?(p|ul|ol|li|strong|em|br)\b/.test(text);
        var content = isHtml ? text : text.replace(/</g,'&lt;');
        div.innerHTML = content + '<span class="time">' + time + '</span>';
        thread.appendChild(div);
    }

    // Load history after DOM is ready, then start timers
    loadHistory();

    // Start idle timer after history loads
    resetIdleTimer();

    // Reset timer on any user interaction
    document.addEventListener('click', resetIdleTimer);
    document.addEventListener('keydown', resetIdleTimer);
})();
</script>
@endpush
@endsection
