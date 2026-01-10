@extends('layouts.back-end.app-expert')
@section('title', translate('chat_Bot'))
@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset(path:'public/assets/back-end/vendor/fontawesome-free/css/all.min.css')}}">
<meta name="csrf-token" content="{{ csrf_token() }}">


@endpush

@section('content')

<style>
    #video-container {
        background: #000;
        border-radius: 12px;
        overflow: hidden;
        margin: 15px 0;
        position: relative;
        height: 400px;
    }

    #remote-media {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #local-media {
        width: 150px;
        height: 150px;
        border: 3px solid white;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    #remote-media video,
    #local-media video {
        position: relative !important;
        object-fit: cover !important;
    }

    #remote-media {
        background-color: #000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
<div class="content container-fluid">
    <div class="whatsapp-container" x-data="expertChatComponent({{ $chat->id }})" x-init="init()">
        <div class="chat-header d-flex justify-content-between px-3 py-3 text-white">
            <div class="bot-info d-flex align-items-center w-100">
                <div class="avatar-wrapper">
                    <img src="{{ getStorageImages(path: $customer->image_full_url, type: 'avatar') }}" class="avatar">
                </div>
                <div class="bot-details w-100 ms-2">
                    <div class="bot-name">{{ $customer->f_name }} {{ $customer->l_name }}</div>
                    <small class="text-white" x-show="customerTyping" style="display:none;">typing...</small>
                </div>
            </div>
            <div class="ms-auto pe-3 d-flex align-items-center gap-3">
                @if($chat->status !== 'ended')
                <button class="btn btn-primary btn-sm" @click="initiateCall(false)">
                    <i class="fa-solid fa-phone"></i>
                </button>
                <button class="btn btn-success btn-sm" @click="initiateCall(true)">
                    <i class="fa-solid fa-video"></i>
                </button>
                <button class="btn btn-danger btn-sm" @click="endChat()">
                    <i class="fa-solid fa-phone-slash"></i>
                </button>
                @endif
            </div>
        </div>
        <div class="chat-body p-3" id="messages" style="height: 500px; overflow-y: auto;">


            @foreach($messages as $msg)
            <div class="message-container {{ $msg->sender_type == 'expert' ? 'user-side' : '' }}" data-message-id="{{ $msg->id }}">
                <div class="message-bubble {{ $msg->sender_type == 'expert' ? 'user' : 'bot' }}">
                    @if(Str::startsWith($msg->message, 'chat-images/'))
                    <img src="{{ asset('storage/' . $msg->message) }}" style="max-width:200px; border-radius:10px;">
                    @else
                    {!! nl2br(e($msg->message)) !!}
                    @endif
                    <div class="message-meta">
                        <span>{{ \Carbon\Carbon::parse($msg->sent_at)->format('h:i A') }}</span>
                        @if($msg->sender_type == 'expert')
                        <span class="read-ticks">{{ $msg->is_read ? '✓✓' : '✓' }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
            @if($chat->status === 'ended')
            <div class="text-center py-5 my-4 bg-light rounded">
                <h5 class="text-muted mb-3">Chat has ended</h5>
                <p class="text-muted mb-0">This chat session is now closed.</p>
            </div>
            @endif
        </div>
        <div class="chat-footer d-flex p-2 gap-2" x-show="$store?.chatStatus?.status !== 'ended' && '{{ $chat->status }}' !== 'ended'">

            @if($chat->status !== 'ended')

            <input type="file" id="expertImageInput" style="display:none" @change="handleFileUpload">
            <button class="btn btn--light" @click="document.getElementById('expertImageInput').click()">
                <i class="fa-solid fa-paperclip"></i>
            </button>

            <input type="text" x-model="newMessage" @keyup.enter="sendMessage" @keyup="typingEvent"
                class="form-control" placeholder="Type message...">

            <button class="btn btn--primary" @click="sendMessage">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
            @endif

        </div>

      
    </div>



</div>

@endsection

@push('script_2')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endpush