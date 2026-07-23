@extends('layouts.app', ['navActive' => 'chat'])

@section('title', 'TruSaba · ' . $room->name)

@section('content')

<div class="app-header">
    <a class="icon-btn" href="{{ route('chat.rooms') }}" aria-label="Back">
        <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="title-block">
        <p class="eyebrow">{{ $canSend ? '💬' : '👁️' }} {{ $room->destination ?? '' }}</p>
        <h1>{{ $room->name }}</h1>
    </div>
    @if($canSend)
    <span class="badge badge-success">Joined</span>
    @endif
</div>

<div class="app-body">
    <div class="pad" style="padding-top:8px;padding-bottom:80px">

        {{-- Messages --}}
        <div id="thread" style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px">
            <div class="card" style="text-align:center;padding:20px">
                <p class="muted">No messages yet. Say hello!</p>
            </div>
        </div>

    </div>

    {{-- Input bar --}}
    <div class="chat-input-bar">
        @if($canSend)
        <input class="input" id="msgInput" type="text" placeholder="Type a message..." maxlength="1000" autocomplete="off" />
        <button type="button" class="chat-send" id="btnSend" aria-label="Send">
            <svg viewBox="0 0 24 24"><path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/></svg>
        </button>
        @else
        <p class="caption muted" style="text-align:center;flex:1">You need an itinerary for {{ $room->destination ?? 'this city' }} to send messages.</p>
        @endif
    </div>
</div>

@if($canSend)
@push('scripts')
<script>
var lastId = 0;
var roomId = {{ $room->id }};

function appendBubble(data) {
    var thread = document.getElementById('thread');
    var empty = thread.querySelector('.card');
    if (empty) empty.remove();

    var div = document.createElement('div');
    div.style.cssText = 'display:flex;flex-direction:' + (data.is_mine ? 'row-reverse' : 'row') + ';gap:8px;align-items:flex-start';

    // Avatar (clickable for DM on other users)
    var avatar = document.createElement('div');
    avatar.style.cssText = 'width:32px;height:32px;border-radius:50%;background:' + (data.is_mine ? 'var(--accent-hex)' : 'var(--muted)') + ';color:#fff;display:flex;align-items:center;justify-content:center;font-size:' + (data.user_initial.length > 2 ? '9px' : '12px') + ';font-weight:600;flex-shrink:0;text-align:center;cursor:' + (data.is_mine ? 'default' : 'pointer');
    avatar.textContent = data.user_initial;
    avatar.title = data.is_mine ? 'You' : 'Send DM to ' + data.user_name;
    if (!data.is_mine && data.user_id) {
        avatar.onclick = function() {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('chat.requests.store') }}';
            form.style.display = 'none';
            form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="to_user_id" value="' + data.user_id + '">';
            document.body.appendChild(form);
            form.submit();
        };
    }

    var bubble = document.createElement('div');
    bubble.style.cssText = 'max-width:75%;min-width:60px';
    var name = document.createElement('p');
    name.className = 'caption';
    name.style.cssText = 'margin-bottom:2px;text-align:' + (data.is_mine ? 'right' : 'left');
    name.textContent = (data.is_mine ? 'You' : data.user_name) + ' · ' + data.created_at;
    var txt = document.createElement('div');
    txt.className = 'card';
    txt.style.cssText = 'padding:8px 12px;font-size:14px;line-height:1.35;word-break:break-word;background:' + (data.is_mine ? 'var(--accent-hex)' : 'var(--surface)') + ';color:' + (data.is_mine ? '#fff' : 'inherit') + ';border-radius:10px';
    txt.textContent = data.content;
    bubble.appendChild(name);
    bubble.appendChild(txt);
    div.appendChild(avatar);
    div.appendChild(bubble);
    thread.appendChild(div);
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
}

function loadHistory(initial) {
    var url = '/chat/rooms/' + roomId + '/history';
    if (!initial && lastId > 0) url += '?after=' + lastId;
    fetch(url).then(r => r.json()).then(msgs => {
        var thread = document.getElementById('thread');
        if (!thread) return;
        if (initial) { thread.innerHTML = ''; lastId = 0; }
        if (!msgs.length) { if (initial) thread.innerHTML = '<div class="card" style="text-align:center;padding:20px"><p class="muted">No messages yet. Say hello!</p></div>'; return; }
        msgs.forEach(function(m) { if (m.id > lastId) lastId = m.id; appendBubble(m); });
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    });
}

function send() {
    var input = document.getElementById('msgInput');
    var content = input.value.trim();
    if (!content) return;
    input.value = ''; input.focus();

    var now = new Date();
    appendBubble({
        is_mine: true, user_id: {{ Auth::id() }},
        user_initial: '{{ strtoupper(mb_substr(explode(" ", trim(Auth::user()->name))[0], 0, 3)) }}',
        user_name: 'You', content: content,
        created_at: String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0')
    });

    fetch('/chat/rooms/' + roomId + '/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ content: content })
    }).then(r => r.json()).then(function(m) {
        if (m.id && m.id > lastId) lastId = m.id;
    });
}

document.getElementById('btnSend').addEventListener('click', send);
document.getElementById('msgInput').addEventListener('keydown', function(e) { if (e.key === 'Enter') send(); });
loadHistory(true);
setInterval(function() { loadHistory(false); }, 5000);
</script>
@endpush
@endif

@endsection
