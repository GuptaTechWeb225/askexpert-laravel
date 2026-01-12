@extends('layouts.back-end.app-expert')
@section('title', translate('chat_Bot'))
@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset(path:'public/assets/back-end/vendor/fontawesome-free/css/all.min.css')}}">
<meta name="csrf-token" content="{{ csrf_token() }}">


@endpush

@section('content')
<div class="content container-fluid">
    <div class="whatsapp-container" x-data="expertChatComponent({{ $chat->id }})" x-init="init()">
        <div class="chat-header d-flex justify-content-between px-3 py-3 text-white">
            <div class="bot-info d-flex align-items-center w-100">
                <div class="avatar-wrapper">
                    <img src="{{ getStorageImages(path: $customer?->image_full_url, type: 'avatar') }}" class="avatar">
                </div>
                <div class="bot-details w-100 ms-2">
                    <div class="bot-name">{{ $customer?->f_name }} {{ $customer?->l_name }}</div>
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
                <div class="dropdown">
                    <button class="btn btn-danger btn-sm" type="button"
                        id="chatActionDropdown" data-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu __auth-dropdown dropdown-menu-right" aria-labelledby="chatActionDropdown">
                        <li><a class="dropdown-item" href="#" @click.prevent="handleChatAction('block')">
                                <i class="fa-solid fa-ban me-2"></i> Block
                            </a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="handleChatAction('miscategorized')">
                                <i class="fa-solid fa-exclamation-triangle me-2"></i> Report Miscategorized
                            </a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="handleChatAction('optout')">
                                <i class="fa-solid fa-sign-out-alt me-2"></i> Opt out
                            </a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#" @click.prevent="handleChatAction('resolved')">
                                <i class="fa-solid fa-check-circle me-2"></i> Resolved
                            </a></li>
                    </ul>
                </div>
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

        <div class="modal fade" id="callModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen">
                <div class="modal-content bg-dark text-white border-0">

                    <!-- Header -->
                    <div class="modal-header border-0">
                        <div class="d-flex align-items-center gap-2">
                            <div>
                                <img :src="callerInfo?.avatar" class="rounded-circle border border-success mb-3"
                                    style="width:50px;height:50px;object-fit:cover">
                            </div>
                            <div>
                                <h4 class="modal-title text-white" x-text="callerInfo?.name"></h4>
                                <p id="call-status" class="text-success mt-1" x-text="callStatusText"></p>
                            </div>
                        </div>
                        <div x-show="callState === 'connected'">
                            <span class="badge badge-pill badge-soft-light py-2 px-3"
                                x-text="formattedDuration"
                                style="font-size: 1.1rem; letter-spacing: 1px;">
                            </span>
                        </div>
                    </div>
                    <div x-show="callState === 'connected'" class="modal-body position-relative p-0">
                        <div id="video-wrapper" class="w-100 h-100" :class="isVideo ? 'd-block' : 'd-none'">
                            <div id="remote-media" class="w-100 h-100"></div>
                            <div id="local-media"
                                class="position-absolute bottom-0 end-0 m-3 rounded overflow-hidden border border-white"
                                style="width:160px;height:200px">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-center border-0 bg-dark">

                        <div x-show="callState === 'incoming'" class="row gap-4 align-items-center">

                            <button @click="rejectCall()" class="btn btn-danger rounded-circle p-4 shadow-lg">
                                <i class="fa-solid fa-phone-slash fa-2x"></i>
                            </button>
                            <button @click="acceptCall()" class="btn btn-success rounded-circle p-4 shadow-lg">
                                <i class="fa-solid fa-phone fa-2x"></i>
                            </button>
                        </div>

                        <div x-show="callState === 'ringing'" class="text-center">
                            <button @click="cancelCall()" class="btn btn-danger rounded-circle p-4 shadow-lg">
                                <i class="fa-solid fa-phone-slash fa-2x"></i>
                            </button>
                        </div>

                        <div x-show="callState === 'connected'" class="row gap-4 align-items-center">
                            <button @click="toggleMute()" :class="isMuted ? 'btn-danger' : 'btn-secondary px-4'"
                                class="btn rounded-circle p-3">
                                <i class="fa-solid" :class="isMuted ? 'fa-microphone-slash' : 'fa-microphone'"></i>
                            </button>
                            <button @click="hangUp()" class="btn btn-danger rounded-circle p-4">
                                <i class="fa-solid fa-phone-slash fa-2x"></i>
                            </button>
                        </div>

                        <div x-show="callState === 'connecting'" class="text-center" x-cloak>
                            <div class="spinner-border text-light" role="status"></div>
                            <p class="mt-2">Connecting...</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script_2')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    window.chatInfo = {
        chatId: {{ $chat->id }},
        customerName: "{{ trim(($customer?->f_name ?? '') . ' ' . ($customer?->l_name ?? 'Customer')) }}",
        categoryName: "{{ $chat->category?->name ?? 'General' }}",
        startTime: "{{ $chat->created_at ? $chat->created_at->toIso8601String() : '' }}"
    };
</script>
@endpush