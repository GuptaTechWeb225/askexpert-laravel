
export function adminExpertChatComponent() {
    return {
        newMessage: '',
        selectedFile: null,
        typing: false,
        typingTimer: null,
        selectedExpertId: null,

        init() {
            this.scrollToBottom();
        },

        openChat(expertId, expertName, expertAvatar) {
            this.selectedExpertId = expertId;

            // Header update
            this.$refs.expertName.textContent = expertName;
            this.$refs.expertAvatar.src = expertAvatar;

            // Show chat window
            this.$refs.placeholder.style.display = 'none';
            this.$refs.chatWindow.classList.remove('d-none');

            // Load messages and join channel
            this.loadMessages(expertId);
            this.joinChannel(expertId);
        },

        loadMessages(expertId) {
            axios.get(`/admin/expert-chat/messages/${expertId}`)
                .then(response => {
                    this.$refs.chatBody.innerHTML = '';
                    response.data.messages.forEach(msg => this.appendMessage(msg));
                    this.scrollToBottom();
                })
                .catch(err => {
                    console.error('Failed to load messages:', err);
                });
        },

        joinChannel(expertId) {
            window.Echo.private(`admin-expert-chat.expert.${expertId}`)
                .listen('AdminExpertMessageSent', (e) => {
                    if (e.message.sender_type === 'expert') {
                        this.appendMessage(e.message);
                    }
                })
                .listenForWhisper('typing', (e) => {
                    if (e.role === 'expert') {
                        this.typing = true;
                        clearTimeout(this.typingTimer);
                        this.typingTimer = setTimeout(() => this.typing = false, 1500);
                    }
                });
        },

        appendMessage(msg) {
            // Prevent duplicate
            if (msg.id && this.$refs.chatBody.querySelector(`[data-msg-id="${msg.id}"]`)) return;

            const div = document.createElement('div');
            if (msg.id) div.setAttribute('data-msg-id', msg.id);

            const isMine = msg.sender_type === 'admin';
            div.className = `message-container ${isMine ? 'user-side' : ''}`;

            let content = '';
            if (msg.image_path) {
                content = `<img src="/storage/${msg.image_path}" style="max-width:250px; border-radius:10px; margin:5px 0;">`;
            } else {
                content = msg.message ? msg.message.replace(/\n/g, '<br>') : '';
            }

            let ticks = '';
            if (isMine) {
                ticks = msg.is_read 
                    ? '<span class="read-ticks" style="color:#34b7f1;">✓✓</span>'
                    : '<span class="read-ticks" style="color:#aaa;">✓</span>';
            }

            div.innerHTML = `
                ${!isMine ? `<img src="${msg.expert_avatar || '/default-expert.jpg'}" class="message-avatar rounded-circle" width="40">` : ''}
                <div class="message-bubble ${isMine ? 'user' : 'bot'}">
                    ${content}
                    <div class="message-meta">
                        <span>${new Date(msg.sent_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</span>
                        ${ticks}
                    </div>
                </div>
                ${isMine ? `<img src="${window.ADMIN_AVATAR || '/default-admin.jpg'}" class="message-avatar rounded-circle" width="40">` : ''}
            `;

            this.$refs.chatBody.appendChild(div);
            this.scrollToBottom();
        },

        scrollToBottom() {
            const body = this.$refs.chatBody;
            if (body) body.scrollTop = body.scrollHeight;
        },

        sendMessage() {
            const message = this.newMessage.trim();
            if (!message && !this.selectedFile) return;
            if (!this.selectedExpertId) return;

            const formData = new FormData();
            formData.append('expert_id', this.selectedExpertId);
            if (message) formData.append('message', message);
            if (this.selectedFile) formData.append('image', this.selectedFile);

            axios.post('/admin/expert-chat/send', formData)
                .then(response => {
                    this.newMessage = '';
                    this.selectedFile = null;
                    if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                    this.appendMessage(response.data.message);
                })
                .catch(err => {
                    console.error('Send failed:', err);
                    alert('Message send failed!');
                });

            // Send typing stop
            if (message) {
                window.Echo.private(`admin-expert-chat.expert.${this.selectedExpertId}`)
                    .whisper('typing', { role: 'admin' });
            }
        },

        typingEvent() {
            if (this.newMessage.trim()) {
                window.Echo.private(`admin-expert-chat.expert.${this.selectedExpertId}`)
                    .whisper('typing', { role: 'admin' });
            }
        },

        handleFileUpload(e) {
            this.selectedFile = e.target.files[0];
            if (this.selectedFile) {
                this.sendMessage();
            }
        }
    }
}

// ==================== EXPERT SIDE COMPONENT ====================
export function expertAdminChatComponent() {
    return {
        newMessage: '',
        selectedFile: null,
        typing: false,
        typingTimer: null,

        init() {
            this.scrollToBottom();
            this.loadMessages();

const expertId = window.EXPERT_ID;

            window.Echo.private(`admin-expert-chat.expert.${expertId}`)
                .listen('AdminExpertMessageSent', (e) => {
                    if (e.message.sender_type === 'admin') {
                        this.appendMessage(e.message);
                    }
                })
                .listenForWhisper('typing', (e) => {
                    if (e.role === 'admin') {
                        this.typing = true;
                        clearTimeout(this.typingTimer);
                        this.typingTimer = setTimeout(() => this.typing = false, 1500);
                    }
                });
        },

        loadMessages() {
            axios.get('/expert/messages/all')
                .then(res => {
                    const messagesDiv = document.getElementById('messages');
                    messagesDiv.innerHTML = '';
                    res.data.messages.forEach(msg => this.appendMessage(msg));
                    this.scrollToBottom();
                })
                .catch(err => console.error('Load failed:', err));
        },

        appendMessage(msg) {
            if (msg.id && document.querySelector(`[data-msg-id="${msg.id}"]`)) return;

            const div = document.createElement('div');
            if (msg.id) div.setAttribute('data-msg-id', msg.id);

            const isMine = msg.sender_type === 'expert';
            div.className = `message-container ${isMine ? 'user-side' : ''}`;

            let content = '';
            if (msg.image_path) {
                content = `<img src="/storage/${msg.image_path}" style="max-width:250px; border-radius:10px; margin:5px 0;">`;
            } else {
                content = msg.message ? msg.message.replace(/\n/g, '<br>') : '';
            }

            let ticks = '';
            if (isMine) {
                ticks = msg.is_read 
                    ? '<span class="read-ticks" style="color:#34b7f1;">✓✓</span>'
                    : '<span class="read-ticks" style="color:#aaa;">✓</span>';
            }

            div.innerHTML = `
                ${!isMine ? '<img src="/admin-avatar.jpg" class="message-avatar rounded-circle" width="40">' : ''}
                <div class="message-bubble ${isMine ? 'user' : 'bot'}">
                    ${content}
                    <div class="message-meta">
                        <span>${new Date(msg.sent_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</span>
                        ${ticks}
                    </div>
                </div>
                ${isMine ? '<img src="/expert-avatar.jpg" class="message-avatar rounded-circle" width="40">' : ''}
            `;

            document.getElementById('messages').appendChild(div);
            this.scrollToBottom();
        },

        scrollToBottom() {
            const body = document.getElementById('messages');
            if (body) body.scrollTop = body.scrollHeight;
        },

        sendMessage() {
            const message = this.newMessage.trim();
            if (!message && !this.selectedFile) return;

            const formData = new FormData();
            if (message) formData.append('message', message);
            if (this.selectedFile) formData.append('image', this.selectedFile);

            axios.post('/expert/massages/admin-chat/send', formData)
                .then(res => {
                    this.newMessage = '';
                    this.selectedFile = null;
                    if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                    this.appendMessage(res.data.message);
                })
                .catch(err => console.error('Send failed:', err));
        },

        typingEvent() {
            if (this.newMessage.trim()) {
const expertId = window.EXPERT_ID;
                window.Echo.private(`admin-expert-chat.expert.${expertId}`)
                    .whisper('typing', { role: 'expert' });
            }
        },

        handleFileUpload(e) {
            this.selectedFile = e.target.files[0];
            if (this.selectedFile) this.sendMessage();
        }
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.data('adminExpertChatComponent', adminExpertChatComponent);
    Alpine.data('expertAdminChatComponent', expertAdminChatComponent);
});
