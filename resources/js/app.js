import './bootstrap';
$(document).ready(function () {
    let selectedUserId = null;

    // Laravel Echo presence
   let onlineUsers = [];

window.Echo.join('chat.presence')
    .here((users) => {
        onlineUsers = users;
        updateOnlineUserList();
    })
    .joining((user) => {
        onlineUsers.push(user);
        updateOnlineUserList();
    })
    .leaving((user) => {
        onlineUsers = onlineUsers.filter(u => u.id !== user.id);
        updateOnlineUserList();
    })
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

// Helper to update modal with online users
function updateOnlineUserList() {
    const $list = $('#online-users-list');
    $list.empty();

    // Filter out the current user
    const filteredUsers = onlineUsers.filter(user => user.id !== window.userId);

    if (filteredUsers.length === 0) {
        $list.append('<li class="list-group-item text-muted">No other users online</li>');
        return;
    }

    filteredUsers.forEach(user => {
        $list.append(`<li class="list-group-item">${user.name}</li>`);
    });
}


    // Click on user to start chat
    $(document).on('click', '.user-item', function () {
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

    // Submit form to send message
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
                _token: $('meta[name="csrf-token"]').attr('content'),
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
