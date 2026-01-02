@php($announcement = getWebConfig(name: 'announcement'))

@if (isset($announcement) && $announcement['status'] == 1)
<div class="text-center position-relative px-4 py-1 d--none" id="announcement"
    style="background-color: {{ $announcement['color'] }};color:{{$announcement['text_color']}}">
    <span>{{ $announcement['announcement'] }} </span>
    <span class="__close-announcement web-announcement-slideUp">X</span>
</div>
@endif
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
   <div class="chat-popup" id="chat-popup">

        <div class="chat-header">
            <div class="bot-info">
                <img src="{{ asset('assets/front-end/img/chat-avtar.png') }}" alt="Avatar del Bot" class="avatar">
                <div class="bot-details">
                    <div class="bot-name">Chat Bot</div>
                    <div class="bot-status"><span class="active-dot"></span> Active</div>
                </div>
            </div>
            <div class="header-icons">
                <span class="tooltip-icon close-btn" data-tooltip="Close" id="close-chat">
                    <!-- <img src="{{ asset('assets/front-end/img/cross-icon.png') }}" alt="Close" class="header-icon-img"> -->
X                </span>
            </div>
        </div>
        <div class="chat-body">
        </div>

        <div class="chat-footer">
                <input type="text" id="userChatQuestion" class="form-control" placeholder="What can we help with Today">
            <button id="startChatBotBtn" class="icon-btn send-btn"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>
@push('script')
<script>
    "use strict";

    $(".category-menu").find(".mega_menu").parents("li")
        .addClass("has-sub-item").find("> a")
        .append("<i class='czi-arrow-{{Session::get('direction') === "
            rtl " ? 'left' : 'right'}}'></i>");
</script>
@endpush