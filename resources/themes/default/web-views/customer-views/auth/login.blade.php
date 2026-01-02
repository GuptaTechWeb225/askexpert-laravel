
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Customer Login</title>
    <meta name="_token" content="{{csrf_token()}}">
    <meta name="robots" content="index, follow">
   <link rel="apple-touch-icon" sizes="180x180" href="{{ $web_config['fav_icon']['path'] }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $web_config['fav_icon']['path'] }}">
    <meta name="google-site-verification" content="{{getWebConfig('google_search_console_code')}}">
    <meta name="msvalidate.01" content="{{getWebConfig('bing_webmaster_code')}}">
    <meta name="baidu-site-verification" content="{{getWebConfig('baidu_webmaster_code')}}">
    <meta name="yandex-verification" content="{{getWebConfig('yandex_webmaster_code')}}">

    <meta name="viewport" content="width=device-width, initial-scale=1">
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

</style>
    <link rel="stylesheet" href="{{theme_asset(path: 'public/assets/front-end/css/custom.css')}}">
    @stack('css_or_js')

    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/home.css') }}" />
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/responsive1.css') }}" />
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/style.css') }}">
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/swiper-bundle.min.css') }}">
  

    <style>
       :root {
        --base: {{ $web_config['primary_color'] ?? '#000000' }};
        --base-2: {{ $web_config['secondary_color'] ?? '#ffffff' }};
        --web-primary: {{ $web_config['primary_color'] ?? '#900000' }};
        --primary: {{ $web_config['primary_color'] ?? '#900000' }};
        --bg-primary: {{ $web_config['primary_color'] ?? '#900000' }};
        --web-primary-10: {{ $web_config['primary_color'] ?? '#900000' }}1A; /* 10% opacity */
        --web-primary-20: {{ $web_config['primary_color'] ?? '#900000' }}33; /* 20% opacity */
        --web-primary-40: {{ $web_config['primary_color'] ?? '#900000' }}66; /* 40% opacity */
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

<style>.toast {
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
</style>
<link rel="stylesheet" href="{{theme_asset(path: 'public/assets/front-end/css/custom.css')}}">

{!! getSystemDynamicPartials(type: 'analytics_script') !!}
</head>


<body>


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

    <?php
    $customerSocialLogin = $web_config['customer_login_options']['social_login'] ?? 0;
    ?>
    <div class="container-fluid">
        <div class="row min-vh-100">

            <div class="col-md-6 d-none d-md-flex left-panel text-secondary" style="background-image: url('{{ asset('assets/front-end/img/form-left.png') }}');">
                <div class=" content-wrapper">
                                    @php($eCommerceLogo = getWebConfig(name: 'company_web_logo'))

                    <img src="{{ getStorageImages(path: $eCommerceLogo, type:'backend-logo') }}" alt="Ask Expert Logo" class="logo">
                    <h1>Hey! Welcome to Ask Expert</h1>
                    <p>Join us and give information to people</p>
                </div>
            </div>
            <div class="col-12 col-md-6 d-flex justify-content-center align-items-center">
                <div class="form-container">
                    <div class="text-center mb-5">
                        <h2 class="position-relative d-inline-block mx-auto">
                            <span class="sparkle">
                                <img src="{{ asset( 'assets/front-end/img/form-icon.png') }}" alt="Icon" class="register-icon">
                            </span>
                            Log in
                        </h2>
                    </div>

                    <form autocomplete="off" class="customer-centralize-login-form" data-action="{{ route('customer.auth.login') }}" method="post" id="customer-login-form">
                        @csrf
                        <input type="hidden" name="login_type" value="manual-login">

                        <div class="row mb-3">
                            <div class="mb-3 p-0">
                                <label class="form-label font-semibold">
                                    {{ translate('email') }} / {{ translate('phone')}}
                                    <span class="input-required-icon">*</span>
                                </label> <input type="text" class="form-control" id="si-email" name="user_identity" placeholder="Enter email or phone">
                                <div class="invalid-feedback">{{ translate('please_provide_valid_email_or_phone_number') }} .</div>

                            </div>

                            <div class="mb-3 p-0">
                                <label class="form-label font-semibold">
                                    {{ translate('password') }}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <div class="password-toggle rtl">
                                    <input class="form-control text-align-direction auth-password-input" name="password" type="password" id="si-password" placeholder="{{ translate('enter_password')}}" required>
                                    <label class="password-toggle-btn">
                                        <input class="custom-control-input" type="checkbox">
                                        <i class="tio-hidden password-toggle-indicator"></i>
                                        <span class="sr-only">{{ translate('show_password') }}</span>
                                    </label>
                                </div>
                            </div>
                            @php($rememberId = rand(111, 999))
                            <div class="form-group d-flex flex-wrap justify-content-between">
                                <div class="rtl">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="remember"
                                            id="remember{{ $rememberId }}" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="custom-control-label text-primary" for="remember{{ $rememberId }}">{{ translate('remember_me') }}</label>
                                    </div>
                                </div>
                                <a class="font-size-sm text-primary text-underline" href="{{route('customer.auth.recover-password')}}">
                                    {{ translate('forgot_password') }}?
                                </a>
                            </div>

                            <button class="btn btn-primary w-100 mb-3" type="submit">Sign In</button>

                            <p class=" text-center">New to Ask Expert Online ?<a class="text-primary text-underline" href="{{route('customer.auth.sign-up')}}"> Sign Up Here </a>
                            </p>

                            <div class="divider">Or With</div>

                            @if($customerSocialLogin)
                            @foreach ($web_config['customer_social_login_options'] as $socialLoginServiceKey => $socialLoginService)
                            @if ($socialLoginService && $socialLoginServiceKey != 'apple')
                            <a class="social-media-login-btn"
                                href="{{ route('customer.auth.service-login', $socialLoginServiceKey) }}">
                                <img alt=""
                                    src="{{theme_asset(path: 'public/assets/front-end/img/icons/'.$socialLoginServiceKey.'.png') }}">
                                <span class="text">
                                    {{ translate($socialLoginServiceKey) }}
                                </span>
                            </a>
                            @endif
                            @endforeach
                            @endif
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script src="{{ theme_asset(path: 'public/assets/front-end/vendor/jquery/dist/jquery-2.2.4.min.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>
    <script src="{{ theme_asset(path: "public/assets/back-end/js/toastr.js") }}"></script>

    <script>
       $('.customer-centralize-login-form').on('submit', function(event) {
    event.preventDefault();

    $.ajax({
        url: $(this).attr('action'),
        method: $(this).attr('method'),
        data: $(this).serialize(),
        beforeSend: function() {
            $("#loading").addClass("d-grid");
        },
        success: function(response) {
            if (response.status === 'success') {
                toastr.success(response.message);

                if (response.pending_question) {
                    $.ajax({
                        url: "{{ route('ask.expert.start') }}",
                        type: "POST",
                        data: {
                            question: response.pending_question,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.success) {
                                if (res.requires_payment) {
                                    window.location.href = res.payment_url;
                                } else {
                                    window.location.href = res.redirect_url;
                                }
                            } else {
                                alert(res.message || 'Something went wrong');
                                 window.location.reload
                            }
                        },
                        error: function() {
                            alert('Something went wrong while submitting the pending question.');
                        }
                    });
                } else {
                    window.location.href = response.redirect_url || "/my/home";
                }
            } else if (response.status === 'error') {
                toastr.error(response.message);
            }
        },
        complete: function() {
            $("#loading").removeClass("d-grid");
        },
    });
});

    </script>
</body>

</html>