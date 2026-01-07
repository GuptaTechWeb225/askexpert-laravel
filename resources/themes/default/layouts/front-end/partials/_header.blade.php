@php($announcement = getWebConfig(name: 'announcement'))

@if (isset($announcement) && $announcement['status'] == 1)
<div class="text-center position-relative px-4 py-1 d--none" id="announcement"
    style="background-color: {{ $announcement['color'] }};color:{{$announcement['text_color']}}">
    <span>{{ $announcement['announcement'] }} </span>
    <span class="__close-announcement web-announcement-slideUp">X</span>
</div>
@endif
@vite(['resources/js/app.js'])

<div class="header-top">
    <a class="text-decoration-none text-white"
        href="{{ 'mailto:'.getWebConfig(name: 'company_email') }}">
        <span><i class="fa fa-envelope  me-2 mt-2 mb-2"></i> {{getWebConfig(name: 'company_email')}} </span>
    </a>
</div>

<header>
    <nav class="navbar navbar-expand-lg custom-navbar fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img class="__inline-11" src="{{ getStorageImages(path: $web_config['web_logo'], type: 'logo') }}" alt="{{$web_config['company_name']}}">
            </a>


            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto gap-3">
                    @if(auth('customer')->check())
                    <li class="nav-item"><a class="nav-link {{request()->is('my/home') ? 'active' : ''}}" href="{{ route('user.home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link {{request()->is('my/questions') ? 'active' : ''}}" href="{{ route('user.questions') }}">My Questions</a></li>
                    <li class="nav-item"><a class="nav-link {{request()->is('my/experts') ? 'active' : ''}}" href="{{route('user.experts')}}">My Experts</a></li>
                    <li class="nav-item"><a class="nav-link {{request()->is('help') ? 'active' : ''}}" href="{{ route('help') }}">Help</a></li>
                    @else
                    <li class="nav-item"><a class="nav-link {{request()->is('/') ? 'active' : ''}}" href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link {{request()->is('about-us') ? 'active' : ''}}" href="{{ route('about-us') }}">About Us</a></li>
                    <li class="nav-item"><a class="nav-link {{request()->is('price') ? 'active' : ''}}" href="{{route('price')}}">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link {{request()->is('expert') ? 'active' : ''}}" href="{{ route('expert') }}">Become an Expert</a></li>
                    <li class="nav-item"><a class="nav-link {{request()->is('help') ? 'active' : ''}}" href="{{ route('help') }}">Help</a></li>
                    @endif
                </ul>
            </div>

            <div class="d-flex align-items-center ms-auto gap-2">
                @if(auth('customer')->check())
                <div class="dropdown">
                    <a class="navbar-tool" type="button" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <div class="navbar-tool-icon-box bg-secondary">
                            <div class="navbar-tool-icon-box bg-secondary">
                                <img class="img-profile rounded-circle __inline-14" alt=""
                                    src="{{ getStorageImages(path: auth('customer')->user()->image_full_url, type: 'avatar') }}">
                            </div>
                        </div>
                        <div class="navbar-tool-text text-dark">
                            <small class=" text-primary">
                                {{ translate('hello')}}, {{ Str::limit(auth('customer')->user()->f_name, 10) }}
                            </small>
                            {{ translate('dashboard')}}
                        </div>
                    </a>
                    <div class="dropdown-menu __auth-dropdown dropdown-menu-{{Session::get('direction') === " rtl" ? 'left' : 'right'
                        }}" aria-labelledby="dropdownMenuButton">

                        <a class="dropdown-item " href="{{route('user-account')}}"> {{
                            translate('my_Profile')}}</a>

                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{route('customer.auth.logout')}}">{{
                            translate('logout')}}</a>
                    </div>
                </div>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                @else
                <a href="{{ route('customer.auth.login') }}" class="btn btn-login">Log in</a>
                @endif

            </div>
        </div>
    </nav>
</header>

<div class="chat-floating-icon">
    <div class="chat-bubble-img">
        <img src="{{ asset('assets/front-end/img/msg-hii.png') }}" alt="Chat Bubble">
    </div>
    <div class="avatar-ring" id="chat-icon">
        <img src="{{ asset('assets/front-end/img/chat-avtar.png') }}" alt="User Avatar">
    </div>
</div>
<div class="chat-popup" id="chat-popup" x-data="categoryBotChatbot()" x-init="init()">

    <!-- HEADER -->
    <div class="chat-header">
        <div class="bot-info">
            <img src="{{ asset('assets/front-end/img/chat-avtar.png') }}" class="avatar">
            <div class="bot-details">
                <div class="bot-name">AskExpert Chat Bot</div>
                <div class="bot-status"><span class="active-dot"></span> Active</div>
            </div>
        </div>
        <div class="header-icons">
            <span class="tooltip-icon close-btn" id="bot-chat-close">X</span>
        </div>
    </div>

    <!-- CHAT AREA -->
    <div class="ec-expert-chat-area" id="bot-chat-area"></div>

    <!-- TYPING -->
    <div id="bot-typing-indicator" class="mb-3 px-3" style="display:none;">
        <img src="{{ asset('assets/front-end/img/chat-avtar.png') }}" class="ec-message-avatar me-3">
        <div>
            <p class="mb-0 text-muted small">AskExpert Chatbot</p>
            <div class="typing-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
    </div>

    <!-- INPUT -->
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


@push('script')

<script>
    window.isCustomerLoggedIn = {{ auth('customer')->check() ? 'true' : 'false' }};
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

            const url = isFirst
                ? "{{ route('chatbot.start') }}"
                : "{{ route('chatbot.message') }}";

            const payload = isFirst
                ? { question: message }
                : { session_id: this.sessionId, message };

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
                        OK. Got it. I'm sending you to a secure page to join askExpert.
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
@endpush