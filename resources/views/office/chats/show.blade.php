<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Chat</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Chat</h1>
                    <p>{{ $chat->request->request_number ?? '' }} - {{ $chat->citizen->full_name ?? '' }}</p>
                </div>
            </header>

            <div class="back-row">
                <a href="{{ route('office.requests.show', $chat->request_id) }}" class="button secondary">Back</a>
            </div>

            @if(session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert error">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
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
                        data-url="{{ route('office.chats.messages.index', $chat->id) }}"
                        data-last-id="{{ $chat->messages->max('id') ?? 0 }}"
                    >
                        @forelse($chat->messages as $message)
                            <div class="message {{ $message->sender_user_id === Auth::id() ? 'mine' : '' }}" data-message-id="{{ $message->id }}">
                                <strong>{{ $message->sender->full_name ?? '' }}</strong>
                                <br>
                                {{ $message->message_text }}
                                @if($message->attachment_path)
                                    <br>
                                    <a href="{{ asset('storage/' . $message->attachment_path) }}" target="_blank">Attachment</a>
                                @endif
                                <br>
                                <small>{{ $message->sent_at }}</small>
                            </div>
                        @empty
                            <p class="muted" id="emptyChatMessage">No messages yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="panel">
                    <h2>Send Message</h2>
                    <form id="chatForm" method="POST" action="{{ route('office.chats.messages.store', $chat->id) }}" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <label>Message</label>
                            <textarea name="message_text" id="messageText"></textarea>
                        </div>

                        <div>
                            <label>Attachment</label>
                            <input type="file" name="attachment">
                        </div>

                        <div>
                            <button>Send Message</button>
                        </div>
                    </form>
                </div>
            </section>
        </main>

        <script>
            const chatBox = document.getElementById('chatMessages');
            const chatForm = document.getElementById('chatForm');
            const messageText = document.getElementById('messageText');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function renderMessage(message) {
                if (chatBox.querySelector(`[data-message-id="${message.id}"]`)) {
                    return;
                }

                const emptyMessage = document.getElementById('emptyChatMessage');
                if (emptyMessage) {
                    emptyMessage.remove();
                }

                const attachment = message.attachment_url
                    ? `<br><a href="${escapeHtml(message.attachment_url)}" target="_blank">Attachment</a>`
                    : '';

                const row = document.createElement('div');
                row.className = message.is_mine ? 'message mine' : 'message';
                row.dataset.messageId = message.id;
                row.innerHTML = `<strong>${escapeHtml(message.sender_name)}</strong><br>${escapeHtml(message.message_text)}${attachment}<br><small>${escapeHtml(message.sent_at)}</small>`;
                chatBox.appendChild(row);
                chatBox.dataset.lastId = message.id;
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            async function fetchMessages() {
                const url = `${chatBox.dataset.url}?after_id=${chatBox.dataset.lastId || 0}`;
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                data.messages.forEach(renderMessage);
                chatBox.dataset.lastId = data.last_id;
            }

            chatForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                const formData = new FormData(chatForm);
                const response = await fetch(chatForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                });

                if (response.ok) {
                    const data = await response.json();
                    renderMessage(data.message);
                    chatForm.reset();
                    messageText.focus();
                } else {
                    chatForm.submit();
                }
            });

            chatBox.scrollTop = chatBox.scrollHeight;
            setInterval(fetchMessages, 2500);
        </script>
    </body>
</html>
