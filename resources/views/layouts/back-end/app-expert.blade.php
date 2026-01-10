@php
use App\Utils\Helpers;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ Session::get('direction') }}"
    style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="nofollow, noindex ">
    <title>@yield('title')</title>
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type:'backend-logo')}}">
  <style>
        :root {
        --base: {{ $web_config['primary_color'] ?? '#FF0000' }};
        --c1: {{ $web_config['primary_color'] ?? '#FF0000' }};
        --base-2: {{ $web_config['secondary_color'] ?? '#00FF00' }};
        --web-primary: {{ $web_config['primary_color'] ?? '#FF0000' }};
        --web-primary-10: {{ $web_config['primary_color'] ?? '#FF0000' }}10;
        --web-primary-20: {{ $web_config['primary_color'] ?? '#FF0000' }}20;
        --web-primary-40: {{ $web_config['primary_color'] ?? '#FF0000' }}40;
        --web-secondary: {{ $web_config['secondary_color'] ?? '#00FF00' }};
        --web-direction: {{ Session::get('direction', 'ltr') }};
    }
    </style>
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/vendor.min.css') }}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css')}}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/vendor/icon-set/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/theme.minc619.css?v=1.0') }}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/daterangepicker.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/style.css')}}">
        <link rel="stylesheet" href="{{dynamicAsset(path:'public/assets/back-end/vendor/fontawesome-free/css/all.min.css')}}">
<script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>

    @if (Session::get('direction') === 'rtl')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/menurtl.css')}}">
    @endif
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/css/lightbox.css') }}">
    @stack('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/toastr.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/custom.css') }}">
    <style>
        select {
            background-image: url('{{dynamicAsset(path: ' public/assets/back-end/img/arrow-down.png')}}');
            background-size: 7px;
            background-position: 96% center;
        }
    </style>

@vite(['resources/js/app.js'])

  
</head>

<body class="footer-offset">
    @include('layouts.back-end.partials._front-settings')
    <div class="row">
        <div class="col-12 position-fixed z-9999 mt-10rem">
            <div id="loading" class="d--none">
                <div id="loader"></div>
            </div>
        </div>
    </div>


    @include('layouts.back-end.partials-expert._header')
    @include('layouts.back-end.partials-expert._side-bar')

    <main id="content" role="main" class="main pointer-event">
        @yield('content')

<div x-data="expertChatComponent({{ $chat->id }})" x-init="init()">
    <div class="modal fade" id="callModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="false" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen">
            <div class="modal-content bg-dark text-white border-0">

                <!-- Header -->
                <div class="modal-header border-0 text-center">
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <img :src="callerInfo?.avatar" class="rounded-circle border border-success mb-3"
                                style="width:50px;height:50px;object-fit:cover">
                        </div>
                        <div>
                            <h4 class="modal-title" x-text="callerInfo?.name"></h4>
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
        @include('layouts.back-end.partials-expert._footer')

        @include('layouts.back-end.partials-expert._modals')

        @include('layouts.back-end.partials-expert._toggle-modal')
        @include('layouts.back-end._translator-for-js')
        @include('layouts.back-end.partials-expert._sign-out-modal')
        @include('layouts.back-end._alert-message')
    </main>
 <audio id="ringtone" loop>
      <source src="{{ dynamicAsset(path: 'public/assets/back-end/sound/notification.mp3') }}" type="audio/mpeg">
    </audio>

<script>
    window.EXPERT_ID = "{{ auth('expert')->id() }}";
    window.AGORA_APP_ID = "{{ config('services.agora.app_id') }}";
    window.AUTH_USER_AVATAR = "{{ getStorageImages(path: auth('expert')->user()->image_full_url, type: 'avatar') }}";
</script>
    <span class="please_fill_out_this_field" data-text="{{ translate('please_fill_out_this_field') }}"></span>
    <span id="onerror-chatting" data-onerror-chatting="{{dynamicAsset(path: 'public/assets/back-end/img/image-place-holder.png')}}"></span>
    <span id="onerror-user" data-onerror-user="{{dynamicAsset(path: 'public/assets/back-end/img/160x160/img1.jpg')}}"></span>
    <span id="get-root-path-for-toggle-modal-image" data-path="{{dynamicAsset(path: 'public/assets/back-end/img/modal')}}"></span>
   
    <span class="system-default-country-code" data-value="{{ getWebConfig(name: 'country_code') ?? 'us' }}"></span>
    <span id="message-select-word" data-text="{{ translate('select') }}"></span>
    <span id="message-yes-word" data-text="{{ translate('yes') }}"></span>
    <span id="message-no-word" data-text="{{ translate('no') }}"></span>
    <span id="message-cancel-word" data-text="{{ translate('cancel') }}"></span>
    <span id="message-are-you-sure" data-text="{{ translate('are_you_sure') }} ?"></span>
    <span id="message-invalid-date-range" data-text="{{ translate('invalid_date_range') }}"></span>
    <span id="message-status-change-successfully" data-text="{{ translate('status_change_successfully') }}"></span>
    <span id="message-are-you-sure-delete-this" data-text="{{ translate('are_you_sure_to_delete_this') }} ?"></span>
    <span id="message-you-will-not-be-able-to-revert-this"
        data-text="{{ translate('you_will_not_be_able_to_revert_this') }}"></span>

    <span id="get-product-stock-limit-title" data-title="{{translate('warning')}}"></span>
    <span id="get-product-stock-limit-image" data-warning-image="{{ dynamicAsset(path: 'public/assets/back-end/img/warning-2.png') }}"></span>


    <span id="get-confirm-and-cancel-button-text-for-delete-all-products" data-sure="{{translate('are_you_sure').'?'}}"
        data-text="{{translate('want_to_clear_all_stock_clearance_products?').'!'}}"
        data-confirm="{{translate('yes_delete_it')}}" data-cancel="{{translate('cancel')}}"></span>

    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/vendor.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/theme.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js') }}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/bootstrap.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/sweet_new_alert.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/toastr.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/js/lightbox.min.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/custom.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/app-script.js') }}"></script>
    

    {!! Toastr::message() !!}
    @if ($errors->any())
    <script>
        @foreach($errors -> all() as $error)
        toastr.error('{{ $error }}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
    @endif
<script>

</script>
    @if(env('APP_MODE') == 'demo')
    <script>
        'use strict'

        function checkDemoResetTime() {
            let currentMinute = new Date().getMinutes();
            if (currentMinute > 55 && currentMinute <= 60) {
                $('#demo-reset-warning').addClass('active');
            } else {
                $('#demo-reset-warning').removeClass('active');
            }
        }
        checkDemoResetTime();
        setInterval(checkDemoResetTime, 60000);
    </script>
    @endif
    @stack('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/common-script.js') }}"></script>
    @stack('script_2')

</body>

</html>