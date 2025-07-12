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

    <!-- Laravel Echo + Pusher -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <script>
        window.userId = {{ auth()->id() }};
    </script>
</head>
<body>
    <div class="container py-4">
        <div class="row">
            <h1>{{ auth()->user()->name }}</h1>

            <!-- User List -->
            <div class="col-md-4">
                <h5>all Users</h5>
                <ul class="list-group mb-3" id="online-users">
                    <!-- Online users from Echo will be shown here -->
                </ul>
                <ul class="list-group">
                    @foreach ($users as $user)
                        <a href="{{ url('/chat/' . $user->id) }}" class="list-group-item">
                            {{ $user->name }}
                        </a>
                    @endforeach
                </ul>
            </div>

            <!-- Chat Messages -->
            <div class="col-md-8">
                <h5>Chat with {{ $otherUser->name }}</h5>
                <ul id="messages" class="list-group mb-3">
                    @foreach ($messages as $msg)
                        <li class="list-group-item">
                            <strong>{{ $msg->sender->id === auth()->id() ? 'You' : $msg->sender->name }}:</strong>
                            {{ $msg->message }}
                        </li>
                    @endforeach
                </ul>

                <form id="chat-form">
                    @csrf
                    <input type="hidden" id="receiver_id" value="{{ $otherUser->id }}">
                    <input type="text" id="message" class="form-control" placeholder="Type a message">
                    <button class="btn btn-primary mt-2">Send</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Echo presence channel
            window.Echo.join('chat.presence')
                .here((users) => {
                    $('#online-users').html('');
                    users.forEach(user => {
                        $('#online-users').append(`<li class="list-group-item">${user.name}</li>`);
                    });
                })
                .joining((user) => {
                    $('#online-users').append(`<li class="list-group-item">${user.name}</li>`);
                })
                .leaving((user) => {
                    $(`#online-users li:contains(${user.name})`).remove();
                })
                .listen('ChatMessageSent', (e) => {
                    if (e.receiver_id === window.userId || e.sender_id === window.userId) {
                        $('#messages').append(
                            `<li class="list-group-item"><strong>${e.sender_name}:</strong> ${e.message}</li>`
                        );
                    }
                });

            // AJAX message send
            $('#chat-form').submit(function (e) {
                e.preventDefault();

                const message = $('#message').val();
                const receiver_id = $('#receiver_id').val();

                if (!message.trim()) return;

                $.ajax({
                    url: '{{ url("/chat/send") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        message: message,
                        receiver_id: receiver_id
                    },
                   
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        alert('Message sending failed!');
                    }
                });
            });
        });
    </script>
</body>
</html>
