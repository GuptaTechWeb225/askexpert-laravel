<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ask Expert - Fullscreen Chat</title>

    <link rel="apple-touch-icon" sizes="180x180" href="{{ $web_config['fav_icon']['path'] }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $web_config['fav_icon']['path'] }}">

    <link rel="stylesheet" href="{{ theme_asset('public/assets/front-end/css/theme.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('public/assets/front-end/css/cat-chatboat.css') }}">
    <link rel="stylesheet" href="{{dynamicAsset(path:'public/assets/back-end/vendor/fontawesome-free/css/all.min.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/bootstrap.min.css')}}">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/style.css') }}">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
      :root {
        --base: {{$web_config['primary_color'] ?? '#000000'}};
      }
    </style>
  <style>

.chat-fullscreen {
  height: 100vh;
  height: 100dvh;
  display: flex;
  flex-direction: column;
  background-color: #fff;
}

/* HEADER & FOOTER fixed */
.chat-header {
  flex-shrink: 0;
}

.chat-footer {
  flex-shrink: 0;
}

.ec-expert-chat-area-1 {
  flex: 1;               
  overflow-y: auto;
  padding: 20px;
  background-color: #f9f9f9;
}

#bot-typing-indicator {
  flex-shrink: 0;
}

.chat-fullscreen {
  min-height: 100svh;
}

 
  </style>
</head>

<body>

  <div class="chat-fullscreen"  x-data="categoryBotChatbot()" x-init="init()">
    <!-- Header -->
    <div class="chat-header">
      <div class="bot-info">
        <img src="{{ asset('assets/front-end/img/chat-avtar.png') }}" alt="Bot Avatar">
        <div class="ml-2">
          <div class="bot-name">AskExpert Chat Bot</div>
          <div class="bot-status"><span class="active-dot"></span> Active</div>
        </div>
      </div>
    </div>

  
    <!-- CHAT AREA -->
    <div class="ec-expert-chat-area-1" id="bot-chat-area"> </div>
<div id="bot-typing-indicator" class="mb-3 px-3" style="display:none;">
        <img src="{{ asset('assets/front-end/img/chat-avtar.png') }}" class="ec-message-avatar me-3">
        <div>
            <p class="mb-0 text-muted small">AskExpert Chatbot</p>
            <div class="typing-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
    </div>
   

    <div class="chat-footer ec-expert-input-footer" id="bot-chat-footer">
        <input
            type="text"
            x-model="newMessage"
            @keyup.enter="sendMessage()"
            placeholder="Type your question here..."
            id="bot-chat-input"
            :disabled="escalate">

        <button
            class="ec-icon-btn ec-send-btn"
            @click="sendMessage()"
            :disabled="escalate || !newMessage.trim()">
            <i class="fa-solid fa-paper-plane"></i>
        </button>
    </div>
  </div>

<script>
    window.isCustomerLoggedIn = {{auth('customer') -> check() ? 'true' : 'false'}};
    window.pendingQuestionForPayment = '';
</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('categoryBotChatbot', () => ({
            newMessage: '',
            sessionId: null,
            originalQuestion: '',
            messageCount: 0,
            escalate: false,
            hasInitMessage: false,
            categoryName: 'general',

            init() {
                if (!this.hasInitMessage) {
                    this.appendBotMessage(
                        'How can I assist you today? What issue are you facing?'
                    );
                    this.hasInitMessage = true;
                }
                this.scrollToBottom();
            },

            scrollToBottom() {
                const chatArea = document.getElementById('bot-chat-area');
                if (chatArea) {
                    chatArea.scrollTop = chatArea.scrollHeight;
                }
            },

            appendUserMessage(text) {
                const chatArea = document.getElementById('bot-chat-area');

                const msgDiv = document.createElement('div');
                msgDiv.className = 'ec-message-container ec-user-side';
                msgDiv.innerHTML = `
                
                <div class="ec-message ec-user">
                    <p>${text}</p>
                </div>
                 <img src="{{ asset('assets/front-end/img/placeholder/user.png') }}"
                     class="ec-message-avatar me-3">
            `;

                chatArea.appendChild(msgDiv);
                this.scrollToBottom();
            },

            appendBotMessage(text) {
                const chatArea = document.getElementById('bot-chat-area');

                const msgDiv = document.createElement('div');
                msgDiv.className = 'ec-message-container ec-bot-side mb-4';

                msgDiv.innerHTML = `
                <img src="{{ asset('assets/front-end/img/chat-avtar.png') }}"
                     class="ec-message-avatar me-3">

                <div class="d-flex flex-column">
                    <p class="mb-1 text-muted" style="font-size:0.7rem">
                        AskExpert AI Chatbot
                    </p>
                    <div class="ec-message ec-bot">
                        <p>${text}</p>
                    </div>
                </div>
            `;

                chatArea.appendChild(msgDiv);
                this.scrollToBottom();
            },

            showTyping() {
                document.getElementById('bot-typing-indicator').style.display = 'flex';
            },

            hideTyping() {
                document.getElementById('bot-typing-indicator').style.display = 'none';
            },

            async sendMessage() {
                const message = this.newMessage.trim();
                if (!message || this.escalate) return;

                if (!this.originalQuestion) {
                    this.originalQuestion = message;
                }

                this.appendUserMessage(message);
                this.newMessage = '';
                this.showTyping();

                const isFirst = !this.sessionId;

                const url = isFirst ?
                    "{{ route('chatbot.start') }}" :
                    "{{ route('chatbot.message') }}";

                const payload = isFirst ?
                    {
                        question: message
                    } :
                    {
                        session_id: this.sessionId,
                        message
                    };

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();

                    this.sessionId = data.session_id ?? this.sessionId;
                    this.messageCount++;

                    let botText = data.bot_message;

                    if (data.escalate || this.messageCount >= 6) {
                        botText = `
                       OK. Got it. I'm sending you to a secure page to join askExpert. While you're filling out that form, I'll tell the Expert about your situation and then connect you two.
                        <a href="javascript:void(0)"
                           @click="proceedToPayment()"
                           style="color:#0066cc; font-weight:bold">
                           Continue >>
                        </a>
                    `;
                        this.escalate = true;
                    }

                    this.hideTyping();
                    this.appendBotMessage(botText);

                } catch (e) {
                    this.hideTyping();
                    this.appendBotMessage('Network error. Please try again.');
                }
            },

            proceedToPayment() {

                if (!window.isCustomerLoggedIn) {
                    window.pendingQuestionForPayment = this.originalQuestion;
                    var emailModal = new bootstrap.Modal(document.getElementById('guestEmailModal'));
                    emailModal.show();
                    return;
                }
                fetch("{{ route('ask.expert.start') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            question: this.originalQuestion
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.payment_url;
                        }
                    });
            }
        }));
    });
</script>
</body>

</html>