<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session()->get('direction') ?? 'ltr' }}">

<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <meta name="_token" content="{{csrf_token()}}">
    <meta name="robots" content="index, follow">
    <meta property="og:site_name" content="{{ $web_config['company_name'] }}" />

    <meta name="google-site-verification" content="{{getWebConfig('google_search_console_code')}}">
    <meta name="msvalidate.01" content="{{getWebConfig('bing_webmaster_code')}}">
    <meta name="baidu-site-verification" content="{{getWebConfig('baidu_webmaster_code')}}">
    <meta name="yandex-verification" content="{{getWebConfig('yandex_webmaster_code')}}">

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $web_config['fav_icon']['path'] }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $web_config['fav_icon']['path'] }}">
    <link rel="stylesheet" media="screen"
        href="{{ theme_asset(path: 'public/assets/front-end/vendor/simplebar/dist/simplebar.min.css') }}">
    <link rel="stylesheet" media="screen"
        href="{{ theme_asset(path: 'public/assets/front-end/vendor/tiny-slider/dist/tiny-slider.css') }}">
    <link rel="stylesheet" media="screen"
        href="{{ theme_asset(path: 'public/assets/front-end/vendor/drift-zoom/dist/drift-basic.min.css') }}">
    <link rel="stylesheet" media="screen"
        href="{{ theme_asset(path: 'public/assets/front-end/vendor/lightgallery.js/dist/css/lightgallery.min.css') }}">
    <link rel="stylesheet" media="screen" href="{{ theme_asset(path: 'public/assets/front-end/css/theme.css') }}">
    <link rel="stylesheet" media="screen" href="{{ theme_asset(path: 'public/assets/front-end/css/slick.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/back-end/css/toastr.css') }}" />
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/master.css') }}" />
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/roboto-font.css')  }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/css/lightbox.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/back-end/vendor/icon-set/style.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{dynamicAsset(path:'public/assets/back-end/vendor/fontawesome-free/css/all.min.css')}}">
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>


    <!-- Heroicons (via unpkg, works in browser) -->
    <script src="https://unpkg.com/heroicons@2.0.18/dist/heroicons.min.js"></script>
    @stack('css_or_js')

    @include(VIEW_FILE_NAMES['robots_meta_content_partials'])

    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/cat-chatboat.css') }}" />
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/home.css') }}" />
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/responsive1.css') }}" />
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/swiper-bundle.min.css') }}">
    <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-D4I1jSkUOeD0BVf5H7JArqF0pWSpv+VJ8XnRgEkp+AEH+5TjJzYc6ZrRwnhiG2lVjHmqKufQZcql3hFdcX7a5g=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
/>


    <style>
       :root {
        --base: {{ $web_config['primary_color'] ?? '#000000' }};
        --base-2: {{ $web_config['secondary_color'] ?? '#ffffff' }};
        --web-primary: {{ $web_config['primary_color'] ?? '#900000' }};
        --primary: {{ $web_config['primary_color'] ?? '#900000' }};
        --bg-primary: {{ $web_config['primary_color'] ?? '#900000' }};
        --web-primary-10: {{ $web_config['primary_color'] ?? '#900000' }}1A; 
        --web-primary-20: {{ $web_config['primary_color'] ?? '#900000' }}33; 
        --web-primary-40: {{ $web_config['primary_color'] ?? '#900000' }}66;
        --web-secondary: {{ $web_config['secondary_color'] ?? '#ffffff' }};
        --web-direction: "{{ Session::get('direction', 'ltr') }}";
        --text-align-direction: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};
        --text-align-direction-alt: {{ Session::get('direction') === 'rtl' ? 'left' : 'right' }};
    }

        .dropdown-menu:not(.m-0) {
            margin-{{ Session::get('direction') === "rtl" ? 'right' : 'left' }}: -8px !important;
        }

        @media (max-width: 767px) {
            .navbar-expand-md .dropdown-menu>.dropdown>.dropdown-toggle {
                padding-{{ Session::get('direction') === "rtl" ? 'left' : 'right'}}: 1.95rem;
            }
        }

        .navbar-nav li a {
            color: black !important;
        }
    </style>

<style>
.toast {
    background-color: #333 !important;
    color: #fff !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
}

.toast-success {
    background-color: #28a745 !important;
}

.toast-error {
    background-color: #dc3545 !important;
}

.toast-info {
    background-color: #17a2b8 !important;
}

.toast-warning {
    background-color: #ffc107 !important;
}
/* Toastr Fix */
#toast-container > .toast-success {
    background-color: #51a351 !important;
    color: #ffffff !important;
}

#toast-container > .toast-error {
    background-color: #bd362f !important;
    color: #ffffff !important;
}

#toast-container > .toast-info {
    background-color: #2f96b4 !important;
    color: #ffffff !important;
}

#toast-container > .toast-warning {
    background-color: #f89406 !important;
    color: #ffffff !important;
}

#toast-container .toast-message {
    color: #ffffff !important;
}

</style>
    <link rel="stylesheet" href="{{theme_asset(path: 'public/assets/front-end/css/custom.css')}}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/style.css') }}">

    {!! getSystemDynamicPartials(type: 'analytics_script') !!}
</head>

<body class="toolbar-enabled">

    @include('layouts.front-end.partials._header')
    @include('layouts.front-end.partials._alert-message')
    @include('layouts.front-end.partials._login-model')

    <span id="authentication-status" data-auth="{{ auth('customer')->check() ? 'true' : 'false' }}"></span>

    <div class="row">
        <div class="col-12 loading-parent">
            <div id="loading" class="d--none">
                <div class="text-center">
                    <img width="200" alt=""
                        src="{{ getStorageImages(path: getWebConfig(name: 'loader_gif'), type: 'source', source: theme_asset(path: 'public/assets/front-end/img/loader.gif')) }}">
                </div>
            </div>
        </div>
    </div>

    @yield('content')
    @include('layouts.front-end.partials._footer')

<!-- Store start chat route in a span tag -->
<span id="start-chat-route" data-url="{{ route('ask.expert.start') }}" style="display:none;"></span>
<span id="guest-chat-route" data-url="{{ route('guest.process-email') }}" style="display:none;"></span>
<span id="session-clear-route" data-url="{{ route('customer.auth.clear.pending') }}" style="display:none;"></span>
<span id="store-return-url-route" data-url="{{ route('customer.auth.store.return.url') }}" style="display:none;"></span>
<script>
    window.csrfToken = "{{ csrf_token() }}";
    window.isCustomerLoggedIn = {{ auth('customer')->check() ? 'true' : 'false' }};
</script>

    <script src="{{ theme_asset(path: 'public/assets/front-end/vendor/jquery/dist/jquery-2.2.4.min.js') }}"></script>
    <script
        src="{{ theme_asset(path: 'public/assets/front-end/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script
        src="{{ theme_asset(path: 'public/assets/front-end/vendor/bs-custom-file-input/dist/bs-custom-file-input.min.js') }}"></script>
    <script
        src="{{ theme_asset(path: 'public/assets/front-end/vendor/smooth-scroll/dist/smooth-scroll.polyfills.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/js/lightbox.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/vendor/drift-zoom/dist/Drift.min.js') }}"></script>
    <script
        src="{{ theme_asset(path: 'public/assets/front-end/vendor/lightgallery.js/dist/js/lightgallery.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/vendor/lg-video.js/dist/lg-video.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/owl.carousel.min.js')}}"></script>
    <script src="{{ theme_asset(path: "public/assets/back-end/js/toastr.js")}}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/theme.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/theme.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/question-model.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/cat-chatboat.js') }}"></script>
    <script src="{{ theme_asset(path: "public/assets/back-end/js/toastr.js") }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/custom.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/main.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/chatboat.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/swiper-bundle.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/custome-swiper.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    {!! Toastr::message() !!}

    @include('layouts.front-end.partials._firebase-script')

    <script>
        "use strict";

        @if(Request::is('/') && \Illuminate\Support\Facades\Cookie::has('popup_banner') == false)
        $(document).ready(function () {
            $('#popup-modal').modal('show');
        });
        @php(\Illuminate\Support\Facades\Cookie::queue('popup_banner', 'off', 1))
        @endif

        @if ($errors->any())
            @foreach($errors->all() as $error)
                toastr.error('{{$error}}', Error, {
                    CloseButton: true,
                    ProgressBar: true
                });
            @endforeach
        @endif

        $(document).mouseup(function (e) {
            let container = $(".search-card");
            if (!container.is(e.target) && container.has(e.target).length === 0) {
                container.hide();
            }
        });

        function route_alert(route, message) {
            Swal.fire({
                title: '{{ translate("are_you_sure")}}?',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '{{$web_config['primary_color']}}',
                cancelButtonText: '{{ translate("no")}}',
                confirmButtonText: '{{ translate("yes")}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = route;
                }
            })
        }

        @php($cookie = $web_config['cookie_setting'] ? json_decode($web_config['cookie_setting']['value'], true) : null)
        let cookie_content = `
        <div class="cookie-section">
            <div class="container">
                <div class="d-flex flex-wrap align-items-center justify-content-between column-gap-4 row-gap-3">
                    <div class="text-wrapper">
                        <h5 class="title">{{ translate("Your_Privacy_Matter")}}</h5>
                        <div>{{ $cookie ? $cookie['cookie_text'] : '' }}</div>
                    </div>
                    <div class="btn-wrapper">
                        <button class="btn bg-dark text-white cursor-pointer" id="cookie-reject">{{ translate("no_thanks")}}</button>
                        <button class="btn btn-success cookie-accept" id="cookie-accept">{{ translate('i_Accept')}}</button>
                    </div>
                </div>
            </div>
        </div>
    `;
        $(document).on('click', '#cookie-accept', function () {
            document.cookie = '6valley_cookie_consent=accepted; max-age=' + 60 * 60 * 24 * 30;
            $('#cookie-section').hide();
        });
        $(document).on('click', '#cookie-reject', function () {
            document.cookie = '6valley_cookie_consent=reject; max-age=' + 60 * 60 * 24;
            $('#cookie-section').hide();
        });

        $(document).ready(function () {
            if (document.cookie.indexOf("6valley_cookie_consent=accepted") !== -1) {
                $('#cookie-section').hide();
            } else {
                $('#cookie-section').html(cookie_content).show();
            }
        });
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

</body>

</html>