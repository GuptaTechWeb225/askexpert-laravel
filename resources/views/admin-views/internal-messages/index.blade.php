@extends('layouts.back-end.app')

@section('title', 'Expert Chat')

@section('content')

<style>
    .chat-wrapper {
        display: flex;
        flex-direction: column;
        height: 85vh;
        min-height: 0;
        /* 🔥 SCROLL FIX */
    }


    /* HEADER */
    .chat-header {
        height: 64px;
        background: #8B0000;
        color: #fff;
        flex-shrink: 0;
    }

    .chat-body {
        flex: 1 1 auto;
        overflow-y: auto;
        min-height: 0;
        /* 🔥 MOST IMPORTANT */
        padding: 15px;
        background: #e5ddd5;
    }

    /* FOOTER */
    .chat-footer {
        background: #f0f0f0;
        padding: 10px;
        flex-shrink: 0;
    }

    /* Message row */
    .message-container {
        display: flex;
        margin-bottom: 10px;
    }

    /* Admin (right side) */
    .message-container.user-side {
        justify-content: flex-end;
    }

    /* Bubble */
    .message-bubble {
        max-width: 70%;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 14px;
    }

    /* Admin bubble */
    .message-bubble.user {
        background: #dcf8c6;
        border-top-right-radius: 0;
    }

    /* Expert bubble */
    .message-bubble.bot {
        background: #fff;
        border-top-left-radius: 0;
    }

    /* Time + ticks */
    .message-meta {
        font-size: 11px;
        color: #777;
        text-align: right;
        margin-top: 4px;
    }

    /* Avatar */
    .message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        margin: 0 6px;
    }


    .chat-header,
    .chat-footer {
        flex-shrink: 0;
    }

    .card-body,
    .row.g-0,
    .col-lg-8,
    .col-lg-4 {
        height: 100%;
    }

    [x-cloak] {
        display: none !important;
    }
</style>
@php
$allExperts = $allExperts->map(function($expert) {
return [
'id' => $expert->id,
'name' => $expert->f_name . ' ' . $expert->l_name,
'avatar' => getStorageImages($expert->image_full_url, 'avatar'),
'specialty' => $expert->primary_specialty ?? 'Expert',
];
});
@endphp
<div class="content container-fluid">
    <div class="card">
        <div class="card-header shadow rounded-3 p-3 d-flex justify-content-between">
            <h5 class="fs-4 mb-0">Chat Management
            </h5>
            <a href="{{ route('admin.expert.miscategorized') }}" class="btn btn--primary"><i class="tio-add"></i>Miscategorized Questions</a>
        </div>
        <div class="card-body">
            <div class="row g-0 shadow overflow-hidden"
                x-data="adminExpertChatComponent()"
                x-init="init()"
                style="height: 85vh; background: white;">

                <div class="col-lg-4 border-end d-flex flex-column gap-2">
                    <div class="p-3 border-bottom">
                        <button class="btn btn-outline--primary w-100" type="button" @click="showSearchModal = true">
                            Select a chat to start messaging
                        </button>
                    </div>
                    <div class="input-group p-3">
                        <input type="text" class="form-control border-start-0" placeholder="Search chats..."
                            oninput="filterChats(this.value)">
                    </div>
                    <div class="overflow-auto flex-grow-1" id="expertsList">
                        @if($experts->isEmpty())
                        <div class="p-5 text-center text-muted">
                            <p>No active chats yet.</p>
                        </div>
                        @else
                        @foreach($experts as $expert)
                        <div class="p-3 border-bottom cursor-pointer hover-bg-light expert-item"
                            data-name="{{ strtolower($expert->f_name . ' ' . $expert->l_name . ' ' . ($expert->primary_specialty ?? '')) }}"
                            style="cursor: pointer"
                            @click="openChat({{ $expert->id }}, '{{ addslashes($expert->f_name . ' ' . $expert->l_name) }}', '{{ getStorageImages($expert->image_full_url, 'backend-profile') }}')">

                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ getStorageImages($expert->image_full_url, 'avatar') }}"
                                    class="rounded-circle me-3 h-42px" width="50">

                                <div class="w-100">
                                    <strong>{{ $expert->f_name }} {{ $expert->l_name }}</strong>
                                    <small class="d-block text-muted">
                                        {{ $expert->primary_specialty ?? 'Expert' }}
                                    </small>
                                </div>

                                @if($expert->unread_count)
                                <div class="badge bg-danger text-white rounded-pill px-2">
                                    {{ $expert->unread_count }}
                                </div>
                                @endif
                            </div>
                        </div>

                        @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-lg-8 d-flex flex-column bg-light">

                    <template x-if="selectedExpertId">
                        <div class="chat-wrapper d-flex flex-column">
                            <div class="chat-header rounded-top d-flex align-items-center px-3 justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <button class="btn btn-light me-3 d-lg-none" @click="selectedExpertId = null">←</button>
                                        <img :src="expertAvatar" class="rounded-circle me-3" width="40">
                                        <div>
                                            <strong x-text="expertName"></strong><br>
                                            <small x-show="typing" class="text-muted">typing...</small>
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

                            <div class="chat-body flex-grow-1" id="messages"></div>
                            <div class="chat-footer p-3 border-top" style="background: #f0f0f0;">
                                <input type="file" id="imageInput" hidden @change="handleFileUpload">

                                <button class="btn btn-light rounded-circle shadow-sm me-2"
                                    type="button"
                                    style="width: 45px; height: 45px; flex-shrink: 0;"
                                    @click="document.getElementById('imageInput').click()">
                                    <i class="fa-solid fa-paperclip text-secondary"></i>
                                </button>

                                <div class="flex-grow-1">
                                    <input type="text"
                                        class="form-control border-0 py-2 shadow-sm"
                                        x-model="newMessage"
                                        @keyup.enter="sendMessage"
                                        @keyup="typingEvent"
                                        placeholder="Type a message..."
                                        style="border-radius: 25px; padding-left: 20px; height: 45px;">
                                </div>

                                <button class="btn btn-success rounded-circle shadow-sm ms-2"
                                    type="button"
                                    style="width: 45px; height: 45px; flex-shrink: 0;"
                                    @click="sendMessage">
                                    <i class="fa-solid fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="top-0 position-fixed start-0 w-100"
                    style="z-index: 1050;"
                    x-show="showSearchModal"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click.self="showSearchModal = false"
                    @keydown.escape.window="showSearchModal = false">

                    <div class="d-flex justify-content-center align-items-center min-vh-100">
                        <div class="bg-white rounded shadow" style="width: 90%; max-width: 500px;" @click.stop>
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Find Expert</h5>
                                <button type="button" class="btn-close btn btn-close-circle" @click="showSearchModal = false"> <i class="tio-clear"></i> </button>
                            </div>
                            <div class="p-3">
                                <input type="text"
                                    class="form-control"
                                    placeholder="Type expert name..."
                                    x-model="searchQuery"
                                    @input="searchExperts"
                                    autofocus
                                    x-ref="searchInput">


                                <div class="mt-3" style="max-height: 400px; overflow-y: auto;">
                                    <template x-for="expert in searchResults" :key="expert.id">
                                        <div class="p-3 border-bottom cursor-pointer hover-bg-light d-flex align-items-center"
                                            @click="selectExpertForChat(expert.id, expert.name, expert.avatar); showSearchModal = false">
                                            <img :src="expert.avatar" class="rounded-circle me-3" width="50" alt="Expert">
                                            <div>
                                                <strong x-text="expert.name"></strong>
                                                <small class="d-block text-muted" x-text="expert.specialty"></small>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                        x-init="$watch('callDuration', () => $el.textContent = formattedDuration)"
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

@vite(['resources/js/app.js'])


<script>
    window.ADMIN_AVATAR = "{{getStorageImages(path: auth('admin')->user()->image_full_url,type: 'backend-profile')}}";
    window.ALL_EXPERTS = @json($allExperts);
</script>
<script>
    function filterChats(value) {
        const search = value.toLowerCase();
        const experts = document.querySelectorAll('#expertsList .expert-item');

        experts.forEach(item => {
            const name = item.getAttribute('data-name');

            if (name.includes(search)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>

@endsection