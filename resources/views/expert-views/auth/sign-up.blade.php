<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Expert Register</title>
    <meta name="_token" content="{{csrf_token()}}">
    <meta name="robots" content="index, follow">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $web_config['fav_icon']['path'] }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $web_config['fav_icon']['path'] }}">
    <meta name="google-site-verification" content="{{getWebConfig('google_search_console_code')}}">
    <meta name="msvalidate.01" content="{{getWebConfig('bing_webmaster_code')}}">
    <meta name="baidu-site-verification" content="{{getWebConfig('baidu_webmaster_code')}}">
    <meta name="yandex-verification" content="{{getWebConfig('yandex_webmaster_code')}}">

    <meta name="viewport" content="width=device-width, initial-scale=1">

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
    <link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">

    <style>
        :root {
            --base: {
                    {
                    $web_config['primary_color'] ?? '#000000'
                }
            }

            ;

            --base-2: {
                    {
                    $web_config['secondary_color'] ?? '#ffffff'
                }
            }

            ;

            --web-primary: {
                    {
                    $web_config['primary_color'] ?? '#900000'
                }
            }

            ;

            --primary: {
                    {
                    $web_config['primary_color'] ?? '#900000'
                }
            }

            ;

            --bg-primary: {
                    {
                    $web_config['primary_color'] ?? '#900000'
                }
            }

            ;

            --web-primary-10: {
                    {
                    $web_config['primary_color'] ?? '#900000'
                }
            }

            1A;

            --web-primary-20: {
                    {
                    $web_config['primary_color'] ?? '#900000'
                }
            }

            33;

            --web-primary-40: {
                    {
                    $web_config['primary_color'] ?? '#900000'
                }
            }

            66;

            --web-secondary: {
                    {
                    $web_config['secondary_color'] ?? '#ffffff'
                }
            }

            ;
            --web-direction: "{{ Session::get('direction', 'ltr') }}";

            --text-align-direction: {
                    {
                    Session: :get('direction')==='rtl' ? 'right': 'left'
                }
            }

            ;

            --text-align-direction-alt: {
                    {
                    Session: :get('direction')==='rtl' ? 'left': 'right'
                }
            }

            ;
        }

        .dropdown-menu:not(.m-0) {
            margin- {
                    {
                    Session: :get('direction')==="rtl" ? 'right': 'left'
                }
            }

            : -8px !important;
        }

        @media (max-width: 767px) {
            .navbar-expand-md .dropdown-menu>.dropdown>.dropdown-toggle {
                padding- {
                        {
                        Session: :get('direction')==="rtl" ? 'left': 'right'
                    }
                }

                : 1.95rem;
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2) !important;
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

    <!-- Header Image -->
    <div class="position-relativess text-center">
        <img src="{{ asset('assets/front-end/img/register.png') }}" class="img-fluid w-100" alt="Header">
        <h2 class="position-absolute top-50 start-50 translate-middle text-white fw-bold">Apply to become an Expert</h2>
    </div>

    <!-- Form Section -->
    <div class="container">
        <div class="form-section my-4">
            <div class="form-header">Enter Personal Information</div>
            <form id="expert-register-form" class="row g-3 mt-3" method="POST" enctype="multipart/form-data" data-action="{{ route('expert.auth.registration.add') }}">
                @csrf
                <!-- First & Last Name -->
                <div class="col-md-6">
                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                    <input name="first_name" type="text" class="form-control" placeholder="Enter First Name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input name="last_name" type="text" class="form-control" placeholder="Enter Last Name" required>
                </div>

                <!-- Email & Phone -->
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input name="email" type="email" class="form-control" placeholder="Enter Email" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <input name="phone" id="phone" type="tel" class="form-control" placeholder="Enter Phone number" required>
                </div>
                <!-- Password -->
                <div class="col-md-6">
                    <label class="form-label">
                        Password <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Enter Password"
                            required>
                        <span class="input-group-text"
                            style="cursor:pointer"
                            onclick="togglePassword('password', this)">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="col-md-6">
                    <label class="form-label">
                        Confirm Password <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="form-control"
                            placeholder="Confirm Password"
                            required>
                        <span class="input-group-text"
                            style="cursor:pointer"
                            onclick="togglePassword('password_confirmation', this)">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Category -->
                <div class="col-md-6">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select name="category_id" id="category" class="form-select js-select2-custom form-control select2-enable" required>
                        <option value="" disabled selected>Select Category</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Primary Specialty -->
                <!-- Primary Specialty -->
                <div class="col-md-6">
                    <label class="form-label">Primary Specialty <span class="text-danger">*</span></label>
                    <input type="text" name="primary_specialty" class="form-control" placeholder="Enter Primary Specialty" required>
                </div>

                <!-- Secondary Specialty -->
                <div class="col-md-6">
                    <label class="form-label">Secondary Specialty (Optional)</label>
                    <input type="text" name="secondary_specialty" class="form-control" placeholder="Enter Secondary Specialty">
                </div>


                <!-- Experience -->
                <div class="col-md-6">
                    <label class="form-label">Total No. of Experience in Field <span class="text-danger">*</span></label>
                    <input name="experience" type="text" class="form-control" placeholder="1 Year">
                </div>

                <!-- File Uploads -->
                <div class="col-md-6">
                    <label class="form-label">Certification <span class="text-danger">*</span></label>
                    <input name="certification" class="form-control" type="file">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Education Degree <span class="text-danger">*</span></label>
                    <input name="degree" class="form-control" type="file">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Resume / CV <span class="text-danger">*</span></label>
                    <input name="resume" class="form-control" type="file">
                </div>

                <!-- Country & State -->
                <div class="col-md-6">
                    <label class="form-label">Country <span class="text-danger">*</span></label>
                    <select name="country" id="country" class="form-select form-control select2-enable" required>
                        <option value="" disabled selected>Select Country</option>
                        @foreach($countries as $country)
                        <option value="{{ $country['name'] }}">{{ $country['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">State <span class="text-danger">*</span></label>
                    <input name="state" id="state" type="text" class="form-control" placeholder="Enter your state">

                </div>

                <!-- Preferences -->
                <div class="col-md-6">
                    <label class="form-label">How would you prefer to answer questions? <span class="text-danger">*</span></label>
                    <select name="preference" class="form-select form-control select2-enable">
                        <option value="chat" selected>Chat</option>
                        <option value="phone">Phone Call</option>
                        <option value="video">Video Call</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Available to start answering questions <span class="text-danger">*</span></label>
                    <input name="start_date" type="date" class="form-control">
                </div>

                <!-- Availability -->
                <div class="col-md-6">
                    <label class="form-label">Availability to answer questions <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center gap-5">
                        <div class="form-check d-flex align-items-center">
                            <input name="availability_weekdays" class="form-check-input" type="checkbox" id="weekdaysCheck">
                            <label class="form-check-label" for="weekdaysCheck">Monday to Friday</label>
                        </div>
                        <div class="form-check d-flex align-items-center">
                            <input name="availability_weekend" class="form-check-input" type="checkbox" id="weekendCheck">
                            <label class="form-check-label" for="weekendCheck">Sat – Sunday</label>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="col-12 text-center mt-3">
                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                </div>
            </form>
            <p class="text-center  my-4">Already have an account? <a href="{{ route('expert.auth.login') }}" class="text-decoration-none text-primary">Login</a></p>

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
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/tags-input.min.js') }}"></script>

    <script>
        var phoneInput = document.querySelector("#phone");
        var iti = window.intlTelInput(phoneInput, {
            separateDialCode: true,
            utilsScript: "{{ theme_asset('public/assets/front-end/plugin/intl-tel-input/js/utils.js') }}"
        });
    </script>
    <script>
        $("#expert-register-form").on("submit", function(e) {
            e.preventDefault();

            let fullNumber = iti.getNumber();
            $("input[name='phone']").val(fullNumber);

            let formData = new FormData(this);
            $.ajax({
                type: "POST",
                url: $(this).data("action"),
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#loading").removeClass("d--none");
                },
                success: function(response) {
                    if (response.errors) {
                        response.errors.forEach(err => toastr.error(err));
                    } else if (response.status === 1) {
                        toastr.success(response.message);
                        window.location.href = response.redirect_url;
                    }
                },
                complete: function() {
                    $("#loading").addClass("d--none");
                },
            });
        });
    </script>
    <script>
        function togglePassword(inputId, el) {
            const input = document.getElementById(inputId);
            const icon = el.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

</body>

</html>