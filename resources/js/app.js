import './bootstrap';
$(document).ready(function () {
    let selectedUserId = null;

    // Laravel Echo presence
    window.Echo.join('chat.presence')
        .here((users) => {
            // Optional: handle online users
        })
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
