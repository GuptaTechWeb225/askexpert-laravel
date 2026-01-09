
export function chatComponent(chatId) {
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    const userId = userIdMeta ? parseInt(userIdMeta.getAttribute('content')) : null;
    return {
        newMessage: '',
        selectedFile: null,
        typing: false,
        expertOnline: false,
        typingTimer: null,
        isVideo: false,
        inCall: false,
        callState: 'idle',
        callInitiator: null,
        callStatusText: '',
        videoEnabled: true,
        isMuted: false,
        agoraClient: null,
        localAudioTrack: null,
        localVideoTrack: null,
        _joining: false,
        callerInfo: null,
        callDuration: 0,
        timerInterval: null,


        initiateCall(withVideo) {
            if (this.inCall) return;

            this.isVideo = withVideo;
            this.inCall = true;
            this.callInitiator = 'user';
            this.callState = 'ringing';
            this.callStatusText = 'Calling...';
            this.callerInfo = { avatar: window.AUTH_USER_AVATAR, name: 'You' };

            const modalEl = document.getElementById('callModal');
            this.callBootstrapModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            this.callBootstrapModal.show();

            this.playRingtone();

            window.Echo.private(`chat.${chatId}`).whisper('incoming-call', {
                from: 'user',
                type: withVideo ? 'video' : 'voice',
                chatId: chatId
            });
        },

        get formattedDuration() {
            const mins = Math.floor(this.callDuration / 60);
            const secs = this.callDuration % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },

        // Timer start karne ka function
        startTimer() {
            this.callDuration = 0;
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                this.callDuration++;
            }, 1000);
        },

        createAgoraClient() {
            const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });
            client.on("user-published", async (user, mediaType) => {
                await client.subscribe(user, mediaType);
                if (mediaType === "video") {
                    // Force wait for Alpine.js to show the div
                    this.$nextTick(() => {
                        const remoteDiv = document.getElementById('remote-media');
                        if (remoteDiv) {
                            user.videoTrack.play(remoteDiv);
                        }
                    });
                }
                if (mediaType === "audio") {
                    user.audioTrack.play();
                }
            });
            return client;
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

        endCall() {
            this.stopRingtone();
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.callDuration = 0;
            if (this.localAudioTrack) {
                this.localAudioTrack.close();
                this.localAudioTrack = null;
            }
            if (this.localVideoTrack) {
                this.localVideoTrack.close();
                this.localVideoTrack = null;
            }
            if (this.agoraClient) {
                this.agoraClient.leave();
                this.agoraClient = null;
            }

            this.callState = 'idle';
            this.inCall = false;
            this.callInitiator = null;
            this.callerInfo = null;

            document.getElementById('local-media').innerHTML = '';
            document.getElementById('remote-media').innerHTML = '';

            const modal = bootstrap.Modal.getInstance(document.getElementById('callModal'));
            if (modal) modal.hide();
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
                    if (this._joining) return;
                    this._joining = true;
                    try {
                        this.stopRingtone();
                        this.callStatusText = 'Connecting...';
                        this.callState = 'connecting';


                        if (!this.agoraClient) {
                            this.agoraClient = this.createAgoraClient(); // Iske andar remote-media play karne ka logic pehle se hai
                        }

                        const res = await axios.post(`/chat/${chatId}/generate-token`);
                        const { token, channel, uid, app_id } = res.data;

                        await this.agoraClient.join(app_id || window.AGORA_APP_ID, channel, token, uid);

                        // --- SAFE TRACKS FOR USER SIDE ---
                        let tracks = [];
                        try {
                            if (this.isVideo) {
                                // Pehle dono try karo
                                tracks = await AgoraRTC.createMicrophoneAndCameraTracks().catch(async (e) => {
                                    console.warn("Camera failed, falling back to audio only", e);
                                    this.isVideo = false;
                                    const audio = await AgoraRTC.createMicrophoneAudioTrack();
                                    return [audio];
                                });
                            } else {
                                const audio = await AgoraRTC.createMicrophoneAudioTrack();
                                tracks = [audio];
                            }
                        } catch (deviceErr) {
                            throw new Error("Could not access microphone/camera");
                        }

                        this.localAudioTrack = tracks[0];
                        this.localVideoTrack = tracks[1] || null;

                        if (this.localVideoTrack) {
                            const localDiv = document.getElementById('local-media');
                            if (localDiv) {
                                localDiv.innerHTML = '';
                                this.localVideoTrack.play(localDiv);
                            }
                        }

                        await this.agoraClient.publish(tracks.filter(Boolean));

                        this.callState = 'connected';
                        this.inCall = true;
                        this.callStatusText = 'Connected';
                        this.startTimer();
                    } catch (err) {
                        console.error('❌ User side Agora join failed:', err);
                        toastr.error('Connection failed: ' + err.message);
                        this.endCall();
                    } finally {
                        this._joining = false;
                    }
                })


                .listenForWhisper('incoming-call', (data) => {
                    if (data.from === 'expert') {
                        this.callInitiator = 'expert';
                        this.callState = 'incoming';
                        this.isVideo = data.type === 'video';
                        this.callStatusText = 'Incoming Call from Expert';
                        this.callerInfo = { avatar: '/assets/front-end/img/placeholder/user.png', name: 'Expert' };

                        const modalEl = document.getElementById('callModal');
                        this.callBootstrapModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        this.callBootstrapModal.show();

                        this.playRingtone();
                    }
                })
                .listenForWhisper('call-rejected', () => {
                    this.callStatusText = 'Call Rejected';
                    this.stopRingtone();
                    this.endCall();
                })
                .listenForWhisper('call-ended', () => {
                    this.callStatusText = 'Call Ended';
                    this.endCall();
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
        async acceptCall() {
            if (this._joining) return;
            this._joining = true;
            this.callState = 'connecting';
            this.callStatusText = 'Connecting...';

            try {
                const res = await axios.post(`/chat/${chatId}/generate-token`);
                const { token, channel, uid, app_id } = res.data; // app_id bhi le agar alag hai

                this.agoraClient = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });

                // Events pehle set karo (jaise pehle suggest kiya tha)
                this.agoraClient.on('user-published', async (user, mediaType) => {
                    await this.agoraClient.subscribe(user, mediaType);
                    if (mediaType === 'video') {
                        user.videoTrack.play('remote-media');
                    }
                    if (mediaType === 'audio') {
                        user.audioTrack.play();
                    }
                });

                await this.agoraClient.join(app_id || window.AGORA_APP_ID, channel, token, uid);

                const tracks = await AgoraRTC.createMicrophoneAndCameraTracks(
                    {}, this.isVideo ? {} : null
                );

                this.localAudioTrack = tracks[0];
                this.localVideoTrack = tracks[1] || null;

                if (this.localVideoTrack) {
                    this.localVideoTrack.play('local-media');
                }

                await this.agoraClient.publish(tracks.filter(Boolean));

                this.callState = 'connected';
                this.callStatusText = 'Connected';

            } catch (err) {
                console.error('❌ Call failed:', err);
                this.callState = 'idle';
                this.callStatusText = 'Connection Failed';
                this.endCall();
            } finally {
                this._joining = false;
            }
        },
        cancelCall() {
            this.stopRingtone();
            window.Echo.private(`chat.${chatId}`).whisper('call-cancelled', { chatId });
            this.endCall();
        },

        rejectCall() {
            window.Echo.private(`chat.${chatId}`).whisper('call-rejected', { chatId });
            this.endCall();
        },

        toggleMute() {
            if (this.localAudioTrack) {
                this.isMuted = !this.isMuted;
                this.localAudioTrack.setEnabled(!this.isMuted);
            }
        },

        toggleVideo() {
            if (this.localVideoTrack) {
                this.videoEnabled = !this.videoEnabled;
                this.localVideoTrack.setEnabled(this.videoEnabled);
            }
        },

        hangUp() {
            window.Echo.private(`chat.${chatId}`).whisper('call-ended', { chatId });
            this.endCall();
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

    if (typeof chatId === 'object') {
        console.error('❌ chatId object aa raha hai:', chatId);
        chatId = chatId.id;
    }

    chatId = Number(chatId);

    return {
        newMessage: '',
        selectedFile: null,
        customerTyping: false,
        customerOnline: false,
        typingTimer: null,
        chatEnded: false,
        isVideo: false,
        isIncoming: false,
        isMuted: false,
        cameraOff: false,
        callerInfo: null,
        callStatusText: '',
        callState: '',
        videoEnabled: false,
        mediaTestResult: null,
        _initialized: false,
        _joining: false,
        callDuration: 0,
        timerInterval: null,



        async testMediaAvailability() {
            try {
                console.log('🔍 Testing mic/camera availability…');

                const stream = await navigator.mediaDevices.getUserMedia({
                    audio: true,
                    video: this.isVideo
                });

                stream.getTracks().forEach(t => t.stop());
                console.log('✅ Media available & Released');

                this.mediaTestResult = 'ok';
                this.callStatusText = 'Mic & Camera ready';

            } catch (err) {
                console.error('❌ Media test failed:', err);

                if (err.name === 'NotAllowedError') {
                    this.mediaTestResult = 'denied';
                    this.callStatusText = 'Permission denied';
                } else {
                    this.mediaTestResult = 'busy';
                    this.callStatusText = 'Mic/Camera busy';
                }
            }
        },

        get formattedDuration() {
            const mins = Math.floor(this.callDuration / 60);
            const secs = this.callDuration % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },

        // Timer start karne ka function
        startTimer() {
            this.callDuration = 0;
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                this.callDuration++;
            }, 1000);
        },

        hangUp() {
            window.Echo.private(`chat.${chatId}`)
                .whisper('call-ended', { chatId });

            this.resetCallUI();
        },
        init() {

            if (this._initialized) return;
            this._initialized = true;

            console.log('🟢 Expert chat initialized ONCE');
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
                .listenForWhisper('incoming-call', async (data) => {
                    console.log('📞 Incoming call:', data);

                    this.isVideo = data.type === 'video';
                    this.callState = 'incoming';
                    this.callStatusText = 'Incoming call…';

                    $('#callModal').modal('show');

                })
                .listenForWhisper('call-ended', () => {
                    this.resetCallUI();
                })
                .listenForWhisper('call-rejected', () => {
                    this.resetCallUI();
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
        async acceptCall() {
            if (this._joining) return;
            this._joining = true;

            try {
                this.callState = 'connecting';
                this.callStatusText = 'Connecting…';

                const res = await axios.post(`/chat/${chatId}/generate-token`);
                const { token, channel, uid } = res.data;

                // Client create
                const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

                // 🔥 Event listeners PEHLE set kar (miss na ho)
                // expertChatComponent -> acceptCall ke andar
                client.on("user-published", async (user, mediaType) => {
                    await client.subscribe(user, mediaType);
                    if (mediaType === "video") {
                        // Timeout thoda delay deta hai taaki DOM ready ho jaye
                        setTimeout(() => {
                            const remoteDiv = document.getElementById('remote-media');
                            if (remoteDiv) {
                                remoteDiv.innerHTML = '';
                                user.videoTrack.play(remoteDiv); // ID pass karne ke bajaye element pass karein
                            }
                        }, 500);
                    }
                    if (mediaType === "audio") {
                        user.audioTrack.play();
                    }
                });

                client.on("user-unpublished", (user, mediaType) => {
                    console.log('❌ Remote user unpublished:', user.uid, mediaType);
                    if (mediaType === "video") {
                        document.getElementById('remote-media').innerHTML = '';
                    }
                });

                client.on("user-left", (user) => {
                    console.log('👋 Remote user left:', user.uid);
                    this.resetCallUI();
                });

                // Join
                await client.join(window.AGORA_APP_ID, channel, token, uid);

                // Tracks create & publish
                const tracks = await AgoraRTC.createMicrophoneAndCameraTracks();
                const micTrack = tracks[0];
                const camTrack = this.isVideo ? tracks[1] : null;

                if (camTrack) camTrack.play(document.getElementById('local-media'));

                const publishTracks = [micTrack];
                if (camTrack) publishTracks.push(camTrack);

                await client.publish(publishTracks);

                this.agoraClient = client;
                this.micTrack = micTrack;
                this.cameraTrack = camTrack;
                this.videoEnabled = !!camTrack;
                this.callState = 'connected';
                this.callStatusText = 'Connected';
                this.startTimer();
                window.Echo.private(`chat.${chatId}`).whisper('call-accepted', { chatId });

                console.log('✅ Agora fully connected');

            } catch (err) {
                console.error('❌ Agora error:', err);
                this.resetCallUI();
                alert('Call failed: ' + (err.message || 'Unknown error'));
            } finally {
                this._joining = false;
            }
        },

        get mediaErrorMessage() {
            if (this.mediaTestResult === 'busy') {
                return `
Microphone or Camera is currently being used elsewhere.

Please:
• Close Zoom / Google Meet / WhatsApp Web
• Close other browser tabs using mic
• Refresh this page
        `;
            }

            if (this.mediaTestResult === 'denied') {
                return `
Camera/Microphone permission denied.

Steps:
1. Click 🔒 lock icon near address bar
2. Allow Camera & Microphone
3. Reload page
        `;
            }

            return '';
        },


        async resetCallUI() {

            if (this.timerInterval) clearInterval(this.timerInterval); // 👈 Ye line add karein
            this.callDuration = 0; // Reset duration
            if (this.agoraClient) {
                await this.agoraClient.leave();
                this.agoraClient = null;
            }

            if (this.micTrack) {
                this.micTrack.stop();
                this.micTrack = null;
            }

            if (this.cameraTrack) {
                this.cameraTrack.stop();
                this.cameraTrack = null;
            }

            this.callState = '';
            this.mediaTestResult = null;

            document.getElementById('local-media').innerHTML = '';
            document.getElementById('remote-media').innerHTML = '';

            $('#callModal').modal('hide');
        }
        ,
        rejectCall() {
            window.Echo.private(`chat.${chatId}`)
                .whisper('call-rejected', { chatId });

            this.resetCallUI();
        },
        toggleMute() {
            this.isMuted = !this.isMuted;

            if (this.micTrack) {
                this.micTrack.setEnabled(!this.isMuted);
            }
        }
        ,
        toggleVideo() {
            if (!this.cameraTrack) return;

            if (this.videoEnabled) {
                this.cameraTrack.setEnabled(false);
                this.videoEnabled = false;
            } else {
                this.cameraTrack.setEnabled(true);
                this.videoEnabled = true;
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