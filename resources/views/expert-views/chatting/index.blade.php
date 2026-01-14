{{-- resources/views/expert/admin-chat.blade.php or jo bhi tera view hai --}}

@extends('layouts.back-end.app-expert')

@section('title', 'Chat with Admin')

@section('content')
<link rel="stylesheet" href="{{dynamicAsset(path:'public/assets/back-end/vendor/fontawesome-free/css/all.min.css')}}">

@vite(['resources/js/app.js']) 

<div class="content container-fluid">

    <div class="card">
        <div class="card-header">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img src="{{asset('/public/assets/back-end/img/support-ticket.png')}}" alt="">
                Chat with Admin
            </h2>
        </div>
        <div class="card-body h-100">
            <div class="row g-0 h-100"
                x-data="expertAdminChatComponent()"
                x-init="init()"
                style="height: 85vh; background: white;">
                <div class="col-lg-4 h-100">
                    <div class="input-group p-3">
                        <input type="text" class="form-control border-start-0" placeholder="Search chats..."
                            oninput="filterChats(this.value)">
                    </div>
                    <div class="overflow-auto flex-grow-1" id="expertsList">

                        <div class="p-3 border rounded-lg m-2 cursor-pointer hover-bg-light bg-soft-danger">
                            <div class="d-flex align-items-center gap-2 ">
                                <img src="{{ getStorageImages($superAdmin->image_full_url, 'avatar') }}"
                                    class="me-3 message-avatar" width="50">

                                <div class="w-100">
                                    <strong>{{ $superAdmin->name }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 d-flex flex-column">

                    <div class="" style="height: 90vh;">
                        <div class="whatsapp-container d-flex flex-column h-100">
                            <!-- Header -->
                            <div class="align-items-center chat-header d-flex justify-content-between p-3">
                                <div class="d-flex align-items-center">
                                <img src="{{ getStorageImages($superAdmin->image_full_url, 'avatar') }}" class="rounded-circle me-3" width="50" alt="Admin">
                                <div>
                                    <h6 class="mb-0 text-white">{{ $superAdmin->name }} (Admin)</h6>
                                    <small class="text-white" x-show="typing" style="display:none;">typing...</small>
                                </div>
</div>
                                <div class="ms-auto pe-3 d-flex align-items-center gap-3">
                                    <button class="btn btn-primary btn-sm" @click="initiateCall(false)">
                                        <i class="fa-solid fa-phone"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" @click="initiateCall(true)">
                                        <i class="fa-solid fa-video"></i>
                                    </button>

                                </div>
                            </div>

                            <div class="chat-body p-3 flex-grow-1"
                                id="messages"
                                style="background:#e5ddd5; overflow-y:auto;">
                                @foreach($messages as $msg)
                                <div class="message-container {{ $msg->sender_type == 'expert' ? 'user-side' : '' }}" data-message-id="{{ $msg->id }}">
                                    @if($msg->sender_type != 'expert')
                                    <img src="{{ getStorageImages($superAdmin->image_full_url, 'avatar') }}" class="message-avatar" alt="Admin">
                                    @endif

                                    <div class="message-bubble {{ $msg->sender_type == 'expert' ? 'user' : 'bot' }}">
                                        @if($msg->image_path)
                                        <img src="{{ asset('storage/' . $msg->image_path) }}" class="chat-img-preview" style="max-width:200px; border-radius:10px;">
                                        @else
                                        {!! nl2br(e($msg->message)) !!}
                                        @endif

                                        <div class="message-meta">
                                            <span>{{ \Carbon\Carbon::parse($msg->sent_at)->format('h:i A') }}</span>
                                            @if($msg->sender_type == 'expert')
                                            <span class="read-ticks" style="{{ $msg->is_read ? 'color: #34b7f1;' : '' }}">
                                                {{ $msg->is_read ? '✓✓' : '✓' }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($msg->sender_type == 'expert')
                                    <img src="{{ getStorageImages(path: auth('expert')->user()->image_full_url, type: 'avatar') }}" class="message-avatar" alt="You">
                                    @endif
                                </div>
                                @endforeach
                            </div>

                            <div class="chat-footer p-3 bg-light border-top"
                                style="position: sticky; bottom: 0; z-index: 10;">
                                <input type="file" id="imageInput" style="display:none" accept="image/*" @change="handleFileUpload">

                                <div class="input-group">
                                    <button class="btn btn-light" @click="document.getElementById('imageInput').click()">
                                        <i class="fa-solid fa-paperclip"></i>
                                    </button>

                                    <input type="text" x-model="newMessage" @keyup.enter="sendMessage" @keyup="typingEvent"
                                        class="form-control mx-2" placeholder="Type a message...">

                                    <button class="btn btn--primary" @click="sendMessage">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="callAdminModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
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
        </div>
    </div>
</div>

<script>
    window.EXPERT_ID = {{auth('expert') -> id()}};
    window.EXPERT_AVATAR = "{{ getStorageImages(path: auth('expert')->user()->image_full_url, type: 'avatar') }}";
</script>
@endsection