<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laravel Chat App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Pusher & Laravel Echo -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body {
            background-color: #f1f1f1;
        }

        .chat-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            height: 80vh;
        }

        .user-list {
            width: 30%;
            border-right: 1px solid #ddd;
            overflow-y: auto;
        }

        .chat-box {
            width: 70%;
            display: flex;
            flex-direction: column;
        }

        .messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #e9ecef;
        }

        .message {
            margin-bottom: 10px;
            max-width: 26%;
            padding: 10px 15px;
            border-radius: 20px;
            clear: both;
        }

        .message.you {
            background-color: #007bff;
            color: white;
            margin-left: auto;
            text-align: right;
        }

        .message.other {
            background-color: #f1f1f1;
            color: #000;
            margin-right: auto;
            text-align: left;
        }

        .chat-footer {
            padding: 10px;
            border-top: 1px solid #ccc;
            background: #fff;
        }

        .user-item:hover {
            background-color: #f5f5f5;
        }
    </style>

    <script>
        window.userId = {{ auth()->id() }};
    </script>
</head>
<body>
<div class="container py-4">
    <div class="chat-container mx-auto">
        <!-- User List -->
        <div class="user-list p-3">
            <h5>💬 Chat Started With</h5>
            <ul class="list-group">
                @foreach ($users as $user)
                    <li class="list-group-item user-item" data-id="{{ $user->id }}" style="cursor:pointer;">
                        {{ $user->name }}
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Chat Box -->
        <div class="chat-box">
            <div class="p-3 border-bottom">
                <h5>Chat with <span id="chat-with">No one</span></h5>
            </div>
            <div id="messages" class="messages"></div>

            <div class="chat-footer">
                <form id="chat-form" class="d-flex">
                    @csrf
                    <input type="hidden" id="receiver_id">
                    <input type="text" id="message" class="form-control me-2" placeholder="Type a message">
                    <button class="btn btn-primary">Send</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    let selectedUserId = null;

    // Laravel Echo presence
    window.Echo.join('chat.presence')
        .here((users) => {})
        .joining((user) => {})
        .leaving((user) => {})
        .listen('ChatMessageSent', (e) => {
            if ((e.receiver_id === window.userId || e.sender_id === window.userId) &&
                (e.receiver_id === selectedUserId || e.sender_id === selectedUserId)) {

                const side = e.sender_id === window.userId ? 'you' : 'other';
                const name = e.sender_id === window.userId ? 'You' : e.sender_name;

                $('#messages').append(
                    `<div class="message ${side}"><strong>${name}:</strong> ${e.message}</div>`
                );
                scrollToBottom();
            }
        });

    // User click to open chat
    $('.user-item').click(function () {
        selectedUserId = $(this).data('id');
        $.get('/chat/messages/' + selectedUserId, function (res) {
            $('#chat-with').text(res.user.name);
            $('#receiver_id').val(selectedUserId);
            $('#messages').html('');

            res.messages.forEach(msg => {
                const side = msg.sender.id === window.userId ? 'you' : 'other';
                const name = msg.sender.id === window.userId ? 'You' : msg.sender.name;

                $('#messages').append(
                    `<div class="message ${side}"><strong>${name}:</strong> ${msg.message}</div>`
                );
            });

            scrollToBottom();
        });
    });

    // Message send
    $('#chat-form').submit(function (e) {
        e.preventDefault();
        const message = $('#message').val().trim();
        const receiver_id = $('#receiver_id').val();

        if (!message || !receiver_id) {
            alert('Select a user and enter a message');
            return;
        }

        $.ajax({
            url: '/chat/send',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                message,
                receiver_id
            },
            success: function () {
                $('#message').val('');
            },
            error: function (xhr) {
                alert('Message send failed');
                console.error(xhr.responseText);
            }
        });
    });

    function scrollToBottom() {
        const messages = document.getElementById('messages');
        messages.scrollTop = messages.scrollHeight;
    }
});
</script>
</body>
</html>
