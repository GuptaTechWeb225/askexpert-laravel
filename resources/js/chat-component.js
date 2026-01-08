let twilioRoom = null;

export function chatComponent(chatId) {
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    const userId = userIdMeta ? parseInt(userIdMeta.getAttribute('content')) : null;
    return {
        newMessage: '',
        selectedFile: null,
        typing: false,
        expertOnline: false,
        typingTimer: null,
        localTrack: null,
        isVideo: false,
        inCall: false,
        callState: 'idle',
        videoEnabled: true,
        isMuted: false,

        async initiateCall(withVideo) {
            if (this.inCall) {
                console.log('Already in call, ignoring');
                return;
            }

            this.isVideo = withVideo;
            this.inCall = true;
            this.callState = 'ringing';

            console.log('User initiating call', { withVideo, chatId });

            const modalEl = document.getElementById('callModal');
            this.callBootstrapModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            this.callBootstrapModal.show();

            this.playRingtone();

            console.log('Sending incoming-call whisper to expert');
            window.Echo.private(`chat.${chatId}`).whisper('incoming-call', {
                from: 'user',
                type: withVideo ? 'video' : 'voice'
            });
        },
        playRingtone() {
            const ringtone = document.getElementById('ringtone');
            if (ringtone) {
                ringtone.currentTime = 0;
                ringtone.play().catch(e => console.log('Autoplay blocked'));
            }
        },

        stopRingtone() {
            const ringtone = document.getElementById('ringtone');
            if (ringtone) {
                ringtone.pause();
                ringtone.currentTime = 0;
            }
        },
        setupCallUI(room) {
            console.log('Setting up Call UI...');

            // Existing participants
            room.participants.forEach(participant => {
                this.attachParticipant(participant);
            });

            // New join
            room.on('participantConnected', participant => {
                this.attachParticipant(participant);
            });

            room.on('participantDisconnected', participant => {
                participant.tracks.forEach(pub => {
                    if (pub.track) {
                        pub.track.detach().forEach(el => el.remove());
                    }
                });
            });

            room.on('disconnected', () => {
                this.endCall();
            });
        },

        cancelCall() {
            this.stopRingtone(); // 👈 Fix: Function call bahar nikala
            window.Echo.private(`chat.${chatId}`).whisper('call-cancelled', { chatId });
            this.endCall();
        },

        endCall() {
            if (twilioRoom) {
                twilioRoom.disconnect();
                twilioRoom = null;
            }

            this.callState = 'idle';
            this.inCall = false;

            document.getElementById('local-media').innerHTML = '';
            document.getElementById('remote-media').innerHTML = '';

            const modal = bootstrap.Modal.getInstance(
                document.getElementById('callModal')
            );
            if (modal) modal.hide();
        },


        toggleMute() {
            this.isMuted = !this.isMuted;

            if (!twilioRoom) return;

            twilioRoom.localParticipant.audioTracks.forEach(pub => {
                if (pub.track) pub.track.enable(!this.isMuted);
            });
        },
        toggleVideo() {
            this.videoEnabled = !this.videoEnabled;

            if (!twilioRoom) return;

            twilioRoom.localParticipant.videoTracks.forEach(pub => {
                if (pub.track) pub.track.enable(this.videoEnabled);
            });
        }
        ,
        hangUp() {
            this.endCall();
            const stopRingtone = () => {
                const ringtone = document.getElementById('ringtone');
                if (ringtone) { ringtone.pause(); ringtone.currentTime = 0; }
            };

            window.Echo.private(`chat.${chatId}`).whisper('call-ended', { chatId });
        },

        init() {
            this.scrollToBottom();
            this.markAllAsRead();
            window.Echo.private(`chat.${chatId}`)
                .listen('ChatMessageSent', (e) => {
                    if (e.message.sender_type !== 'user') {
                        this.appendMessage(e.message);
                        this.markAsRead(e.message.id);
                    }
                })

                .listenForWhisper('call-accepted', async () => {
                    if (twilioRoom) return;

                    console.log('User joining room after accept');

                    this.stopRingtone();
                    this.callState = 'connected';

                    try {
                        const res = await axios.post(`/chat/${chatId}/generate-token`);
                        const room = await Twilio.Video.connect(res.data.token, {
                            name: 'chat_room_' + chatId,
                            audio: true,
                            video: this.isVideo ? { width: 640 } : false
                        });

                        twilioRoom = room;
                        this.setupCallUI(room);

                        document.getElementById('video-wrapper').classList.remove('d-none');
                        document.getElementById('call-status').textContent = 'Connected';

                    } catch (err) {
                        console.error('User join failed:', err);
                        alert('Call connection failed.');
                        this.endCall();
                    }
                })

                .listenForWhisper('call-rejected', () => {
                    this.stopRingtone();
                    this.endCall();
                    toastr.info('Call rejected');
                })

                .listenForWhisper('call-cancelled', () => {
                    this.stopRingtone();
                    this.endCall();
                    toastr.info('Call cancelled');
                })
                .listen('MessageRead', (e) => {
                    this.updateTicksToRead(e.messageId);
                })
                .listenForWhisper('typing', (e) => {
                    if (e.role === 'expert') {
                        this.typing = true;
                        clearTimeout(this.typingTimer);
                        this.typingTimer = setTimeout(() => this.typing = false, 1500);
                    }
                });

            // Polling for expert online
            this.checkExpertOnline();
            setInterval(() => this.checkExpertOnline(), 10000);
        },

        checkExpertOnline() {
            axios.get(`/chat/${chatId}/experts-online`)
                .then(res => this.expertOnline = res.data.expertOnline)
                .catch(() => this.expertOnline = false);
        },

        appendMessage(msg) {
            if (msg.id && document.querySelector(`[data-message-id="${msg.id}"]`)) return;

            const messagesDiv = document.getElementById('messages');
            const div = document.createElement('div');
            if (msg.id) div.setAttribute('data-message-id', msg.id);

            div.className = `message-container ${msg.sender_type === 'user' ? 'user-side' : ''}`;

            const isImage = msg.message && (msg.message.endsWith('.jpg') || msg.message.endsWith('.png') || msg.message.endsWith('.jpeg') || msg.message.startsWith('chat-images/'));
            let messageContent = msg.message ? msg.message.replace(/\n/g, '<br>') : '';

            if (isImage) {
                messageContent = `<img src="/storage/${msg.message}" class="chat-img-preview" style="max-width:200px; border-radius:10px;">`;
            }
            let ticks = '';
            if (msg.sender_type === 'user') {
                ticks = msg.is_read ? '<span class="read-ticks" style="color: #34b7f1; margin-left: 4px;">✓✓</span>' : '<span class="read-ticks" style="margin-left: 4px;">✓</span>';
            }

            let html = `
                ${msg.sender_type !== 'user' ? '<img src="/assets/front-end/img/placeholder/user.png" class="message-avatar">' : ''}
                <div class="message-bubble ${msg.sender_type === 'user' ? 'user' : 'bot'}">
                    ${messageContent}
                    <div class="message-meta">
                        <span>${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                        ${ticks}
                    </div>
                </div>
                ${msg.sender_type === 'user' ? `<img src="${window.AUTH_USER_AVATAR}" class="message-avatar">` : ''}
            `;

            div.innerHTML = html;
            messagesDiv.appendChild(div);
            this.scrollToBottom();
        },
        scrollToBottom() {
            const body = document.querySelector('.chat-body');
            if (body) body.scrollTop = body.scrollHeight;
        },
        handleFileUpload(event) {
            this.selectedFile = event.target.files[0];
            if (this.selectedFile) {
                this.sendMessage();
            }
        },
        markAllAsRead() {
            axios.post('/chat/mark-read', { chat_id: chatId });
        },

        markAsRead(messageId) {
            axios.post('/chat/mark-specific-read', { message_id: messageId });
        },
        updateTicksToRead(messageId) {
            if (messageId) {
                const messageRow = document.querySelector(`[data-message-id="${messageId}"]`);
                if (messageRow) {
                    const tickSpan = messageRow.querySelector('.read-ticks');
                    if (tickSpan) {
                        tickSpan.innerText = '✓✓';
                        tickSpan.style.color = '#34b7f1';
                    }
                }
            } else {
                document.querySelectorAll('.read-ticks').forEach(tick => {
                    tick.innerText = '✓✓';
                    tick.style.color = '#34b7f1';
                });
            }
        },
        sendMessage() {
            const message = this.newMessage.trim();
            if (!message && !this.selectedFile) return;

            // Use FormData for files
            let formData = new FormData();
            formData.append('chat_id', chatId);
            if (message) formData.append('message', message);
            if (this.selectedFile) formData.append('image', this.selectedFile);

            axios.post('/chat/send-message', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
                .then((res) => {
                    this.newMessage = '';
                    this.selectedFile = null;
                    document.getElementById('imageInput').value = ''; // Reset input
                    this.appendMessage(res.data.message_data);
                })
                .catch(err => console.error(err));
        },

        typingEvent() {
            window.Echo.private(`chat.${chatId}`).whisper('typing', { role: 'user' });
        },

        markAsRead(messageId) {
            axios.post('/chat/mark-read', { message_id: messageId });
        }


    }
}

export function expertChatComponent(chatId) {

    console.log('Expert Chat ID:', chatId, typeof chatId);

    return {
        newMessage: '',
        selectedFile: null,
        customerTyping: false,
        customerOnline: false,
        typingTimer: null,
        chatEnded: false,
        isVideo: false,
        isIncoming: false, // Call aa rahi hai?
        isMuted: false,
        cameraOff: false,
        callerInfo: null,
        callStatusText: '',
        callState: '', // 'incoming', 'ringing', 'connected'
        videoEnabled: false,


        async initiateCall(withVideo) {
            if (this.activeRoom) {
                alert('Already in a call!');
                return;
            }

            this.isVideo = withVideo;

            try {
                const response = await axios.post(`/chat/${chatId}/generate-token`);
                const token = response.data.token;

                const room = await Twilio.Video.connect(token, {
                    name: 'chat_room_' + chatId,
                    audio: true,
                    video: withVideo ? { width: 640 } : false
                });

                this.activeRoom = room;

                // Helper function to attach track if subscribed
                const attachTrack = (publication, containerId) => {
                    if (publication && publication.track) {
                        const element = publication.track.attach();
                        const container = document.getElementById(containerId);
                        if (container) {
                            container.appendChild(element);
                        }
                    }
                };

                // Local tracks attach
                room.localParticipant.videoTracks.forEach(publication => {
                    attachTrack(publication, 'local-media');
                });
                // Audio local no visual needed

                // Handle existing remote participants
                const handleParticipant = (participant) => {
                    participant.videoTracks.forEach(publication => {
                        if (publication.isSubscribed) {
                            attachTrack(publication, 'remote-media');
                        } else {
                            publication.on('subscribed', track => {
                                attachTrack({ track }, 'remote-media');
                            });
                        }
                    });

                    participant.on('trackSubscribed', track => {
                        if (track.kind === 'video') {
                            const element = track.attach();
                            document.getElementById('remote-media').appendChild(element);
                        }
                    });
                };

                room.participants.forEach(handleParticipant);
                room.on('participantConnected', handleParticipant);

                // Cleanup on disconnect
                room.on('participantDisconnected', (participant) => {
                    participant.tracks.forEach(publication => {
                        if (publication.track) {
                            publication.track.detach().forEach(el => el.remove());
                        }
                    });
                });

                // Show modal instead of inline container
                document.getElementById('call-modal').classList.remove('hidden');

            } catch (error) {
                console.error('Call failed:', error);
                alert('Call failed: ' + (error.message || 'Permission/Connection issue'));
            }
        },

        participantConnected(participant) {
            participant.tracks.forEach(publication => {
                if (publication.isSubscribed && publication.track) {
                    document.getElementById('remote-media').appendChild(publication.track.attach());
                }
            });

            participant.on('trackSubscribed', track => {
                document.getElementById('remote-media').appendChild(track.attach());
            });
        },

        hangUp() {
            window.Echo.private(`chat.${chatId}`)
                .whisper('call-ended', { chatId });

            this.resetCallUI();
        },
        init() {
            this.scrollToBottom();
            this.markAllAsRead();

            // Listen for user messages
            window.Echo.private(`chat.${chatId}`)
                .listen('ChatMessageSent', (e) => {
                    if (e.message.sender_type === 'user') {
                        this.appendMessage(e.message);
                        this.markAsRead(e.message.id);

                    }
                })
                .listen('MessageRead', (e) => {
                    this.updateTicksToRead(e.messageId);
                })
                .listenForWhisper('typing', (e) => {
                    if (e.role === 'user') {
                        this.customerTyping = true;
                        clearTimeout(this.typingTimer);
                        this.typingTimer = setTimeout(() => this.customerTyping = false, 2000);
                    }
                })
                .listenForWhisper('incoming-call', (data) => {
                    console.log('Incoming call data:', data);

                    this.isIncoming = true;
                    this.isVideo = data.type === 'video';
                    this.callState = 'incoming';

                    this.callStatusText = 'Incoming ' + (this.isVideo ? 'Video' : 'Voice') + ' Call';

                    this.callerInfo = {
                        avatar: data.avatar ?? '/assets/back-end/img/placeholder/user.png',
                        name: data.name ?? 'Customer'
                    };

                    // ✅ Bootstrap 4 modal show
                    $('#callModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });

                    $('#callModal').modal('show');
                })
                .listenForWhisper('call-accepted', async () => {
                    if (this.activeRoom) return;

                    const res = await axios.post(`/chat/${chatId}/generate-token`);
                    const token = res.data.token;

                    twilioRoom = await Twilio.Video.connect(token, {
                        name: 'chat_room_' + chatId,
                        audio: true,
                        video: this.isVideo ? { width: 640 } : false
                    });

                    this.setupCallUI(twilioRoom);

                });

            if ('{{ $chat->status }}' === 'ended') {
                this.chatEnded = true;
                this.hideFooterAndButton();
                this.showEndedMessage();
            }
        },

        sendMessage() {
            if (!this.newMessage.trim() && !this.selectedFile) return;

            let formData = new FormData();
            formData.append('chat_id', chatId);
            if (this.newMessage) formData.append('message', this.newMessage);
            if (this.selectedFile) formData.append('image', this.selectedFile);

            axios.post('/expert/chat/send-message', formData).then(res => {
                this.newMessage = '';
                this.selectedFile = null;
                this.appendMessage(res.data.message_data);
            });
        },

        acceptCall() {
            console.log('Expert accepting call');

            this.callState = 'connected';

            // Notify user
            window.Echo.private(`chat.${chatId}`).whisper('call-accepted');

            // Join room
            axios.post(`/chat/${chatId}/generate-token`)
                .then(async res => {
                    console.log('Expert token received');

                    try {
                        const room = await Twilio.Video.connect(res.data.token, {
                            name: 'chat_room_' + chatId,
                            audio: true,
                            video: this.isVideo ? { width: 640 } : false
                        });

                        twilioRoom = room;
                        console.log('Expert joined room successfully');

                        this.setupCallUI(room);

                        // Show connected UI
                        document.getElementById('video-wrapper').classList.remove('d-none');
                        document.getElementById('call-status').textContent = 'Connected';

                    } catch (err) {
                        console.error('Twilio connect failed:', err);
                        alert('Unable to connect. Please check camera/mic permissions.');
                        this.endCall();
                    }
                })
                .catch(err => {
                    console.error('Token fetch failed:', err);
                    alert('Failed to get connection token.');
                });
        },
        setupCallUI(room) {
            console.log('Setting up Call UI...');

            // Existing participants
            room.participants.forEach(participant => {
                this.attachParticipant(participant);
            });

            // New join
            room.on('participantConnected', participant => {
                this.attachParticipant(participant);
            });

            room.on('participantDisconnected', participant => {
                participant.tracks.forEach(pub => {
                    if (pub.track) {
                        pub.track.detach().forEach(el => el.remove());
                    }
                });
            });

            room.on('disconnected', () => {
                this.endCall();
            });
        }
        ,
        resetCallUI() {
            if (this.activeRoom) {
                this.activeRoom.disconnect();
                this.activeRoom = null;
            }

            this.callState = '';
            this.isIncoming = false;
            this.isVideo = false;

            $('#callModal').modal('hide');

            document.getElementById('local-media').innerHTML = '';
            document.getElementById('remote-media').innerHTML = '';
        },

        rejectCall() {
            window.Echo.private(`chat.${chatId}`)
                .whisper('call-rejected', { chatId });

            this.resetCallUI();
        },
        toggleMute() {
            this.isMuted = !this.isMuted;

            if (this.activeRoom) {
                this.activeRoom.localParticipant.audioTracks.forEach(pub => {
                    if (pub.track) pub.track.enable(!this.isMuted);
                });
            }
        },
        toggleVideo() {
            this.videoEnabled = !this.videoEnabled;

            if (this.activeRoom) {
                this.activeRoom.localParticipant.videoTracks.forEach(pub => {
                    if (pub.track) pub.track.enable(this.videoEnabled);
                });
            }
        },
        typingEvent() {
            window.Echo.private(`chat.${chatId}`).whisper('typing', { role: 'expert' });
        },

        appendMessage(msg) {
            if (msg.id && document.querySelector(`[data-message-id="${msg.id}"]`)) return;

            const messagesDiv = document.getElementById('messages');
            const div = document.createElement('div');
            if (msg.id) div.setAttribute('data-message-id', msg.id);

            div.className = `message-container ${msg.sender_type === 'expert' ? 'user-side' : ''}`;

            let content = msg.message;
            if (msg.message && msg.message.startsWith('chat-images/')) {
                content = `<img src="/storage/${msg.message}" style="max-width:200px; border-radius:10px;">`;
            }

            // Tick Logic for Expert Side
            let ticks = '';
            if (msg.sender_type === 'expert') {
                ticks = msg.is_read ? '<span class="read-ticks" style="color: #fff; margin-left: 4px;">✓✓</span>' : '<span class="read-ticks" style="margin-left: 4px; opacity: 0.7;">✓</span>';
            }

            div.innerHTML = `
                <div class="message-bubble ${msg.sender_type === 'expert' ? 'user' : 'bot'}">
                    ${content}
                    <div class="message-meta">
                        <span>${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                        ${ticks}
                    </div>
                </div>
            `;
            messagesDiv.appendChild(div);
            this.scrollToBottom();
        },

        hideFooterAndButton() {
            const footer = document.querySelector('.chat-footer');
            if (footer) footer.style.display = 'none';

            const endBtn = document.querySelector('.chat-header .btn-danger');
            if (endBtn) endBtn.style.display = 'none';
        },

        // NAYA METHOD: Ended message add
        showEndedMessage() {
            const messagesDiv = document.getElementById('messages');
            if (messagesDiv && !messagesDiv.querySelector('.chat-ended-message')) {
                messagesDiv.insertAdjacentHTML('beforeend', `
                    <div class="text-center py-5 my-4 bg-light rounded chat-ended-message">
                        <h5 class="text-muted mb-3">Chat has ended</h5>
                        <p class="text-muted mb-0">This chat has been ended by the expert.</p>
                    </div>
                `);
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }
        },

        endChatByExpert() {
            Swal.fire({
                title: 'End Chat?',
                text: "Are you sure you want to end this chat?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, End Chat',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/expert/chat/${chatId}/end`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                toastr.success(data.message || 'Chat ended successfully!');

                                // State update
                                this.chatEnded = true;

                                // UI updates
                                this.hideFooterAndButton();
                                this.showEndedMessage();
                            } else {
                                toastr.error(data.message || 'Failed');
                            }
                        })
                        .catch(() => toastr.error('Network error'));
                }
            });
        },
        scrollToBottom() {
            const body = document.getElementById('messages');
            if (body) body.scrollTop = body.scrollHeight;
        },
        updateTicksToRead(messageId) {
            if (messageId) {
                const tickSpan = document.querySelector(`[data-message-id="${messageId}"] .read-ticks`);
                if (tickSpan) {
                    tickSpan.innerText = '✓✓';
                    tickSpan.style.color = '#34b7f1'; // Ya jo bhi expert theme ka color ho
                }
            } else {
                document.querySelectorAll('.read-ticks').forEach(tick => {
                    tick.innerText = '✓✓';
                    tick.style.color = '#34b7f1';
                });
            }
        },
        markAllAsRead() {
            axios.post('/expert/chat/mark-read', { chat_id: chatId });
        },

        markAsRead(messageId) {
            axios.post('/expert/chat/mark-specific-read', { message_id: messageId });
        },
        handleFileUpload(event) {
            this.selectedFile = event.target.files[0];
            this.sendMessage();
        },
        markAsRead(messageId) {
            axios.post('/expert/chat/mark-read', { chat_id: chatId });
        }
    }
}

export function adminExpertChatComponent() {
    return {
        newMessage: '',
        selectedFile: null,
        typing: false,
        typingTimer: null,
        showSearchModal: false,

        selectedExpertId: null,
        expertName: '',
        expertAvatar: '',
        currentChannel: null,

        searchQuery: '',
        searchResults: [],

        // Active experts in sidebar
        activeExperts: [],

        init() {
            console.log('[AdminChat] Component initialized');

            this.activeExperts = window.INITIAL_EXPERTS || [];
            this.searchResults = window.ALL_EXPERTS;

            this.loadInitialMessages();
            this.scrollToBottom();
            this.markAllAsRead();


        },

        openChat(expertId, name, avatar) {
            this.showSearchModal = false;

            // --- NAYA LOGIC: Sidebar mein check karo ya add karo ---
            const exists = this.activeExperts.find(e => e.id === expertId);
            if (!exists) {
                this.activeExperts.unshift({
                    id: expertId,
                    name: name,
                    avatar: avatar,
                    specialty: 'Expert',
                    unread_count: 0
                });
            }

            if (this.selectedExpertId === expertId) return;

            this.selectedExpertId = expertId;
            this.expertName = name;
            this.expertAvatar = avatar;

            const messagesDiv = document.getElementById('messages');
            if (messagesDiv) messagesDiv.innerHTML = '';

            if (this.currentChannel) {
                window.Echo.leave(this.currentChannel);
            }

            this.currentChannel = `admin-chat.${expertId}`;

            window.Echo.private(this.currentChannel)
                .listen('AdminExpertMessageSent', (e) => {
                    this.appendMessage(e.message);
                    if (e.message.sender_type === 'expert') {
                        this.markAsRead(e.message.id);
                    }
                })
                .listenForWhisper('typing', (e) => {
                    if (e.role === 'expert') {
                        this.typing = true;
                        clearTimeout(this.typingTimer);
                        this.typingTimer = setTimeout(() => this.typing = false, 1500);
                    }
                });

            this.loadInitialMessages(expertId);
            this.markAllAsRead();
        },
        selectExpertForChat(expertId, name, avatar) {
            this.openChat(expertId, name, avatar);
            this.searchQuery = '';
            this.searchResults = [];
        },

        appendMessage(msg) {
            console.log('[AdminChat] appendMessage called with:', msg);

            if (!msg || !msg.id) {
                console.warn('[AdminChat] Invalid message object, skipping append');
                return;
            }

            if (document.querySelector(`[data-message-id="${msg.id}"]`)) {
                console.log('[AdminChat] Message already exists, skipping:', msg.id);
                return;
            }

            const messagesDiv = document.getElementById('messages');
            if (!messagesDiv) {
                console.error('[AdminChat] #messages div not found!');
                return;
            }

            const div = document.createElement('div');
            div.setAttribute('data-message-id', msg.id);

            const isAdmin = msg.sender_type === 'admin';
            div.className = `message-container ${isAdmin ? 'user-side' : ''}`;

            let content = '';
            if (msg.image_path) {
                content = `<img src="/storage/${msg.image_path}" class="chat-img-preview" style="max-width:200px; border-radius:10px;">`;
            } else {
                content = msg.message ? msg.message.replace(/\n/g, '<br>') : '';
            }

            let ticks = '';
            if (isAdmin) {
                ticks = msg.is_read
                    ? '<span class="read-ticks" style="color: #34b7f1;">✓✓</span>'
                    : '<span class="read-ticks">✓</span>';
            }

            const time = new Date(msg.sent_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            div.innerHTML = `
                ${!isAdmin ? '<img src="' + this.expertAvatar + '" class="message-avatar" alt="Expert">' : ''}
                <div class="message-bubble ${isAdmin ? 'user' : 'bot'}">
                    ${content}
                    <div class="message-meta">
                        <span>${time}</span>
                        ${ticks}
                    </div>
                </div>
                ${isAdmin ? '<img src="' + window.ADMIN_AVATAR + '" class="message-avatar" alt="Admin">' : ''}
            `;

            messagesDiv.appendChild(div);
            console.log('[AdminChat] ✅ Message appended to DOM:', msg.id);
            this.scrollToBottom();
        },

        loadInitialMessages(expertId) {
            if (!expertId) {
                console.warn('[AdminChat] loadInitialMessages: expertId missing');
                return;
            }

            console.log('[AdminChat] Fetching initial messages for expert:', expertId);

            axios.get(`/admin/expert-chat/messages/${expertId}`)
                .then(res => {
                    console.log('[AdminChat] Initial messages loaded:', res.data.messages.length, 'messages');
                    res.data.messages.forEach(msg => this.appendMessage(msg));
                    this.scrollToBottom();
                })
                .catch(err => {
                    console.error('[AdminChat] Failed to load initial messages:', err.response || err);
                });
        },

        scrollToBottom() {
            const body = document.querySelector('.chat-body');
            if (body) {
                body.scrollTop = body.scrollHeight;
            }
        },

        sendMessage() {
            if (!this.newMessage.trim() && !this.selectedFile) return;
            if (!this.selectedExpertId) {
                console.warn('[AdminChat] Cannot send: no expert selected');
                return;
            }

            console.log('[AdminChat] Sending message to expert:', this.selectedExpertId);

            let formData = new FormData();
            formData.append('expert_id', this.selectedExpertId);
            if (this.newMessage) formData.append('message', this.newMessage);
            if (this.selectedFile) formData.append('image', this.selectedFile);

            axios.post('/admin/expert-chat/send', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            }).then(res => {
                console.log('[AdminChat] Message sent successfully:', res.data);
                this.newMessage = '';
                this.selectedFile = null;
                document.getElementById('imageInput').value = '';
                this.appendMessage(res.data.message_data);
            }).catch(err => {
                console.error('[AdminChat] Send failed:', err.response || err);
            });
        },

        typingEvent() {
            if (!this.selectedExpertId) return;

            console.log('[AdminChat] Typing whisper sent to:', `admin-chat.${this.selectedExpertId}`);
            window.Echo.private(`admin-chat.${this.selectedExpertId}`)
                .whisper('typing', { role: 'admin' });
        },

        handleFileUpload(event) {
            this.selectedFile = event.target.files[0];
            if (this.selectedFile) {
                this.sendMessage();
            }
        },

        markAllAsRead() {
            if (!this.selectedExpertId) return;

            axios.post('/admin/expert-chat/mark-read', { expert_id: this.selectedExpertId });
        },

        markAsRead(messageId) {
            axios.post('/admin/expert-chat/mark-specific-read', { message_id: messageId });
        },

        // Search functionality
        async searchExperts() {
            if (!this.searchQuery.trim()) {
                this.searchResults = window.ALL_EXPERTS;
                return;
            }

            try {
                const query = this.searchQuery.toLowerCase();
                this.searchResults = window.ALL_EXPERTS.filter(expert =>
                    expert.name.toLowerCase().includes(query) ||
                    expert.specialty.toLowerCase().includes(query)
                );
            } catch (err) {
                console.error('Search error:', err);
                this.searchResults = [];
            }
        },

        selectExpertForChat(expertId, name, avatar) {
            this.openChat(expertId, name, avatar);
        }
    }
}

export function expertAdminChatComponent() {
    const expertId = window.EXPERT_ID;
    if (!expertId || expertId === 'null') {
        console.error('Expert ID missing!');
        return {};
    }
    return {
        newMessage: '',
        selectedFile: null,
        typing: false,
        typingTimer: null,

        init() {
            this.loadInitialMessages();
            this.scrollToBottom();
            this.markAllAsRead();


            window.Echo.private(`admin-chat.${expertId}`)
                .listen('AdminExpertMessageSent', (e) => {

                    this.appendMessage(e.message);

                    if (e.message.sender_type === 'admin') {
                        this.markAsRead(e.message.id);
                    }
                })
                .listenForWhisper('typing', (e) => {
                    if (e.role === 'admin') {
                        this.typing = true;
                        clearTimeout(this.typingTimer);
                        this.typingTimer = setTimeout(() => this.typing = false, 2000);
                    }
                });
        },
        loadInitialMessages() {
            axios.get('/expert/admin-chat/messages')
                .then(res => {
                    res.data.messages.forEach(msg => this.appendMessage(msg));
                    this.scrollToBottom();
                });
        },

        appendMessage(msg) {
            if (msg.id && document.querySelector(`[data-message-id="${msg.id}"]`)) return;

            const messagesDiv = document.getElementById('messages');
            const div = document.createElement('div');
            div.setAttribute('data-message-id', msg.id);

            const isExpert = msg.sender_type === 'expert';
            div.className = `message-container ${isExpert ? 'user-side' : ''}`;

            let content = msg.image_path
                ? `<img src="/storage/${msg.image_path}" class="chat-img-preview" style="max-width:200px; border-radius:10px;">`
                : (msg.message ? msg.message.replace(/\n/g, '<br>') : '');

            let ticks = '';
            if (isExpert) {
                ticks = msg.is_read
                    ? '<span class="read-ticks" style="color: #34b7f1;">✓✓</span>'
                    : '<span class="read-ticks">✓</span>';
            }

            div.innerHTML = `
                ${!isExpert ? '<img src="/assets/admin-avatar.jpg" class="message-avatar">' : ''}
                <div class="message-bubble ${isExpert ? 'user' : 'bot'}">
                    ${content}
                    <div class="message-meta">
                        <span>${new Date(msg.sent_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                        ${ticks}
                    </div>
                </div>
                ${isExpert ? '<img src="' + window.EXPERT_AVATAR + '" class="message-avatar">' : ''}
            `;

            messagesDiv.appendChild(div);
            this.scrollToBottom();
        },

        scrollToBottom() {
            const body = document.querySelector('.chat-body');
            if (body) body.scrollTop = body.scrollHeight;
        },

        sendMessage() {
            if (!this.newMessage.trim() && !this.selectedFile) return;

            let formData = new FormData();
            if (this.newMessage) formData.append('message', this.newMessage);
            if (this.selectedFile) formData.append('image', this.selectedFile);

            axios.post('/expert/massages/admin-chat/send', formData).then(res => {
                this.newMessage = '';
                this.selectedFile = null;
                document.getElementById('imageInput').value = '';
                this.appendMessage(res.data.message_data);
            });
        },

        typingEvent() {
            window.Echo.private(`admin-chat.${expertId}`).whisper('typing', { role: 'expert' });
        },

        handleFileUpload(event) {
            this.selectedFile = event.target.files[0];
            if (this.selectedFile) this.sendMessage();
        },

        markAllAsRead() {
            axios.post('/expert/admin-chat/mark-read');
        },

        markAsRead(messageId) {
            axios.post('/expert/admin-chat/mark-specific-read', { message_id: messageId });
        }
    }
}

document.addEventListener('alpine:init', () => {
    window.chatComponent = chatComponent;
    window.expertChatComponent = expertChatComponent;
    window.expertAdminChatComponent = expertAdminChatComponent;

    Alpine.data('adminExpertChatComponent', adminExpertChatComponent);
});