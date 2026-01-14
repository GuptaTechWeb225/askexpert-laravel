
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Customer Register</title>
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

  <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">

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
            <div class="col-md-6 d-flex d-md-none justify-content-center pt-5 text-secondary" >
                <div class="text-center">
                    <img src="{{ asset( 'assets/front-end/img/logo.png') }}" alt="Ask Expert Logo" class="logo">
                    <h1>Hey! Welcome to Ask Expert</h1>
                    <p>Join us and give information to people</p>
                </div>
            </div>
            <div class="col-md-6 d-none d-md-flex left-panel text-secondary"  style="background-image: url('{{ asset('assets/front-end/img/form-left.png') }}');">
                <div class="content-wrapper">
                    <img src="{{ asset( 'assets/front-end/img/logo.png') }}" alt="Ask Expert Logo" class="logo">
                    <h1>Hey! Welcome to Ask Expert</h1>
                    <p>Join us and give information to people</p>
                </div>
            </div>
            <div class="col-12 col-md-6 d-flex justify-content-center align-items-center">
                <div class="form-container">
                    <div class="text-center">
                        <h2 class="position-relative d-inline-block mx-auto">
                            <span class="sparkle">
                                <img src="{{ asset( 'assets/front-end/img/form-icon.png') }}" alt="Icon" class="register-icon">
                            </span>
                            Register Now
                        </h2>
                    </div>

                         <form class="needs-validation_" id="customer-register-form" data-action="{{ route('customer.auth.sign-up')}}"
                        method="post">
                        @csrf
                        <div class="row mb-3 p-0">
                            <div class="col-12 col-md-6">
                                <label for="firstName" class="form-label">First name</label>
 <input class="form-control text-align-direction" value="{{ old('f_name')}}" type="text" name="f_name"
                                        placeholder="{{ translate('Ex') }}: {{ translate('Jhone') }}"
                                        required >      
                                                                    <div class="invalid-feedback">{{ translate('please_enter_your_first_name')}}!</div>
                      </div>
                            <div class="col-12 col-md-6">
                                <label for="lastName" class="form-label">Last name</label>
  <input class="form-control text-align-direction" type="text" value="{{old('l_name') }}" name="l_name"
                                        placeholder="{{ translate('ex') }}: {{ translate('Doe') }}" required>  
                                                                    <div class="invalid-feedback">{{ translate('please_enter_your_last_name') }}!</div>
                          </div>
                        </div>

                        <div class="mb-3 p-0">
                            <label for="email" class="form-label">Email</label>
   <input class="form-control text-align-direction" type="email" value="{{old('email') }}" name="email"
                                     placeholder="{{ translate('enter_email_address') }}" autocomplete="off"
                                        required>                                         <div class="invalid-feedback">{{ translate('please_enter_valid_email_address') }}!</div>
               </div>

                      <div class="mb-3 p-0">
    <label for="password" class="form-label">Password</label>
                                <div class="password-toggle rtl">

    <input class="form-control auth-password-input"
           type="password"
           name="password"
           id="password"
           placeholder="******"
           required>

    <label class="password-toggle-btn">
        <input class="custom-control-input" type="checkbox">
        <i class="tio-hidden password-toggle-indicator"></i>
        <span class="sr-only">{{ translate('show_password') }}</span>
    </label>
    </div>

</div>



                  <div class="mb-3 p-0 w-100">
                    <label class="form-label">Phone Number</label>
                    <input id="phone" type="tel" name="phone" class="form-control" placeholder="Enter Phone number" required>
                </div>

                        <div class="form-check my-3 d-flex  align-items-center">
                            <input class="form-check-input" type="checkbox" value="" id="privacyCheck" required>
                            <label class="form-check-label" for="privacyCheck">
                                You agree to our friendly <a class="text-primary" href="#">privacy policy</a>.
                            </label>
                        </div>
                        <button  class="btn btn--primary w-100 mb-3" id="sign-up" type="submit" >Sign Up</button>

                        <p class="text-center">Already have an account? <a href="{{ route('customer.auth.login') }}">Login</a></p>

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
        <script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
        <script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>


        
    <span id="message-otp-sent-again" data-text="{{ translate('OTP_has_been_sent_again.') }}"></span>
    <span id="message-wait-for-new-code" data-text="{{ translate('please_wait_for_new_code.') }}"></span>
    <span id="message-please-check-recaptcha" data-text="{{ translate('please_check_the_recaptcha.') }}"></span>
    <span id="message-please-retype-password" data-text="{{ translate('please_ReType_Password') }}"></span>
    <span id="message-password-not-match" data-text="{{ translate('password_do_not_match') }}"></span>
    <span id="message-password-match" data-text="{{ translate('password_match') }}"></span>
    <span id="message-password-need-longest" data-text="{{ translate('password_Must_Be_6_Character') }}"></span>
    <span id="message-send-successfully" data-text="{{ translate('send_successfully') }}"></span>
    <span id="message-update-successfully" data-text="{{ translate('update_successfully') }}"></span>
    <span id="message-successfully-copied" data-text="{{ translate('successfully_copied') }}"></span>
    <span id="message-copied-failed" data-text="{{ translate('copied_failed') }}"></span>
    <span id="message-cannot-input-minus-value" data-text="{{ translate('cannot_input_minus_value') }}"></span>


    <span id="password-error-message" data-max-character="{{translate('at_least_8_characters') . '.'}}"
        data-uppercase-character="{{translate('at_least_one_uppercase_letter_') . '(A...Z)' . '.'}}"
        data-lowercase-character="{{translate('at_least_one_uppercase_letter_') . '(a...z)' . '.'}}"
        data-number="{{translate('at_least_one_number') . '(0...9)' . '.'}}"
        data-symbol="{{translate('at_least_one_symbol') . '(!...%)' . '.'}}"></span>
    <span class="system-default-country-code" data-value="{{ getWebConfig(name: 'country_code') ?? 'us' }}"></span>
    <span id="system-session-direction" data-value="{{ session()->get('direction') ?? 'ltr' }}"></span>

    <span id="is-request-customer-auth-sign-up" data-value="{{ Request::is('customer/auth/sign-up*') ? 1 : 0 }}"></span>
    <span id="is-customer-auth-active" data-value="{{ auth('customer')->check() ? 1 : 0 }}"></span>

    <span id="storage-flash-deals" data-value="{{ $web_config['flash_deals']['start_date'] ?? '' }}"></span>

    <script src="{{ theme_asset(path: 'public/assets/front-end/vendor/jquery/dist/jquery-2.2.4.min.js') }}"></script>
    <script src="{{ theme_asset(path: "public/assets/back-end/js/toastr.js")}}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/sweet_alert.js') }}"></script>
    <script src="{{ theme_asset(path: "public/assets/back-end/js/toastr.js") }}"></script>
<script>

  $("#customer-register-form").on("submit", function (e) {
    e.preventDefault();

    var fullNumber = iti.getNumber(); 
    $("#phone").val(fullNumber); 

    $.ajax({
        type: "POST",
        url: $(this).data("action"),
        data: $(this).serialize(),
        beforeSend: function () {
            $("#loading").addClass("d-grid");
        },
        success: function (response) {
            if (response.errors) {
                for (let index = 0; index < response.errors.length; index++) {
                    toastr.error(response.errors[index].message);
                }
            } else if (response.error) {
                toastr.error(response.error);
            } else if (response.status === 1) {
                toastr.success(response.message);
                window.location.href = response.redirect_url;
            } else if (response.redirect_url !== "") {
                window.location.href = response.redirect_url;
            }
        },
        complete: function () {
            $("#loading").removeClass("d-grid");
        },
    });
});

</script>
<script>
$(document).on('change', '.password-toggle-btn input[type="checkbox"]', function () {
    const wrapper = $(this).closest('.password-toggle');
    const input = wrapper.find('input[type="password"], input[type="text"]');
    const icon = wrapper.find('.password-toggle-indicator');

    if (this.checked) {
        input.attr('type', 'text');
        icon.removeClass('tio-hidden').addClass('tio-visible');
    } else {
        input.attr('type', 'password');
        icon.removeClass('tio-visible').addClass('tio-hidden');
    }
});
</script>

</body>

</html>