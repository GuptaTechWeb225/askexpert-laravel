{{-- resources/views/expert/admin-chat.blade.php or jo bhi tera view hai --}}

@extends('layouts.back-end.app-expert')

@section('title', 'Chat with Admin')

@section('content')
@vite(['resources/js/app.js']) {{-- chat-component.js import ho jayega --}}

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
                x-data="adminExpertChatComponent()"
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
                        <div class="whatsapp-container d-flex flex-column h-100"
                            x-data="expertAdminChatComponent()"
                            x-init="init()">
                            <!-- Header -->
                            <div class="chat-header p-3 d-flex align-items-center">
                                <img src="{{ getStorageImages($superAdmin->image_full_url, 'avatar') }}" class="rounded-circle me-3" width="50" alt="Admin">
                                <div>
                                    <h6 class="mb-0 text-white">{{ $superAdmin->name }} (Admin)</h6>
                                    <small class="text-white" x-show="typing" style="display:none;">typing...</small>
                                </div>
                            </div>

                            <!-- Messages Body -->
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

                            <!-- Footer -->
                            <div class="chat-footer p-3 bg-light border-top"
                                style="position: sticky; bottom: 0; z-index: 10;">
                                <input type="file" id="imageInput" style="display:none" accept="image/*" @change="handleFileUpload">

                                <div class="input-group">
                                    <button class="btn btn-light" @click="document.getElementById('imageInput').click()">
                                        <i class="fa-solid fa-paperclip"></i>
                                    </button>

                                    <input type="text" x-model="newMessage" @keyup.enter="sendMessage" @keyup="typingEvent"
                                        class="form-control mx-2" placeholder="Type a message...">

                                    <button class="btn btn-success" @click="sendMessage">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>
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