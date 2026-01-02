@extends('layouts.front-end.app')

@section('title',translate('contact_us'))

@push('css_or_js')
<script src="https://cdn.tailwindcss.com"></script>

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Intl Tel Input CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.min.css" />
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
@endpush

@section('content')

<section class="relative bg-gradient-to-r from-[#129d91] to-[#0f776d] text-white py-24 overflow-hidden shadow-xl rounded-lg">
    <!-- Background Image Layer (optional) -->
    <div class="absolute inset-0 bg-[url('/images/contact-us-bg.jpg')] bg-cover bg-center opacity-10 blur-sm"></div>

    <!-- Main Content -->
    <div class="relative container mx-auto px-6 text-center animate__animated animate__fadeInDown">
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4 tracking-tight">Get In Touch With Us</h1>
        <p class="text-lg md:text-xl mb-6 max-w-2xl mx-auto">
            Have questions or need assistance? Our team is ready to help. Reach out to us today.
        </p>
        <a href="mailto:support@nisr.in" class="inline-block bg-white text-[#129d91] hover:bg-[#e6f6f4] px-6 py-3 font-semibold rounded-full transition-all duration-300 shadow-lg">
            Contact Us
        </a>
    </div>
</section>

<div class="__inline-58 py-5">
    <section>
        <div class="py-8 bg-gray-50 mb-5">
            <div class="container mx-auto px-4">
                <h1 class="text-3xl md:text-4xl font-bold text-center mb-5">{{ translate('contact_us') }}</h1>

                <!-- Contact Cards -->
                <div class="flex flex-wrap justify-center gap-6">

                    <!-- Phone -->
                    <div class="bg-white p-6 rounded-2xl shadow text-center w-72 md:w-80 lg:w-96">
                        <img src="https://cdn-icons-png.flaticon.com/512/724/724664.png" class="mx-auto w-14 h-14 mb-4" alt="Phone">
                        <h3 class="text-xl font-semibold mb-2">{{ translate('Call Us') }}</h3>
                        <a class="text-gray-700 text-base md:text-lg hover:underline" href="tel:{{ getWebConfig(name: 'company_phone') }}">
                            <i class="fa fa-phone me-2"></i>{{ getWebConfig(name: 'company_phone') }}
                        </a>
                    </div>

                    <!-- Email -->
                    <div class="bg-white p-6 rounded-2xl shadow text-center w-72 md:w-80 lg:w-96">
                        <img src="https://cdn-icons-png.flaticon.com/512/561/561188.png" class="mx-auto w-14 h-14 mb-4" alt="Email">
                        <h3 class="text-xl font-semibold mb-2">{{ translate('Email Us') }}</h3>
                        <a class="text-gray-700 text-base md:text-lg hover:underline" href="mailto:{{ getWebConfig(name: 'company_email') }}">
                            <i class="fa fa-envelope me-2"></i>{{ getWebConfig(name: 'company_email') }}
                        </a>
                    </div>

                    <!-- Address -->
                    <div class="bg-white p-6 rounded-2xl shadow text-center w-72 md:w-80 lg:w-96">
                        <img src="https://cdn-icons-png.flaticon.com/512/684/684908.png" class="mx-auto w-14 h-14 mb-4" alt="Address">
                        <h3 class="text-xl font-semibold mb-2">{{ translate('address') }}</h3>
                        <p class="text-gray-700 text-base md:text-lg">
                            <i class="fa fa-map-marker me-2"></i>{{ getWebConfig(name: 'shop_address') }}
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="py-3 bg-gray-50">
            <div class="container mx-auto px-4">
                <h1 class="text-3xl font-bold text-center mb-8">{{ translate('follow_us') }}</h1>

                <!-- Follow Us Section -->
                @if(!empty($web_config['social_media']))
                <div class="flex flex-wrap justify-center gap-8 mb-12">

                    @foreach ($web_config['social_media'] as $item)
                    @php
                    // Icons list
                    $defaultIcons = [
                    'facebook' => 'https://cdn-icons-png.flaticon.com/512/733/733547.png',
                    'instagram' => 'https://cdn-icons-png.flaticon.com/512/174/174855.png',
                    'twitter' => 'https://cdn-icons-png.flaticon.com/512/733/733579.png',
                    'linkedin' => 'https://cdn-icons-png.flaticon.com/512/145/145807.png',
                    'pinterest' => 'https://cdn-icons-png.flaticon.com/512/2111/2111498.png',
                    'youtube' => 'https://cdn-icons-png.flaticon.com/512/1384/1384060.png',
                    ];

                    $icon = $defaultIcons[$item->name] ?? 'https://cdn-icons-png.flaticon.com/512/25/25694.png';
                    @endphp

                    <div class="bg-white p-6 rounded-2xl shadow text-center w-40">
                        <img src="{{ $icon }}" class="mx-auto w-12 h-12 mb-4" alt="{{ ucfirst($item->name) }}">
                        <h3 class="text-lg font-semibold">{{ ucfirst($item->name) }}</h3>
                        <a href="{{ $item->link }}" target="_blank" class="text-sm text-gray-600 break-all">
                            {{ '@' . parse_url($item->link, PHP_URL_HOST) }}
                        </a>
                    </div>
                    @endforeach

                </div>
                @endif

            </div>
        </div>
    </section>


    <div class="container rtl text-align-direction">
        <div class="row no-gutters py-5 ">
            <div class="col-lg-6 iframe-full-height-wrap ">
                <img class="for-contact-image" src="{{theme_asset(path: "public/assets/front-end/png/contact.png")}}" alt="">
            </div>
            <div class="col-lg-6">
                <div class="card px-5">
                    <div class="card-body for-send-message">
                        <h2 class="h4 mb-4 text-center font-semibold text-black">{{translate('send_us_a_message')}}</h2>
                        <form action="{{route('contact.store')}}" method="POST" id="getResponse">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>{{translate('your_name')}}</label>
                                        <input class="form-control name" name="name" type="text" value="{{ old('name') }}" placeholder="{{ translate('John_Doe') }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cf-email">{{translate('email_address')}}</label>
                                        <input class="form-control email" name="email" type="email" value="{{ old('email') }}" placeholder="{{ translate('enter_email_address') }}" required>

                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cf-phone">{{translate('your_phone')}}</label>
                                        <input class="form-control mobile_number phone-input-with-country-picker" type="number" value="{{ old('mobile_number') }}" placeholder="{{translate('contact_number')}}" required>

                                        <div class="">
                                            <input type="hidden" class="country-picker-country-code w-50" name="country_code" readonly>
                                            <input type="hidden" class="country-picker-phone-number w-50" name="mobile_number" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="cf-subject">{{translate('subject')}}:</label>
                                        <input class="form-control subject" type="text" name="subject" value="{{ old('subject') }}" placeholder="{{translate('short_title')}}" required>

                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="cf-message">{{translate('message')}}</label>
                                        <textarea class="form-control message" name="message" rows="2" required>{{ old('subject') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            @php($recaptcha = getWebConfig(name: 'recaptcha'))
                            @if(isset($recaptcha) && $recaptcha['status'] == 1)
                            <div id="recaptcha_element" class="w-100" data-type="image"></div>
                            <br />
                            @else
                            <div class="row mb-3 mt-1">
                                <div class="col-6 pr-0">
                                    <input type="text" class="form-control" name="default_captcha_value" value="" placeholder="{{translate('enter_captcha_value')}}" autocomplete="off">
                                </div>
                                <div class="col-6 input-icons rounded">
                                    <a href="javascript:" class="get-contact-recaptcha-verify" data-link="{{ URL('/contact/code/captcha') }}">
                                        <img src="{{ URL('/contact/code/captcha/1') }}" class="input-field __h-44 rounded" id="default_recaptcha_id" alt="">
                                        <i class="tio-refresh icon"></i>
                                    </a>
                                </div>
                            </div>
                            @endif
                            <div class=" ">
                                <button class="btn btn--primary" type="submit">{{translate('send')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('script')

@if(isset($recaptcha) && $recaptcha['status'] == 1)
<script type="text/javascript">
    "use strict";
    var onloadCallback = function() {
        grecaptcha.render('recaptcha_element', {
            'sitekey': '{{ getWebConfig(name: '
            recaptcha ')['
            site_key '] }}'
        });
    };

</script>
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
<script>
    "use strict";
    $("#getResponse").on('submit', function(e) {
        var response = grecaptcha.getResponse();
        if (response.length === 0) {
            e.preventDefault();
            toastr.error($('#message-please-check-recaptcha').data('text'));
        }
    });

</script>
@endif

<script src="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/country-picker-init.js') }}"></script>
@endpush
