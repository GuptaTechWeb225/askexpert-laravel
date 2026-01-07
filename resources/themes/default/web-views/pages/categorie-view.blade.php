@extends('layouts.front-end.app')

@section('title', $categorie->name)

@section('content')

@vite(['resources/js/app.js'])
<style>
    .ec-bot-header {
        background: #f8f9fa;
        padding: 12px 16px;
        border-bottom: 1px solid #eee;
        font-size: 0.95rem;
    }

    .typing-indicator-bot {
        background: #f8f9fa;
        padding: 12px 16px;
        border-top: 1px solid #eee;
    }

    .typing-dots span {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #999;
        margin: 0 3px;
        animation: typing 1.4s infinite ease-in-out;
    }

    .typing-dots span:nth-child(1) {
        animation-delay: 0s;
    }

    .typing-dots span:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-dots span:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-8px);
        }
    }

    .ec-user-side {
        text-align: right;
    }

    .ec-user {
        background: #dcf8c6;
        padding: 10px 14px;
        border-radius: 18px;
        display: inline-block;
        max-width: 80%;
    }

    input:disabled,
    button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
<div class="ec-hero-section hero-computer" style="background-image: url('{{ $categorie->cms_image_url }}');">
    <div class="ec-content-container">
        <div class="ec-ask-expert-container" x-data="categoryChatbot()" x-init="init()">
            <div class="ec-expert-header">
                <div class="ec-categories">
                    <div class="ec-dropdown">
                        <button class="ec-category-btn">{{ $categorie->name }}<i class="fa-solid fa-caret-down"></i></button>
                        <div class="ec-dropdown-content category-dropdown">
                            @foreach ($categories as $cat)
                            <a href="{{ route('category.view', $cat->id) }}">{{ $cat->name }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="ec-dropdown">
                        <button class="ec-more-btn">More <i class="fa-solid fa-caret-down"></i></button>
                        <div class="ec-dropdown-content">
                            @foreach ($categories as $cat)
                            <div class="ec-dropdown-column">
                                <h4>{{ $cat->name }}</h4>
                                @foreach($cat->sub_categorys as $sub)
                                <a href="{{ route('category.view', $cat->id) }}">{{ $sub }}</a>
                                @endforeach
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
                @if(!empty($expert))
                <div class="ec-expert-info">
                    <img src="{{ getStorageImages(path: $expert->image_full_url, type: 'avatar') }}"
                        alt="{{ $expert->f_name ?? 'Expert' }}"
                        class="ec-expert-avatar">

                    <div class="ec-expert-details">
                        <p class="ec-expert-name">
                            {{ trim(($expert->f_name ?? '') . ' ' . ($expert->l_name ?? '')) }}, {{ $expert->category?->name ?? 'General' }}
                        </p>
                        <!-- <p class="ec-expert-stats">
                            {{ $expert->chats->count() ?? 0 }} satisfied customers
                        </p> -->
                        <p class="ec-expert-specialty">
                            {{ $expert->primary_specialty 
                    ?? 'Certified expert with professional experience' }}
                        </p>
                    </div>
                </div>
                @else
                <div class="ec-expert-info">

                    <img src="https://i.pravatar.cc/150?img=16" alt="Dr. Andy" class="ec-expert-avatar">
                    <div class="ec-expert-details">
                        <p class="ec-expert-name">Alan, IT Director</p>
                        <p class="ec-expert-stats">2,865 satisfied customers</p>
                        <p class="ec-expert-specialty">
                            Computer specialist, MIT graduate, emphasis in Hardware, Networking, and Security
                        </p>
                    </div>
                </div>
                @endif

            </div>


            <div class="ec-expert-chat-area" id="ec-expert-chat-area">
            </div>
            <div id="typingIndicator" class=" mb-3 px-3" style="display:none;">
                <img src="{{ asset('assets/front-end/img/chat-avtar.png') }}" alt="Assistant" class="ec-message-avatar me-3">
                <div>
                    <p class="mb-0 text-muted small">AskExpert Chatbot, Assistant</p>
                    <div class="typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>

            <div class="ec-expert-input-footer start-chat" id="chatInputFooter">
                <input
                    type="text"
                    x-model="newMessage"
                    @keyup.enter="sendMessage()"
                    placeholder="Type your question here..."
                    id="userQuestion"
                    :disabled="escalate">
                <button
                    class="ec-icon-btn ec-send-btn"
                    @click="sendMessage()"
                    :disabled="escalate || !newMessage.trim()">
                    <i class="fa-solid fa-paper-plane material-icons"></i>
                </button>
            </div>


            <p class="ec-online-status">{{ $categorie->name }} expert is Online Now</p>
        </div>

        <div class="ec-hero-text">
            <h1>{{ \Illuminate\Support\Str::limit($categorie->cms_heading, 50) }}</h1>
            <p>{{ \Illuminate\Support\Str::limit($categorie->cms_description, 150) }}</p>
        </div>

    </div>
</div>

<section class="container slider-container my-5">
    <h2 class="section-title">Expert Categories</h2>
    <div class="swiper expert-categories-slider">
        <div class="swiper-wrapper">
            @foreach($categories as $cat)
            <div class="swiper-slide page-link">
                <img src="{{ $cat->icon_url }}" alt="{{ $cat->name ?? '' }}" class="card-image">
                <div class="slide-content">
                    <a href="{{ route('category.view', $cat->id) }}"> <strong class="d-block mb-2 text-dark">{{ $cat['name'] ?? '' }}</strong>
                    </a>
                    <span class="text-muted">
                        {{ $cat->experts_count ?? 1 }} Expert{{ ($cat->experts_count ?? 1) != 1 ? 's' : '' }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>


<!-- Popular Questions -->
<section class="container-fluid py-5">
    <div class="container">
        <h2 class="section-title">Popular questions at AskExpert</h2>
        <div class="row gx-4 gy-4">
            @foreach($popular_questions as $q)
            <div class="col-md-6 col-lg-3">
                <a href="{{ $q['link'] ?? '#' }}" class="questions-card position-relative text-white">
                    <div class="questions-card-bg">
                        <img src="{{ asset($q['image'] ?? '') }}" alt="{{ $q['title'] ?? '' }}" />
                    </div>
                    <div class="questions-card-overlay">
                        <div class="questions-card-content">
                            <h3>{{ $q['title'] ?? '' }}</h3>
                            <p>{{ $q['description'] ?? '' }}</p>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection

@push('script')

<script>
    window.isCustomerLoggedIn = {{ auth('customer')->check() ? 'true' : 'false' }};
    window.pendingQuestionForPayment = '';
</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('categoryChatbot', () => ({
            newMessage: '',
            sessionId: null,
            originalQuestion: '',
            messageCount: 0,
            escalate: false,
            hasInitMessage: false, // ← Naya flag: init message dikha ya nahi
            categoryName: '{{ $categorie->name }}',

            init() {
                // Sirf pehli baar init message dikhao
                if (!this.hasInitMessage) {
                    this.appendBotMessage(`How can I assist you with ${this.categoryName} today? What issue are you facing?`);
                    this.hasInitMessage = true;
                }
                this.scrollToBottom();
            },

            scrollToBottom() {
                const chatArea = document.getElementById('ec-expert-chat-area');
                if (chatArea) {
                    chatArea.scrollTop = chatArea.scrollHeight;
                }
            },

            appendUserMessage(text) {
                const chatArea = document.getElementById('ec-expert-chat-area');
                const msgDiv = document.createElement('div');
                msgDiv.className = 'ec-message-container ec-user-side';
                msgDiv.innerHTML = `<div class="ec-message ec-user"><p>${text}</p></div>`;
                chatArea.appendChild(msgDiv);
                this.scrollToBottom();
            },

            appendBotMessage(text) {
                const chatArea = document.getElementById('ec-expert-chat-area');

                const msgDiv = document.createElement('div');
                msgDiv.className = 'ec-message-container ec-bot-side mb-4';

                msgDiv.innerHTML = `      
    <img src="{{ asset('assets/front-end/img/chat-avtar.png') }}" alt="Assistant" class="ec-message-avatar me-3">
<div class="d-flex flex-column">

    <div class="px-3">
        <p class="mb-0 text-muted" style="font-size: 0.7rem; margin-top: -4px;">
            AskExpert AI Chatbot, {{ $categorie->name }} Assistant
        </p>
    </div>
    <div class="d-flex align-items-start px-2">
        <div class="ec-message ec-bot">
            <p>${text}</p>
        </div>
    </div>
</div>
 `;

                chatArea.appendChild(msgDiv);
                this.scrollToBottom();
            },

            showTyping() {
                document.getElementById('typingIndicator').style.display = 'flex';
                this.scrollToBottom();
            },

            hideTyping() {
                document.getElementById('typingIndicator').style.display = 'none';
            },

            async sendMessage() {
                let message = this.newMessage.trim();
                if (!message || this.escalate) return;

                if (!this.originalQuestion) {
                    this.originalQuestion = message;
                }

                this.appendUserMessage(message);
                this.newMessage = '';
                this.showTyping();

                const isFirstMessage = !this.sessionId;

                let url = isFirstMessage ?
                    "{{ route('chatbot.start') }}" :
                    "{{ route('chatbot.message') }}";

                let payload = isFirstMessage ? {
                    question: message
                } : {
                    session_id: this.sessionId,
                    message: message
                };

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    if (!response.ok) throw new Error('Network error');

                    const data = await response.json();

                    this.sessionId = data.session_id || this.sessionId;
                    this.messageCount++;

                    let botResponse = data.bot_message;

                    // Jab escalate ho → special clickable message
                    if (data.escalate || this.messageCount >= 6) {
                        botResponse = `OK. Got it. I'm sending you to a secure page to join askExpert. While you're filling out that form, I'll tell the <strong>${this.categoryName} Technician</strong> about your situation and then connect you two. <a href="javascript:void(0)" @click="proceedToPayment()" style="color:#0066cc; font-weight:bold; text-decoration:none;">Continue >></a>`;
                        this.escalate = true; // Input disable ho jaayega
                    }

                    this.hideTyping();
                    this.appendBotMessage(botResponse);

                } catch (error) {
                    console.error(error);
                    this.hideTyping();
                    this.appendBotMessage('Sorry, having trouble. <a href="javascript:void(0)" @click="proceedToPayment()" style="color:#0066cc; font-weight:bold;">Continue anyway >></a>');
                    this.escalate = true;
                }
            },

            proceedToPayment() {
                if (!this.originalQuestion) return;

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
                        if (data.success && data.payment_url) {
                            window.location.href = data.payment_url;
                        } else {
                            alert('Something went wrong. Please try again.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Network error.');
                    });
            }
        }));
    });
</script>

<style>
    .ec-user-side {
        text-align: right;
    }

    .ec-user {
        background: #dcf8c6;
        padding: 10px 14px;
        border-radius: 18px;
        display: inline-block;
        max-width: 100%;
    }

    /* Disabled input styling */
    input:disabled {
        background-color: #f0f0f0;
        opacity: 0.6;
        cursor: not-allowed;
    }

    button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
@endpush