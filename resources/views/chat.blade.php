<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pusher Chat App</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script>
        window.userId = {{ auth()->id() }};
    </script>
</head>
<body>
<div class="container py-4">

<h1>Welcome back {{ auth()->user()->name }}</h1>
<!-- Check Online Users Button -->
<div class="text-end">
    <button class="btn btn-outline-success mb-3" data-bs-toggle="modal" data-bs-target="#onlineUsersModal">
        👥 Check Online Users
    </button>
</div>
<!-- Online Users Modal -->
<div class="modal fade" id="onlineUsersModal" tabindex="-1" aria-labelledby="onlineUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Online Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group" id="online-users-list">
                    <!-- Dynamic user list will be appended here -->
                </ul>
            </div>
        </div>
    </div>
</div>

    <div class="chat-container mx-auto mt-3">
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
</body>
</html>
