<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>
<body>
    @if(auth()->user()?->isOfficeStaff())
        @include('office.navbar')
    @else
        @include('citizen.navbar')
    @endif

    <main class="main">
        <header class="topbar">
            <div>
                <h1>Chat</h1>
                <p>
                    {{ $chat->request->request_number ?? '' }}
                    @if($chat->office) &mdash; {{ $chat->office->name }} @endif
                    @if(auth()->user()->isOfficeStaff() && $chat->citizen)
                        &mdash; {{ $chat->citizen->full_name }}
                    @endif
                </p>
            </div>
        </header>

        <div class="back-row">
            <a href="{{ route('chat.index') }}" class="button secondary">Back to Chats</a>
        </div>

        @if(session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        <section class="grid two">
            <div class="panel">
                <div class="panel-head">
                    <h2>Messages</h2>
                    <span class="badge">{{ $chat->status }}</span>
                </div>

                <div
                    id="chatMessages"
                    class="chat-thread"
                    data-url="{{ route('chat.messages', $chat->id) }}"
                    data-last-id="{{ $chat->messages->max('id') ?? 0 }}"
                    data-chat-id="{{ $chat->id }}"
                >
                    @forelse($chat->messages as $message)
                        <div class="message {{ $message->sender_user_id === Auth::id() ? 'mine' : '' }}" data-message-id="{{ $message->id }}">
                            <strong>{{ $message->sender->full_name ?? '' }}</strong><br>
                            {{ $message->message_text }}
                            @if($message->attachment_path)
                                <br><a href="{{ asset('storage/' . $message->attachment_path) }}" target="_blank">Attachment</a>
                            @endif
                            <br><small>{{ $message->sent_at }}</small>
                        </div>
                    @empty
                        <p class="muted" id="emptyChatMessage">No messages yet. Say hello!</p>
                    @endforelse
                </div>
            </div>

            @if($chat->status === 'open')
            <div class="panel">
                <h2>Send Message</h2>
                <form id="chatForm" method="POST" action="{{ route('chat.messages.store', $chat->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <label>Message</label>
                        <textarea name="message_text" id="messageText" rows="4"></textarea>
                    </div>
                    <div>
                        <label>Attachment <small>(pdf, jpg, png, doc — max 5 MB)</small></label>
                        <input type="file" name="attachment">
                    </div>
                    <div>
                        <button type="submit">Send</button>
                    </div>
                </form>
            </div>
            @else
                <div class="panel">
                    <p class="muted">This chat is closed and no longer accepts messages.</p>
                </div>
            @endif
        </section>
    </main>

    @vite(['resources/js/app.js'])

    <script>
        const chatBox   = document.getElementById('chatMessages');
        const chatForm  = document.getElementById('chatForm');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const userId    = {{ Auth::id() }};
        const chatId    = parseInt(chatBox.dataset.chatId);

        function escapeHtml(v) {
            return String(v ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#039;');
        }

        function renderMessage(m) {
            if (chatBox.querySelector(`[data-message-id="${m.id}"]`)) return;
            document.getElementById('emptyChatMessage')?.remove();
            const attach = m.attachment_url ? `<br><a href="${escapeHtml(m.attachment_url)}" target="_blank">Attachment</a>` : '';
            const row = document.createElement('div');
            row.className = m.is_mine ? 'message mine' : 'message';
            row.dataset.messageId = m.id;
            row.innerHTML = `<strong>${escapeHtml(m.sender_name)}</strong><br>${escapeHtml(m.message_text ?? '')}${attach}<br><small>${escapeHtml(m.sent_at)}</small>`;
            chatBox.appendChild(row);
            chatBox.dataset.lastId = m.id;
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        if (window.Echo) {
            window.Echo.private(`chat.${chatId}`)
                .listen('.message.sent', (data) => {
                    if (data.sender_id !== userId) {
                        renderMessage({ ...data, is_mine: false });
                    }
                });
        } else {
            const poll = async () => {
                const res = await fetch(`${chatBox.dataset.url}?after_id=${chatBox.dataset.lastId || 0}`, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) return;
                const data = await res.json();
                data.messages.forEach(renderMessage);
                chatBox.dataset.lastId = data.last_id;
            };
            setInterval(poll, 3000);
        }

        if (chatForm) {
            chatForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                const formData = new FormData(chatForm);
                const res = await fetch(chatForm.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: formData,
                });
                if (res.ok) {
                    const data = await res.json();
                    renderMessage(data.message);
                    chatForm.reset();
                    document.getElementById('messageText')?.focus();
                } else {
                    chatForm.submit();
                }
            });
        }

        chatBox.scrollTop = chatBox.scrollHeight;
    </script>
</body>
</html>
