
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
        _dummy: false,
        formattedDuration: 0,
        callAcceptedWhisperSent: false,


        initiateCall(withVideo) {
            if (this.inCall) return;

            this.isVideo = withVideo;
            this.inCall = true;
            this.callInitiator = 'user';
            this.callState = 'ringing';
            this.callStatusText = 'Calling...';
            this.callerInfo = { avatar: window.AUTH_USER_AVATAR, name: 'Expert' };

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
            this.cleanupMedia();
            document.getElementById('local-media').innerHTML = '';
            document.getElementById('remote-media').innerHTML = '';

            const modal = bootstrap.Modal.getInstance(document.getElementById('callModal'));
            if (modal) modal.hide();
        },


        init() {
            this.scrollToBottom();
            this.markAllAsRead();
            this.callAcceptedWhisperSent = false;
            window.Echo.private(`chat.${chatId}`)



                .subscribed(() => {
                    console.log('✅ SUCCESSFULLY SUBSCRIBED to chat.' + chatId);
                })
                .error((error) => {
                    console.error('❌ CHANNEL SUBSCRIPTION FAILED for chat.' + chatId, error);
                })
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
                        this.callStatusText = 'Connecting...';
                        this.callState = 'connecting';


                        if (!this.agoraClient) {
                            this.agoraClient = this.createAgoraClient();
                        }

                        const res = await axios.post(`/chat/${chatId}/generate-token`);
                        const { token, channel, uid, app_id } = res.data;

                        await this.agoraClient.join(app_id || window.AGORA_APP_ID, channel, token, uid);

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
                        this.stopRingtone();
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
                    this.stopRingtone();
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

            const wasInCall = sessionStorage.getItem('activeCall') === 'true';
            if (wasInCall) {
                console.log('Page refreshed during active call – auto ending call');
                this.endCall();
                sessionStorage.removeItem('activeCall');
                toastr.warning('Call ended due to page refresh');
            }

            this.$watch('callState', (newVal) => {
                if (newVal === 'connected') {
                    sessionStorage.setItem('activeCall', 'true');
                } else {
                    sessionStorage.removeItem('activeCall');
                }
            });

            this.checkExpertOnline();
            setInterval(() => this.checkExpertOnline(), 10000);
        },
         async acceptCall() {
            if (this._joining) return;
            this._joining = true;

            try {
                this.stopRingtone();
                this.callState = 'connecting';
                this.callStatusText = 'Connecting…';

                const res = await axios.post(`/chat/${chatId}/generate-token`);
                const { token, channel, uid } = res.data;

                const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });
                client.on("user-published", async (user, mediaType) => {
                    await client.subscribe(user, mediaType);
                    if (mediaType === "video") {
                        setTimeout(() => {
                            const remoteDiv = document.getElementById('remote-media');
                            if (remoteDiv) {
                                remoteDiv.innerHTML = '';
                                user.videoTrack.play(remoteDiv);
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

        cancelCall() {
            this.stopRingtone();
            window.Echo.private(`chat.${chatId}`).whisper('call-cancelled', { chatId });
            this.endCall();
        },
        cleanupMedia() {
            console.log('Cleaning up all media resources...');
            if (this.localAudioTrack) {
                this.localAudioTrack.stop();
                this.localAudioTrack.close();
                this.localAudioTrack = null;
            }
            if (this.localVideoTrack) {
                this.localVideoTrack.stop();
                this.localVideoTrack.close();
                this.localVideoTrack = null;
            }
            if (this.micTrack) {
                this.micTrack.stop();
                this.micTrack.close();
                this.micTrack = null;
            }
            if (this.cameraTrack) {
                this.cameraTrack.stop();
                this.cameraTrack.close();
                this.cameraTrack = null;
            }
            if (this.agoraClient) {
                try {
                    this.agoraClient.leave();
                    this.agoraClient.removeAllListeners(); // All events hata do
                } catch (e) {
                    console.warn('Client leave failed, ignoring:', e);
                }
                this.agoraClient = null;
            }
            const localDiv = document.getElementById('local-media');
            if (localDiv) localDiv.innerHTML = '';
            const remoteDiv = document.getElementById('remote-media');
            if (remoteDiv) remoteDiv.innerHTML = '';
            this.callState = 'idle';
            this.inCall = false;
            this.callInitiator = null;
            this.callerInfo = null;
            this.callDuration = 0;
            this.videoEnabled = false;
            this.isMuted = false;
            console.log('Media cleanup complete!');
        },
        rejectCall() {
            window.Echo.private(`chat.${chatId}`).whisper('call-rejected', { chatId });
            this.endCall();
        },
        toggleMute() {
            this.isMuted = !this.isMuted;
            let audioTrack = this.localAudioTrack || this.micTrack;

            if (!audioTrack) {
                console.warn('No audio track available for mute/unmute');
                return;
            }

            try {
                if (this.isMuted) {
                    audioTrack.setEnabled(false);
                    console.log('Mic muted successfully');
                } else {
                    console.log('Unmuting: Stopping & replacing old track...');

                    audioTrack.stop();
                    audioTrack.close();

                    // Fresh new mic track banao
                    AgoraRTC.createMicrophoneAudioTrack()
                        .then(newTrack => {
                            // Update reference
                            this.localAudioTrack = newTrack;
                            this.micTrack = newTrack;  // Expert side ke liye bhi

                            // Publish new track
                            if (this.agoraClient) {
                                this.agoraClient.publish(newTrack)
                                    .then(() => {
                                        console.log('Fresh mic track published after unmute – success!');
                                    })
                                    .catch(err => {
                                        console.error('Republish failed:', err);
                                    });
                            } else {
                                console.warn('No agoraClient found for republish');
                            }
                        })
                        .catch(err => {
                            console.error('Failed to create fresh mic track:', err);
                            alert('Mic access failed. Please check permissions.');
                        });
                }
            } catch (err) {
                console.error('toggleMute error:', err);
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
        get formattedDuration() {
            const mins = Math.floor(this.callDuration / 60);
            const secs = this.callDuration % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },

        startTimer() {
            this.callDuration = 0;
            this.formattedDuration = '00:00';

            if (this.timerInterval) clearInterval(this.timerInterval);

            this.timerInterval = setInterval(() => {
                this.callDuration++;

                const mins = Math.floor(this.callDuration / 60);
                const secs = this.callDuration % 60;

                this.formattedDuration =
                    `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;

                console.log('Timer tick:', this.callDuration, this.formattedDuration);
            }, 1000);
        },
        markAsRead(messageId) {
            axios.post('/chat/mark-read', { message_id: messageId });
        }
    }
}

export function expertChatComponent(chatId) {


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
        inCall: false,
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
        localAudioTrack: null,
        localVideoTrack: null,

        initiateCall(withVideo) {
            if (this.inCall) return;

            this.isVideo = withVideo;
            this.callState = 'ringing';
            this.callStatusText = 'Calling User...';
            this.callerInfo = { avatar: window.AUTH_USER_AVATAR, name: 'User' };
            this.callInitiator = 'user';

            $('#callModal').modal('show');
            this.playRingtone?.();

            window.Echo.private(`chat.${chatId}`).whisper('incoming-call', {
                from: 'expert',
                type: withVideo ? 'video' : 'voice',
                chatId: chatId
            });
        },


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
                    this.callInitiator = 'user';
                    this.isVideo = data.type === 'video';
                    this.callState = 'incoming';
                    this.callStatusText = 'Incoming Call from User';
                    this.callerInfo = { avatar: '/assets/front-end/img/placeholder/user.png', name: 'User' };
                    this.playRingtone();
                    $('#callModal').modal('show');

                })


                .listenForWhisper('call-accepted', async () => {
                    if (this._joining) {
                        console.warn('[Expert] Already joining in progress - ignoring duplicate call-accepted');
                        return;
                    }
                    this._joining = true;

                    const startTime = Date.now();
                    console.log(`[Expert ${new Date().toISOString()}] call-accepted whisper RECEIVED`);

                    await new Promise(r => setTimeout(r, 800)); // safety delay - adjust 500–1200ms if needed

                    try {
                        console.log(`[Expert] Step 1: Generating token... (time: ${Date.now() - startTime}ms)`);
                        const res = await axios.post(`/chat/${chatId}/generate-token`);
                        const { token, channel, uid, app_id, role } = res.data;  // ← role bhi log karo!

                        console.log(`[Expert] Step 2: Token received`, {
                            timestamp: new Date().toISOString(),
                            uid: uid,
                            uidType: typeof uid,
                            channel: channel,
                            roleFromBackend: role,          // ← yeh check karna zaroori
                            timeTaken: Date.now() - startTime
                        });

                        if (!this.agoraClient) {
                            console.log('[Expert] Creating new Agora client');
                            this.agoraClient = this.createAgoraClient();
                        }

                        const clientState = this.agoraClient.connectionState;
                        console.log(`[Expert] Current client state before join: ${clientState}`);

                        if (clientState === 'CONNECTED') {
                            console.log('[Expert] Already connected → skipping join, only publishing tracks');
                        } else if (clientState === 'CONNECTING') {
                            console.warn('[Expert] Still connecting → waiting extra 1s');
                            await new Promise(r => setTimeout(r, 1000));
                        } else {
                            console.log(`[Expert] Step 3: Joining channel with UID ${uid}...`);
                            try {
                                await this.agoraClient.join(app_id || window.AGORA_APP_ID, channel, token, uid);
                                console.log(`[Expert] Join SUCCESS - UID: ${uid}`);
                            } catch (joinErr) {
                                console.error('[Expert] JOIN FAILED - Detailed error:', {
                                    code: joinErr.code,
                                    message: joinErr.message,
                                    reason: joinErr.reason || 'N/A',
                                    fullError: joinErr
                                });
                                throw joinErr;
                            }
                        }

                        let tracks = [];
                        try {
                            if (this.isVideo) {
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
                        console.error('[Expert] Full accept flow FAILED:', {
                            error: err,
                            code: err.code,
                            message: err.message,
                            stack: err.stack?.substring(0, 300)
                        });
                        toastr.error('Connection failed: ' + (err.message || 'Unknown'));
                        this.endCall?.();
                    } finally {
                        this._joining = false;
                        console.log(`[Expert] Accept flow completed in ${Date.now() - startTime}ms`);
                    }
                })
                .listenForWhisper('call-cancelled', () => {
                    this.stopRingtone();
                    this.resetCallUI();
                    toastr.info('Call cancelled');
                })

                .listenForWhisper('call-ended', () => {
                    this.stopRingtone();
                    this.resetCallUI();
                })
                .listenForWhisper('call-rejected', () => {
                    this.stopRingtone();
                    this.resetCallUI();
                });

            const wasInCall = sessionStorage.getItem('activeCall') === 'true';
            if (wasInCall) {
                console.log('Page refreshed during active call – auto ending call');
                this.resetCallUI();
                sessionStorage.removeItem('activeCall');
                toastr.warning('Call ended due to page refresh');
            }

            this.$watch('callState', (newVal) => {
                if (newVal === 'connected') {
                    sessionStorage.setItem('activeCall', 'true');
                } else {
                    sessionStorage.removeItem('activeCall');
                }
            });

            if ('{{ $chat->status }}' === 'ended') {
                this.chatEnded = true;
                this.hideFooterAndButton();
                this.showEndedMessage();
            }
        },
        handleChatAction(action) {

            if (action === 'resolved') {
                // Window se safe data le rahe hain
                const info = window.chatInfo || {};
                const chatIdDisplay = info.chatId || chatId || 'N/A'; // fallback
                const customerName = info.customerName || 'Customer';
                const categoryName = info.categoryName || 'General';

                let sessionDuration = 'N/A';
                if (info.startTime) {
                    try {
                        const startTime = new Date(info.startTime);
                        if (!isNaN(startTime.getTime())) {
                            const endTime = new Date();
                            const durationMs = endTime - startTime;
                            const minutes = Math.floor(durationMs / 60000);
                            const seconds = Math.floor((durationMs % 60000) / 1000);
                            sessionDuration = `${minutes} min ${seconds} sec`;
                        }
                    } catch (e) {
                        console.warn('Duration calculation failed:', e);
                    }
                }

                Swal.fire({
                    title: 'Mark Question as Completed',
                    html: `
    <div style="text-align: left; font-size: 15px; margin: 20px 0; line-height: 1.6;">
        <!-- Row 1: Question ID + Customer Name -->
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Question ID</label>
                <input type="text" value="${chatIdDisplay}" readonly 
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; background: #f8f9fa; font-size: 14px;">
            </div>
            <div style="flex: 1;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Customer Name</label>
                <input type="text" value="${customerName}" readonly 
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; background: #f8f9fa; font-size: 14px;">
            </div>
        </div>

        <!-- Row 2: Category + Session Type -->
        <div style="display: flex; gap: 15px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Category</label>
                <input type="text" value="${categoryName}" readonly 
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; background: #f8f9fa; font-size: 14px;">
            </div>
            <div style="flex: 1;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Session Type</label>
                <input type="text" value="Chat" readonly 
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; background: #f8f9fa; font-size: 14px;">
            </div>
        </div>

        <!-- Row 3: Session Duration (sirf ek field, lekin flex ke saath align) -->
        <div style="display: flex; gap: 15px;">
            <div style="flex: 1;">
                <label style="font-weight: bold; display: block; margin-bottom: 5px;">Session Duration</label>
                <input type="text" value="${sessionDuration}" readonly 
                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px; background: #f8f9fa; font-size: 14px;">
            </div>
            <!-- Empty div for alignment (2 fields wale row ke saath balance) -->
            <div style="flex: 1;"></div>
        </div>

        <p style="margin-top: 25px; color: #555; font-size: 14px; text-align: center;">
            Are you sure you want to mark this chat as resolved?
        </p>
    </div>
`,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm & Send for Admin',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.performChatAction('resolved', null);
                    }
                });

                return;
            }
            if (action !== 'optout') {
                // Baaki actions same rahe (block, miscategorized, resolved)
                let title, text, confirmBtnText, confirmBtnColor;

                switch (action) {
                    case 'block':
                        title = 'Block User?';
                        text = "User will be blocked and won't be able to chat with you again.";
                        confirmBtnText = 'Yes, Block';
                        confirmBtnColor = '#dc3545';
                        break;
                    case 'miscategorized':
                        title = 'Report Miscategorized?';
                        text = "This will report the chat as miscategorized to admin and end the session.";
                        confirmBtnText = 'Yes, Report';
                        confirmBtnColor = '#fd7e14';
                        break;
                    case 'resolved':
                        title = 'Mark as Resolved?';
                        text = "Chat will be marked as successfully resolved.";
                        confirmBtnText = 'Yes, Resolved';
                        confirmBtnColor = '#198754';
                        break;
                }

                Swal.fire({
                    title: title,
                    text: text,
                    icon: action === 'resolved' ? 'success' : 'warning',
                    showCancelButton: true,
                    confirmButtonText: confirmBtnText,
                    confirmButtonColor: confirmBtnColor,
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.performChatAction(action, null);
                    }
                });

                return;
            }

            Swal.fire({
                title: 'Opt Out of Chat',
                html: `
            <div style="text-align: left; margin: 20px 0;">
                <p>Please select a reason for opting out (required):</p>
                <div class="optout-reasons p-2">
                    <label style="display: flex; margin: 12px 0; font-size: 15px;" class="border p-3 radius-10">
                        
                        Miscategorized: Sent for manual review and reassigned.
                          <div>
                        <input type="radio" name="optoutReason" value="Miscategorized: Sent for manual review and reassigned." style="">
                          </div>
                    </label>
                    <label style="display: flex; margin: 12px 0; font-size: 15px;" class="border p-3 radius-10">
                        Not my expertise: Reassigned as per usual matching rules.
                                                  <div>

                                                <input type="radio" name="optoutReason" value="Not my expertise: Reassigned as per usual matching rules." style="">
                                                                          </div>


                    </label>
                    <label style="display: flex; margin: 12px 0; font-size: 15px;" class="border p-3 radius-10">
                        Time consuming: Reassigned as per usual matching rules.
                                                  <div>

                                                <input type="radio" name="optoutReason" value="Time consuming: Reassigned as per usual matching rules." style="">
                                                                          </div>


                    </label>
                    <label style="display: flex; margin: 12px 0; font-size: 15px;" class="border p-3 radius-10">
                        Other: Reassigned as per usual matching rules.
                                                  <div>

                                                <input type="radio" name="optoutReason" value="Other: Reassigned as per usual matching rules." style="">
                                                                          </div>


                    </label>
                </div>
            </div>
        `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Opt Out',
                confirmButtonColor: '#0d6efd',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const selected = document.querySelector('input[name="optoutReason"]:checked');
                    if (!selected) {
                        Swal.showValidationMessage('Please select a reason');
                        return false;
                    }
                    return selected.value;
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    const reason = result.value;
                    this.performChatAction('optout', reason);
                }
            });
        },

        async performChatAction(action, reason = null) {
            try {
                const payload = {
                    action: action,
                    chat_id: chatId
                };
                if (action === 'optout' && reason) {
                    payload.reason = reason;  // ← yeh extra field bhej rahe hain
                }

                const res = await fetch(`/expert/chat/${chatId}/action`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.success) {
                    toastr.success(data.message || 'Action completed successfully');

                    // Sabhi cases me chat end karna hai
                    this.chatEnded = true;
                    this.hideFooterAndButton();
                    this.showEndedMessage();

                    // Optional: extra UI feedback
                    if (action === 'block') {
                        toastr.info('User has been blocked');
                    } else if (action === 'miscategorized') {
                        toastr.warning('Miscategorization reported to admin');
                    } else if (action === 'optout') {
                        toastr.info('You have opted out of this chat');
                    }
                } else {
                    toastr.error(data.message || 'Action failed');
                }
            } catch (err) {
                console.error(err);
                toastr.error('Network error. Please try again.');
            }
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
        cancelCall() {
            this.stopRingtone();
            window.Echo.private(`chat.${chatId}`).whisper('call-cancelled', { chatId });
            this.resetCallUI();

        },
        async acceptCall() {
            if (this._joining) return;
            this._joining = true;

            try {
                this.stopRingtone();
                this.callState = 'connecting';
                this.callStatusText = 'Connecting…';

                const res = await axios.post(`/chat/${chatId}/generate-token`);
                const { token, channel, uid } = res.data;

                const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });
                client.on("user-published", async (user, mediaType) => {
                    await client.subscribe(user, mediaType);
                    if (mediaType === "video") {
                        setTimeout(() => {
                            const remoteDiv = document.getElementById('remote-media');
                            if (remoteDiv) {
                                remoteDiv.innerHTML = '';
                                user.videoTrack.play(remoteDiv);
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

            if (this.timerInterval) clearInterval(this.timerInterval);
            this.callDuration = 0;
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
            this.cleanupMedia();
            document.getElementById('local-media').innerHTML = '';
            document.getElementById('remote-media').innerHTML = '';

            $('#callModal').modal('hide');
        },
        rejectCall() {
            window.Echo.private(`chat.${chatId}`)
                .whisper('call-rejected', { chatId });
            this.resetCallUI();
        },
        toggleMute() {
            this.isMuted = !this.isMuted;  // Toggle state

            // Dono possible track references
            let audioTrack = this.localAudioTrack || this.micTrack;

            if (!audioTrack) {
                console.warn('No audio track available for mute/unmute');
                return;
            }

            try {
                if (this.isMuted) {
                    // Mute – simple disable
                    audioTrack.setEnabled(false);
                    console.log('Mic muted successfully');
                } else {
                    // Unmute – critical part: Purana track close + fresh create + republish
                    console.log('Unmuting: Stopping & replacing old track...');

                    // Purana track stop/close
                    audioTrack.stop();
                    audioTrack.close();

                    // Fresh new mic track banao
                    AgoraRTC.createMicrophoneAudioTrack()
                        .then(newTrack => {
                            // Update reference
                            this.localAudioTrack = newTrack;
                            this.micTrack = newTrack;  // Expert side ke liye bhi

                            // Publish new track
                            if (this.agoraClient) {
                                this.agoraClient.publish(newTrack)
                                    .then(() => {
                                        console.log('Fresh mic track published after unmute – success!');
                                    })
                                    .catch(err => {
                                        console.error('Republish failed:', err);
                                    });
                            } else {
                                console.warn('No agoraClient found for republish');
                            }
                        })
                        .catch(err => {
                            console.error('Failed to create fresh mic track:', err);
                            alert('Mic access failed. Please check permissions.');
                        });
                }
            } catch (err) {
                console.error('toggleMute error:', err);
            }
        },

        cleanupMedia() {
            console.log('Cleaning up all media resources...');

            // 1. All known tracks stop + close
            [this.localAudioTrack, this.localVideoTrack, this.micTrack, this.cameraTrack]
                .filter(t => t)
                .forEach(track => {
                    try {
                        track.stop();
                        track.close();
                        console.log(`Track stopped & closed: ${track.trackMediaType || 'unknown'}`);
                    } catch (e) {
                        console.warn('Track stop/close failed:', e);
                    }
                });

            // 2. Clear all references
            this.localAudioTrack = this.localVideoTrack = this.micTrack = this.cameraTrack = null;

            // 3. Force release ALL devices at browser level (most important fix)
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ audio: true, video: true })
                    .then(stream => {
                        stream.getTracks().forEach(track => {
                            track.stop();
                            track.enabled = false;
                        });
                        console.log('All browser media devices forcefully released');
                    })
                    .catch(err => {
                        console.log('Force release getUserMedia failed (normal if no permission):', err);
                    });
            }

            if (this.agoraClient) {
                try {
                    this.agoraClient.leave();
                    this.agoraClient.removeAllListeners();
                    this.agoraClient = null;
                    console.log('Agora client fully left & cleared');
                } catch (e) {
                    console.warn('Agora leave failed:', e);
                }
            }
            ['local-media', 'remote-media'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.innerHTML = '';
            });
            this.callState = 'idle';
            this.inCall = false;
            this.callInitiator = null;
            this.callerInfo = null;
            this.callDuration = 0;
            this.videoEnabled = false;
            this.isMuted = false;

            console.log('Media cleanup complete – browser devices should be free now!');
        },
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

            const endBtn = document.querySelector('.chat-header .btn-danger .btn-hide-from');
            if (endBtn) endBtn.style.display = 'none';
        },

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
                                this.chatEnded = true;
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
                    tickSpan.style.color = '#34b7f1';
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
        activeExperts: [],
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
        _dummy: false,
        formattedDuration: 0,

        setupCallListenerForExpert(expertId) {
            const channel = `admin-chat.${expertId}`;
            console.log('[Admin] Setting up call & message listener for channel:', channel);

            // Purana channel leave kar do (duplicate avoid)
            if (this.currentChannel && this.currentChannel !== channel) {
                window.Echo.leave(this.currentChannel);
                console.log('[Admin] Left old channel:', this.currentChannel);
            }

            this.currentChannel = channel;

            const privateChannel = window.Echo.private(channel);

            // Subscription success/fail debug
            privateChannel.subscribed(() => {
                console.log('[Admin DEBUG] ✅ FULLY SUBSCRIBED to:', channel);
            }).error((error) => {
                console.error('[Admin DEBUG] ❌ SUBSCRIPTION ERROR on channel', channel, error);
            });

            privateChannel
                .listenForWhisper('incoming-call', (data) => {
                    this.handleIncomingCall(data);
                })
                .listenForWhisper('new-message-from-expert', (data) => {
                    console.log('[ADMIN] Real-time message from expert via whisper!', data);
                    if (data.message) {
                        this.appendMessage(data.message);
                        this.scrollToBottom();
                    } else {
                        console.warn('[Admin] Whisper data missing message object', data);
                    }
                })
                .listenForWhisper('call-accepted', async () => {
                    if (this._joining) return;
                    this._joining = true;
                    try {
                        this.callStatusText = 'Connecting...';
                        this.callState = 'connecting';


                        if (!this.agoraClient) {
                            this.agoraClient = this.createAgoraClient();
                        }

                        const res = await axios.post(`/expert/massages/admin-chat/${chatId}/generate-token`);
                        const { token, channel, uid, app_id } = res.data;

                        await this.agoraClient.join(app_id || window.AGORA_APP_ID, channel, token, uid);

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
                        this.stopRingtone();
                        this.startTimer();

                    } catch (err) {
                        console.error('❌ User side Agora join failed:', err);
                        toastr.error('Connection failed: ' + err.message);
                        this.endCall();
                    } finally {
                        this._joining = false;
                    }
                })

                .listenForWhisper('call-rejected', () => this.handleCallRejected())
                .listenForWhisper('call-ended', () => this.endCall())
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
                })
                .listenForWhisper('call-cancelled', () => {
                    toastr.info('Call cancelled');
                    this.endCall();
                });
        },
        initiateCall(withVideo) {
            if (this.inCall || !this.selectedExpertId) return;

            this.isVideo = withVideo;
            this.inCall = true;
            this.callInitiator = 'admin';
            this.callState = 'ringing';
            this.callStatusText = 'Calling Expert...';
            this.callerInfo = { avatar: window.ADMIN_AVATAR, name: 'You (Admin)' };

            const modalEl = document.getElementById('callModal');
            this.callBootstrapModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            this.callBootstrapModal.show();

            this.playRingtone();

            window.Echo.private(`admin-chat.${this.selectedExpertId}`).whisper('incoming-call', {
                from: 'admin',
                type: withVideo ? 'video' : 'voice',
                chatId: this.selectedExpertId
            });
        },
        handleIncomingCall(data) {
            if (data.from === 'expert') {
                this.callInitiator = 'expert';
            } else if (data.from === 'admin') {
                this.callInitiator = 'admin';
            }

            this.callState = 'incoming';
            this.isVideo = data.type === 'video';
            this.callStatusText = `Incoming call from ${this.callInitiator}`;
            this.callerInfo = {
                avatar: data.avatar || '/assets/front-end/img/placeholder/user.png',
                name: data.name || this.callInitiator
            };

            const modalEl = document.getElementById('callModal');
            if (!modalEl) return console.error('Call modal not found in DOM!');

            $('#callModal').modal({ backdrop: 'static', keyboard: false }).modal('show');
            this.playRingtone();
        },

        handleCallRejected() {
            this.callStatusText = 'Call Rejected';
            this.stopRingtone();
            this.endCall();
        },
        init() {
            console.log('[AdminChat] Component initialized');

            this.activeExperts = window.INITIAL_EXPERTS || [];
            this.searchResults = window.ALL_EXPERTS;

            this.scrollToBottom();
            this.markAllAsRead();
            if (this.selectedExpertId) {
                this.setupCallListenerForExpert(this.selectedExpertId);
            }
        },

        ensureAgoraClient() {
            if (this.agoraClient) {
                console.log('Agora client already exists, reusing');
                return this.agoraClient;
            }

            const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

            client.on("user-published", async (user, mediaType) => {
                console.log('Remote user published:', user.uid, mediaType);

                try {
                    await client.subscribe(user, mediaType);

                    if (mediaType === "video") {
                        this.$nextTick(() => {
                            const remoteDiv = document.getElementById('remote-media');
                            if (remoteDiv) {
                                remoteDiv.innerHTML = '';
                                user.videoTrack.play(remoteDiv);
                                console.log('Remote video playing in remote-media');
                            } else {
                                console.warn('remote-media div not found');
                            }
                        });
                    }

                    if (mediaType === "audio") {
                        user.audioTrack.play();
                        console.log('Remote audio playing');
                    }
                } catch (err) {
                    console.error('Subscribe failed for remote user:', user.uid, err);
                }
            });

            this.agoraClient = client;
            console.log('Agora client created with remote tracks listener');
            return client;
        },
        createAgoraClient() {
            if (this.agoraClient) {
                console.log('Agora client already exists, reusing');
                return this.agoraClient;
            }

            const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

            client.on("user-published", async (user, mediaType) => {
                console.log('Remote user published:', user.uid, mediaType);
                await client.subscribe(user, mediaType);

                if (mediaType === "video") {
                    this.$nextTick(() => {
                        const remoteDiv = document.getElementById('remote-media');
                        if (remoteDiv) {
                            remoteDiv.innerHTML = '';
                            user.videoTrack.play(remoteDiv);
                            console.log('Remote video playing');
                        } else {
                            console.warn('remote-media div not found');
                        }
                    });
                }

                if (mediaType === "audio") {
                    user.audioTrack.play();
                    console.log('Remote audio playing');
                }
            });

            this.agoraClient = client;
            console.log('Agora client created with remote listener');
            return client;
        },
        async acceptCall() {
            if (this._joining || !this.selectedExpertId) return;
            this._joining = true;

            try {
                this.callState = 'connecting';
                this.callStatusText = 'Connecting...';

                const res = await axios.post(`/expert/massages/admin-chat/${this.selectedExpertId}/generate-token`);
                const { token, channel, uid, app_id } = res.data;

                const client = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });

                client.on('user-published', async (user, mediaType) => {
                    await client.subscribe(user, mediaType);
                    if (mediaType === 'video') {
                        setTimeout(() => {
                            const remoteDiv = document.getElementById('remote-media');
                            if (remoteDiv) {
                                remoteDiv.innerHTML = '';
                                user.videoTrack.play(remoteDiv);
                            }
                        }, 500);
                    }
                    if (mediaType === 'audio') {
                        user.audioTrack.play();
                    }
                });

                await client.join(window.AGORA_APP_ID || app_id, channel, token, uid);

                const tracks = await AgoraRTC.createMicrophoneAndCameraTracks();
                const micTrack = tracks[0];
                const camTrack = this.isVideo ? tracks[1] : null;

                if (camTrack) camTrack.play(document.getElementById('local-media'));

                const publishTracks = [micTrack];
                if (camTrack) publishTracks.push(camTrack);

                await client.publish(publishTracks);

                this.agoraClient = client;
                this.localAudioTrack = micTrack;
                this.localVideoTrack = camTrack;
                this.videoEnabled = !!camTrack;
                this.callState = 'connected';
                this.callStatusText = 'Connected';
                this.startTimer();

                window.Echo.private(`admin-chat.${this.selectedExpertId}`).whisper('call-accepted', { chatId: this.selectedExpertId });

            } catch (err) {
                console.error('❌ Admin accept call failed:', err);
                alert('Call failed: ' + (err.message || 'Unknown error'));
                this.endCall();
            } finally {
                this._joining = false;
            }
        },

        cancelCall() {
            this.stopRingtone();
            window.Echo.private(`admin-chat.${this.selectedExpertId}`).whisper('call-cancelled', { chatId: this.selectedExpertId });
            this.endCall();
        },
        openChat(expertId, name, avatar) {
            this.showSearchModal = false;
            this.setupCallListenerForExpert(expertId);

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

            this.currentChannel = `admin-chat.${expertId}`;



            window.Echo.private(this.currentChannel)


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
        get formattedDuration() {
            const mins = Math.floor(this.callDuration / 60);
            const secs = this.callDuration % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },
        startTimer() {
            this.callDuration = 0;
            this.formattedDuration = '00:00';

            if (this.timerInterval) clearInterval(this.timerInterval);

            this.timerInterval = setInterval(() => {
                this.callDuration++;

                const mins = Math.floor(this.callDuration / 60);
                const secs = this.callDuration % 60;

                this.formattedDuration =
                    `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;

                console.log('Timer tick:', this.callDuration, this.formattedDuration);
            }, 1000);
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
        rejectCall() {
            window.Echo.private(`admin-chat.${this.selectedExpertId}`).whisper('call-rejected', { chatId: this.selectedExpertId });
            this.endCall();
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

        hangUp() {
            window.Echo.private(`admin-chat.${this.selectedExpertId}`).whisper('call-ended', { chatId: this.selectedExpertId });
            this.endCall();
        },

        toggleMute() {
            this.isMuted = !this.isMuted;
            if (this.localAudioTrack) {
                this.localAudioTrack.setEnabled(!this.isMuted);
            }
        },

        toggleVideo() {
            if (this.localVideoTrack) {
                this.videoEnabled = !this.videoEnabled;
                this.localVideoTrack.setEnabled(this.videoEnabled);
            }
        },

        startTimer() {
            this.callDuration = 0;
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => {
                this.callDuration++;
                console.log('Admin timer tick:', this.callDuration);
            }, 1000);
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
        _dummy: false,


        initiateCall(withVideo) {
            console.log('Expert initiating call → showing modal immediately');
            if (this.inCall) return;

            this.isVideo = withVideo;
            this.inCall = true;
            this.callInitiator = 'expert';
            this.callState = 'ringing';
            this.callStatusText = 'Calling...';
            this.callerInfo = { avatar: window.AUTH_USER_AVATAR, name: 'Expert' };

            $('#callAdminModal').modal('show');


            this.playRingtone();

            window.Echo.private(`admin-chat.${expertId}`).whisper('incoming-call', {
                from: 'expert',
                type: withVideo ? 'video' : 'voice',
                chatId: expertId
            });

            console.log('Whisper sent to channel admin-chat.' + expertId + ' with from: expert');
        },


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
                }).listenForWhisper('incoming-call', (data) => {
                    if (data.from === 'admin') {
                        this.callInitiator = 'admin';
                        this.callState = 'incoming';
                        this.isVideo = data.type === 'video';
                        this.callStatusText = 'Incoming Call from admin';
                        this.callerInfo = { avatar: '/assets/front-end/img/placeholder/user.png', name: 'admin' };

                        $('#callAdminModal').modal('show');

                        this.playRingtone();
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
                            this.agoraClient = this.createAgoraClient();
                        }

                        const res = await axios.post(`/expert/massages/admin-chat/${expertId}/generate-token`);
                        const { token, channel, uid, app_id } = res.data;

                        await this.agoraClient.join(app_id || window.AGORA_APP_ID, channel, token, uid);

                        let tracks = [];
                        try {
                            if (this.isVideo) {
                                tracks = await AgoraRTC.createMicrophoneAndCameraTracks();
                            } else {
                                const audio = await AgoraRTC.createMicrophoneAudioTrack();
                                tracks = [audio];
                            }
                        } catch (e) {
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
                        console.error('❌ Admin side Agora join failed:', err);
                        toastr.error('Connection failed: ' + err.message);
                        this.endCall();
                    } finally {
                        this._joining = false;
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
                });

        },
        loadInitialMessages() {
            axios.get('expert/massages/admin-chat/messages')
                .then(res => {
                    res.data.messages.forEach(msg => this.appendMessage(msg));
                    this.scrollToBottom();
                });
        },
        createAgoraClient() {
            if (this.agoraClient) {
                console.log('Agora client already exists, reusing');
                return this.agoraClient;
            }

            const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

            client.on("user-published", async (user, mediaType) => {
                console.log('Remote user published:', user.uid, mediaType);
                await client.subscribe(user, mediaType);

                if (mediaType === "video") {
                    this.$nextTick(() => {
                        const remoteDiv = document.getElementById('remote-media');
                        if (remoteDiv) {
                            remoteDiv.innerHTML = '';
                            user.videoTrack.play(remoteDiv);
                            console.log('Remote video playing');
                        } else {
                            console.warn('remote-media div not found');
                        }
                    });
                }

                if (mediaType === "audio") {
                    user.audioTrack.play();
                    console.log('Remote audio playing');
                }
            });

            this.agoraClient = client;
            console.log('Agora client created with remote listener');
            return client;
        },

        ensureAgoraClient() {
            if (this.agoraClient) {
                console.log('Agora client already exists, reusing');
                return this.agoraClient;
            }

            const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

            // 🔥 Remote tracks listener – yeh sabse important hai
            client.on("user-published", async (user, mediaType) => {
                console.log('Remote user published → subscribing:', user.uid, mediaType);

                try {
                    await client.subscribe(user, mediaType);

                    if (mediaType === "video") {
                        await this.$nextTick();
                        const remoteDiv = document.getElementById('remote-media');
                        if (remoteDiv) {
                            remoteDiv.innerHTML = '';
                            user.videoTrack.play(remoteDiv);
                            console.log('✅ Remote video playing in remote-media');
                        } else {
                            console.error('remote-media div not found!');
                        }
                    }

                    if (mediaType === "audio") {
                        user.audioTrack.play();
                        console.log('Remote audio started');
                    }
                } catch (subErr) {
                    console.error('Subscribe failed for remote user:', user.uid, subErr);
                }
            });

            this.agoraClient = client;
            console.log('Agora client created with remote tracks listener');
            return client;
        },
        async acceptCall() {
            if (this._joining) return;
            this._joining = true;

            try {
                this.stopRingtone();
                this.callState = 'connecting';
                this.callStatusText = 'Connecting...';
                this.agoraClient = this.ensureAgoraClient();
                const res = await axios.post(`/expert/massages/admin-chat/${expertId}/generate-token`);
                const { token, channel, uid, app_id } = res.data;

                const client = AgoraRTC.createClient({ mode: 'rtc', codec: 'vp8' });


                await this.agoraClient.join(app_id || window.AGORA_APP_ID, channel, token, uid);

                let tracks = [];
                try {
                    if (this.isVideo) {
                        tracks = await AgoraRTC.createMicrophoneAndCameraTracks();
                    } else {
                        tracks = [await AgoraRTC.createMicrophoneAudioTrack()];
                    }
                } catch (e) {
                    throw new Error("Could not access mic/camera");
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

                window.Echo.private(`admin-chat.${this.selectedExpertId || expertId}`).whisper('call-accepted', {});

            } catch (err) {
                console.error('❌ Call failed:', err);
                alert('Call failed: ' + (err.message || 'Unknown error'));
                this.endCall();
            } finally {
                this._joining = false;
            }
        },

        cancelCall() {
            window.Echo.private(`admin-chat.${expertId}`).whisper('call-cancelled', { chatId: expertId });
            this.endCall();
        },

        rejectCall() {
            window.Echo.private(`admin-chat.${expertId}`).whisper('call-rejected', { chatId: expertId });
            this.endCall();
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

            $('#callAdminModal').modal('hide');

        },

        hangUp() {
            window.Echo.private(`admin-chat.${expertId}`).whisper('call-ended', { chatId: expertId });
            this.endCall();
        },
        get formattedDuration() {
            const mins = Math.floor(this.callDuration / 60);
            const secs = this.callDuration % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },
        startTimer() {
            this.callDuration = 0;

            if (this.timerInterval) {
                clearInterval(this.timerInterval);
            }

            this.timerInterval = setInterval(() => {
                this.callDuration++;
                console.log('Timer tick:', this.callDuration, this.formattedDuration);
            }, 1000);
        },

        toggleMute() {
            this.isMuted = !this.isMuted;
            if (this.localAudioTrack) {
                this.localAudioTrack.setEnabled(!this.isMuted);
            }
        },

        toggleVideo() {
            if (this.localVideoTrack) {
                this.videoEnabled = !this.videoEnabled;
                this.localVideoTrack.setEnabled(this.videoEnabled);
            }
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
                console.log('[Expert] Message sent successfully:', res.data);

                this.newMessage = '';
                this.selectedFile = null;
                document.getElementById('imageInput').value = '';

                // Expert ke apne chat mein append (yeh already hai)
                this.appendMessage(res.data.message_data);

                // 🔥 Admin ko real-time whisper bhej do
                window.Echo.private(`admin-chat.${expertId}`).whisper('new-message-from-expert', {
                    message: res.data.message_data  // pura message object bhej do
                });

                console.log('[Expert] Whisper sent to admin on channel: admin-chat.' + expertId);
            }).catch(err => {
                console.error('[Expert] Send failed:', err);
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
            axios.post('/expert/massages/admin-chat/mark-read');
        },

    }
}

document.addEventListener('alpine:init', () => {
    window.chatComponent = chatComponent;
    window.expertChatComponent = expertChatComponent;
    window.expertAdminChatComponent = expertAdminChatComponent;

    Alpine.data('adminExpertChatComponent', adminExpertChatComponent);
});