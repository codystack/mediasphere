<?php
include "./components/header.php";
?>
    <div class="d-flex flex-column flex-lg-row h-lg-full bg-surface-secondary">
        <?php include "./components/side-nav.php"; ?>

        <div class="flex-lg-1 h-screen overflow-y-lg-auto">
            <?php include "./components/top-nav.php"; ?>

            <header>
                <div class="container-fluid">
                    <div class="pt-6">
                        <div class="row align-items-center">
                            <div class="col-sm col-12">
                                <h1 class="h2 ls-tight">BlinksChat</h1>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="py-6 bg-surface-secondary">
                <div class="container-fluid">
                    <div class="vstack gap-4">
                        <div class="row g-6">
                            <!-- LEFT: Staff List -->
                            <div class="col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="input-group input-group-sm input-group-inline">
                                            <span class="input-group-text pe-2"><i class="bi bi-search"></i></span>
                                            <input type="text" class="form-control" id="searchStaff" placeholder="Search staff">
                                        </div>
                                    </div>

                                    <div class="card-body py-0 scrollable-y mb-5 mt-3" style="max-height:400px;">
                                        <div id="staffList" class="vstack gap-3">
                                            <!-- Staff dynamically loaded -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT: Chat Area -->
                            <div class="col-xl-8">
                                <div class="card h-100 d-flex flex-column overflow-hidden" style="max-height:600px;">
                                    <!-- Header -->
                                    <div class="card-header d-flex align-items-center justify-content-between flex-shrink-0">
                                        <div class="d-flex align-items-center">
                                            <img id="chatUserAvatar" class="avatar rounded-circle me-3" src="./assets/img/chat.svg" alt="User">
                                            <div>
                                                <h6 id="chatUserName" class="mb-0">Select a user</h6>
                                                <small class="text-muted" id="chatUserStatus">—</small>
                                            </div>
                                        </div>
                                        <a href="#" class="px-2 text-muted"><i class="bi bi-three-dots-vertical"></i></a>
                                    </div>

                                    <!-- Scrollable message body -->
                                    <div id="chatMessages" class="card-body flex-grow-1 bg-light overflow-auto px-4 py-3" style="min-height: 0;">
                                        <div class="text-center text-muted mt-5">Select a staff member to start chatting.</div>
                                    </div>

                                    <!-- Input pinned to bottom -->
                                    <div class="card-footer bg-white flex-shrink-0">
                                        <form id="chatForm" class="d-flex align-items-center">
                                            <input type="hidden" id="receiver_id">
                                            <input type="text" id="messageInput" class="form-control border-0 shadow-none me-2" placeholder="Type a message...">
                                            <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="./assets/js/main.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    
    <script>
        $(document).ready(() => {
            const notyf = new Notyf();
            let currentReceiver = null;
            let messageInterval = null;
            let typingTimeout = null;

            // ------------------ STAFF LIST ------------------
            function loadStaff() {
                fetch('./auth/load_staff_auth.php')
                .then(res => res.json())
                .then(data => {
                    if (data.error) return notyf.error(data.error);

                    const staffList = $('#staffList');
                    staffList.empty();

                    if (!data.length) {
                        staffList.append('<p class="text-muted">No staff found.</p>');
                        return;
                    }

                    data.forEach(user => {
                        const statusClass = user.status === 'Online' ? 'text-success' : 'text-muted';
                        const unreadBadge = user.unread > 0 ? `<span class="badge bg-danger ms-auto">${user.unread}</span>` : '';

                        const item = `
                            <div class="d-flex align-items-center p-2 rounded hover-bg-light staff-item" 
                                data-id="${user.id}" data-name="${user.name}" data-avatar="${user.avatar}" style="cursor:pointer;">
                                <img src="${user.avatar}" class="avatar rounded-circle me-3" alt="${user.name}">
                                <div class="flex-1">
                                    <strong>${user.name}</strong><br>
                                    <small class="${statusClass}">${user.status}</small>
                                </div>
                                ${unreadBadge}
                            </div>
                        `;
                        staffList.append(item);
                    });
                })
                .catch(() => notyf.error('Failed to load staff list.'));
            }

            // ------------------ OPEN CHAT ------------------
            $(document).on('click', '.staff-item', function() {
                currentReceiver = $(this).data('id');
                const name = $(this).data('name');
                const avatar = $(this).data('avatar');

                $('#chatUserName').text(name);
                $('#chatUserAvatar').attr('src', avatar);
                $('#receiver_id').val(currentReceiver);
                $('#chatUserStatus').text(''); // reset typing indicator

                loadMessages();

                if (messageInterval) clearInterval(messageInterval);
                messageInterval = setInterval(loadMessages, 3000);
            });

            // ------------------ LOAD MESSAGES ------------------
            function loadMessages() {
                if (!currentReceiver) return;

                fetch(`./auth/load_messages_auth.php?receiver_id=${currentReceiver}`)
                .then(res => res.json())
                .then(messages => {
                    const chatBody = $('#chatMessages');
                    const isNearBottom = chatBody.scrollTop() + chatBody.innerHeight() >= chatBody[0].scrollHeight - 50;

                    chatBody.empty();

                    if (messages.error) return notyf.error(messages.error);
                    if (messages.length === 0) {
                        chatBody.html('<div class="text-center text-muted mt-5">No messages yet.</div>');
                        return;
                    }

                    messages.forEach(msg => {
                        const msgHTML = `
                            <div class="d-flex ${msg.is_sender ? 'justify-content-end' : 'justify-content-start'} mb-3 align-items-end">
                                <div class="${msg.is_sender ? 'text-end' : 'text-start'}" style="max-width:70%;">
                                    <div class="p-3 rounded ${msg.is_sender ? 'bg-soft-success text-dark' : 'bg-soft-danger text-dark'}">
                                        ${msg.parent_message ? `<p class="text-xs text-info mb-1">Reply to: "${msg.parent_message}"</p>` : ''}
                                        <p class="mb-0 text-sm">${msg.message}</p>
                                    </div>
                                    <div class="d-flex justify-content-${msg.is_sender ? 'end' : 'start'} mt-1" style="font-size:10px; color:#888;">
                                        <span class="me-1">${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                                        ${msg.is_sender && msg.status === 'read' ? '<i class="bi bi-check-all text-success"></i>' : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                        chatBody.append(msgHTML);
                    });

                    // Only scroll to bottom if user was already near bottom
                    if (isNearBottom) {
                        chatBody.scrollTop(chatBody.prop('scrollHeight'));
                    }
                })
                .catch(() => notyf.error('Failed to load messages.'));
            }


            // ------------------ SEND MESSAGE ------------------
            $('#chatForm').submit(function(e) {
                e.preventDefault();
                const message = $('#messageInput').val().trim();
                const receiver_id = $('#receiver_id').val();

                if (!message) return notyf.error('Message cannot be empty.');
                if (!receiver_id) return notyf.error('Please select a user.');

                fetch('./auth/send_message_auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ receiver_id, message })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        $('#messageInput').val('');
                        loadMessages();
                        notyf.success('Message sent.');
                    } else {
                        notyf.error(data.error || 'Failed to send message.');
                    }
                })
                .catch(() => notyf.error('Error sending message.'));
            });

            // ------------------ ENTER KEY SEND ------------------
            $('#messageInput').keydown(function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    $('#chatForm').submit();
                }

                // Emit "typing..." indicator
                if (currentReceiver) {
                    $('#chatUserStatus').text('Typing...');
                    clearTimeout(typingTimeout);
                    typingTimeout = setTimeout(() => $('#chatUserStatus').text(''), 1000);
                }
            });

            // ------------------ AUTO REFRESH ------------------
            setInterval(loadStaff, 10000); // refresh staff list every 10s
            setInterval(() => { if(currentReceiver) loadMessages(); }, 3000); // refresh chat every 3s

            // Initial load
            loadStaff();
        });
    </script>

</body>

</html>