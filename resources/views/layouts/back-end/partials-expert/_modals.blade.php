<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{translate('ready_to_Leave').'?'}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">{{translate('select_Logout_below_if_you_are_ready_to_end_your_current_session').'.'}}</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">{{translate('cancel')}}</button>
                <a class="btn btn--primary" href="{{route('expert.auth.logout')}}">{{translate('logout')}}</a>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="popup-modal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <div class="text-center">
                            <h2 class="__color-8a8a8a">
                                <i class="tio-shopping-cart-outlined"></i> {{translate('you_have_new order').','.translate('check_Please')}}.
                            </h2>
                            <hr>
                            <button class="btn btn-warning ignore-check-order">{{ translate('Ignore_this_now') }}</button>
                            <button class="btn btn--primary check-order">{{translate('ok').','.translate('let_me_check')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="NotificationModal" tabindex="-1" aria-labelledby="shiftNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg" id="NotificationModalContent">
        </div>
    </div>
</div>


@php
    $resolvedChatId = isset($chat) && isset($chat->id)
        ? $chat->id
        : (auth('expert')->user()->current_chat_id ?? null);

        dd($resolvedChatId)
@endphp

<div x-data="expertChatComponent({{ $resolvedChatId }})" x-init="init()">
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