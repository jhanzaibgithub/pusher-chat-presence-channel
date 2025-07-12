import './bootstrap';

$(function () {
    let selectedUserId = null, onlineUsers = [];

    function updateOnlineUserList() {
        const $list = $('#online-users-list').empty();
        const users = onlineUsers.filter(u => u.id !== window.userId);
        if (!users.length) return $list.append('<li class="list-group-item text-muted">No other users online</li>');
        users.forEach(u => $list.append(`<li class="list-group-item user-item" data-id="${u.id}" style="cursor:pointer;">${u.name}</li>`));
    }

    function scrollToBottom() {
        const m = document.getElementById('messages');
        m.scrollTop = m.scrollHeight;
    }
function updateOnlineIndicators() {
    const onlineIds = onlineUsers.map(user => user.id);
    $('.online-badge').addClass('d-none'); 

    onlineIds.forEach(id => {
        const badge = $('#online-indicator-' + id);
        if (badge.length) {
            badge.removeClass('d-none'); 
        }
    });
}

    if (window.Echo) {
        window.Echo.join('chat.presence')
            .here(users => { onlineUsers = users; updateOnlineUserList();  updateOnlineIndicators(); })
            .joining(user => { onlineUsers.push(user); updateOnlineUserList(); updateOnlineIndicators();})
            .leaving(user => { onlineUsers = onlineUsers.filter(u => u.id !== user.id); updateOnlineUserList(); updateOnlineIndicators();})
            .listen('ChatMessageSent', e => {
                if ([e.receiver_id, e.sender_id].includes(window.userId) && [e.receiver_id, e.sender_id].includes(selectedUserId)) {
                    const side = e.sender_id === window.userId ? 'you' : 'other';
                    const name = e.sender_id === window.userId ? 'You' : e.sender_name;
                    $('#messages').append(`<div class="message ${side}"><strong>${name}:</strong> ${e.message}</div>`);
                    scrollToBottom();
                }
            });
    } else {
        console.warn('Echo is not loaded.');
    }

    $(document).on('click', '.user-item', function () {
        selectedUserId = $(this).data('id');
        $.get('/chat/messages/' + selectedUserId, res => {
            $('#chat-with').text(res.user.name);
            $('#receiver_id').val(selectedUserId);
            $('#messages').html('');
            res.messages.forEach(msg => {
                const side = msg.sender.id === window.userId ? 'you' : 'other';
                const name = msg.sender.id === window.userId ? 'You' : msg.sender.name;
                $('#messages').append(`<div class="message ${side}"><strong>${name}:</strong> ${msg.message}</div>`);
            });
            scrollToBottom();
        });
    });

    $('#chat-form').submit(function (e) {
        e.preventDefault();
        const message = $('#message').val().trim(), receiver_id = $('#receiver_id').val();
        if (!message || !receiver_id) return alert('Select a user and enter a message');
        $.post('/chat/send', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            message, receiver_id
        }).done(() => $('#message').val(''))
          .fail(xhr => { alert('Message send failed'); console.error(xhr.responseText); });
    });
});
