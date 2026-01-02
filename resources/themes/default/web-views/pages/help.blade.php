@extends('layouts.front-end.app')

@section('title', translate('Help'))

@section('content')
@php
$hero = app('App\Http\Controllers\Admin\Cms\HelpController')::getSectionDataStatic('hero')[0] ?? [];
$buttons = app('App\Http\Controllers\Admin\Cms\HomeController')::getSectionDataStatic('quick_buttons');
$faqGeneral = app('App\Http\Controllers\Admin\Cms\HelpController')::getSectionDataStatic('faq_general');
$faqBilling = app('App\Http\Controllers\Admin\Cms\HelpController')::getSectionDataStatic('faq_billing');
$faqAccount = app('App\Http\Controllers\Admin\Cms\HelpController')::getSectionDataStatic('faq_account');
$faqExperts = app('App\Http\Controllers\Admin\Cms\HelpController')::getSectionDataStatic('faq_experts');
$kb = app('App\Http\Controllers\Admin\Cms\HelpController')::getSectionDataStatic('knowledge_base');
@endphp
<section class="hero-section">
    <img src="{{ asset($hero['bg_image'] ?? 'assets/img/help/help-hero.png') }}" alt="" class="bg-img w-100 h-100 object-fit-cover">
    <div class="overlay"></div>
    <div class="hero-content">
        <div>
            <div>
                <h2 class="hero-heading text-white mb-2">{{ $hero['heading1'] ?? 'We’re Changing the Way the' }}</h2>
                <h2 class="hero-heading text-white mb-2">{{ $hero['heading2'] ?? 'World Gets Expert Advice' }}</h2>
                <p class="text-white">{!! nl2br($hero['paragraph'] ?? '') !!}</p>
            </div>

            <div class="mb-4 d-flex flex-wrap gap-2 quick-quetions ">
                @foreach($buttons as $btn)
                <a href="{{ $btn['link'] ?? '#' }}" class="btn btn-outline-light btn-sm rounded-4">
                    <i class="bi bi-search"></i> {{ $btn['text'] ?? '' }}
                </a>
                @endforeach
            </div>

            <div class="input-group shadow-lg start-chat start-chat-home">
            <input type="text" id="userQuestion" class="form-control" placeholder="{{ $hero['search_placeholder'] ?? 'What can we help with Today ?' }}">
            <button id="startChatBtn" class="btn btn-primary px-4">
                Start Chat
            </button>
        </div>
        </div>
    </div>
    </div>
</section>

<!-- FAQ Tabs -->
<section class="container py-4">
    <ul class="nav nav-pills row g-3" id="myTab" role="tablist">
        <li class="col-12 col-sm-6 col-md-3">
            <button class="btn btn-outline-primary w-100 py-2 fw-semibold active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general">General</button>
        </li>
        <li class="col-12 col-sm-6 col-md-3">
            <button class="btn btn-outline-primary w-100 py-2 fw-semibold" id="billing-tab" data-bs-toggle="tab" data-bs-target="#billing">Billing & Payments</button>
        </li>
        <li class="col-12 col-sm-6 col-md-3">
            <button class="btn btn-outline-primary w-100 py-2 fw-semibold" id="account-tab" data-bs-toggle="tab" data-bs-target="#account">Account & Access</button>
        </li>
        <li class="col-12 col-sm-6 col-md-3">
            <button class="btn btn-outline-primary w-100 py-2 fw-semibold" id="experts-tab" data-bs-toggle="tab" data-bs-target="#experts">Experts</button>
        </li>
    </ul>

    <div class="tab-content mt-4" id="myTabContent">
        <!-- General -->
        <div class="tab-pane fade show active" id="general">
            <h2 class="section-title mb-3">General FAQ</h2>
            <div class="accordion custom-accordion" id="faqAccordionGeneral">
                @foreach($faqGeneral as $id => $faq)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqG{{ $id }}">
                            {{ $faq['question'] ?? '' }}
                        </button>
                    </h2>
                    <div id="faqG{{ $id }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordionGeneral">
                        <div class="accordion-body">{{ $faq['answer'] ?? '' }}</div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>

        <!-- Billing -->
        <div class="tab-pane fade" id="billing">
            <h2 class="section-title mb-3">Billing & Payments FAQ</h2>
            <div class="accordion custom-accordion" id="faqAccordionBilling">
                @foreach($faqBilling as $id => $faq)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqB{{ $id }}">
                            {{ $faq['question'] ?? '' }}
                        </button>
                    </h2>
                    <div id="faqB{{ $id }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordionBilling">
                        <div class="accordion-body">{{ $faq['answer'] ?? '' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Account -->
        <div class="tab-pane fade" id="account">
            <h2 class="section-title mb-3">Account & Access FAQ</h2>
            <div class="accordion custom-accordion" id="faqAccordionAccount">
                @foreach($faqAccount as $id => $faq)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqA{{ $id }}">
                            {{ $faq['question'] ?? '' }}
                        </button>
                    </h2>
                    <div id="faqA{{ $id }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordionAccount">
                        <div class="accordion-body">{{ $faq['answer'] ?? '' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Experts -->
        <div class="tab-pane fade" id="experts">
            <h2 class="section-title mb-1">Expert FAQ</h2>
            <div class="accordion custom-accordion" id="faqAccordionExperts">
                @foreach($faqExperts as $id => $faq)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqE{{ $id }}">
                            {{ $faq['question'] ?? '' }}
                        </button>
                    </h2>
                    <div id="faqE{{ $id }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordionExperts">
                        <div class="accordion-body">{{ $faq['answer'] ?? '' }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
<section class="container py-4">

    <div class="knowledge-base mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="section-title mb-0">Expert Knowledge Base</h2>
            <a href="{{ route('knowledge-base.all') }}" class="btn btn-primary px-4">View All</a>
        </div>
        <div class="row g-4">
            @foreach(array_slice($kb, 0, 6) as $id => $item)
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{{ $item['title'] ?? '' }}</h5>
                        <p class="card-text">
                            {{ Str::limit($item['short_desc'] ?? '', 100) }}
                            <a href="{{ route('knowledge-base.read', $id) }}" class="btn btn-link p-0">Read more</a>
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</section>


<section>
    <div class="container p-0">
        <div class="row g-0 p-4 bg-white shadow rounded-4 mb-4">
            <!-- Left Contact Info -->
            <div class="col-12 col-md-6 contact-left-container">
                <div class="px-4 pt-4 h-100 d-flex flex-column justify-content-between">

                    <!-- Heading + Subheading -->
                    <div class="text-center mb-4">
                        <h2 class="mb-2">Need Help or Support?</h2>
                        <p class="text-white">We’re here to assist you. For any inquiries, reach out below.</p>
                    </div>

                    <!-- Email Info -->
                    <div class="mb-4 text-center">
                        <p>
                            <i class="bi bi-envelope me-2"></i>
                            <a href="mailto:askexpertonline@gmail.com"
                                class="text-white">askexpertonline@gmail.com</a>
                        </p>
                    </div>

                    <!-- Image Section -->
                    <div class="text-center mt-auto">
                        <img src="{{ asset('assets/front-end/img/form-img.png') }}" alt="Support Illustration" class="img-fluid"
                            style="max-width: 90%; height: auto; object-fit: cover;">
                    </div>

                </div>
            </div>

            <!-- Right Form -->
            <div class="col-12 col-md-6 mt-5 mt-md-0 Inquiry-form-container rounded-end-4 p-4">
                <div class="px-4 py-3">
                    <form action="{{ route('contact.store') }}" method="POST" id="getResponse">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                                    id="firstName" placeholder="First name" value="{{ old('first_name') }}" required>
                                @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                                    id="lastName" placeholder="Last name" value="{{ old('last_name') }}" required>
                                @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-12 col-sm-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" placeholder="xyz@gmail.com" value="{{ old('email') }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-sm-6">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror"
                                    id="phone" placeholder="+1 012 3456 789" value="{{ old('mobile_number') }}" required>
                                @error('mobile_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 Select-subject-radio">
                            <label class="form-label fw-semibold">Select Subject?</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check d-flex">
                                    <input class="form-check-input mb-0" type="radio" name="subject"
                                        id="generalInquiry" value="General Inquiry" checked>
                                    <label class="form-check-label mb-0" for="generalInquiry">
                                        General Inquiry
                                    </label>
                                </div>

                                <div class="form-check d-flex">
                                    <input class="form-check-input" type="radio" name="subject"
                                        id="contactInquiry" value="Contact Inquiry">
                                    <label class="form-check-label" for="contactInquiry">
                                        Contact Inquiry
                                    </label>
                                </div>

                                <div class="form-check d-flex">
                                    <input class="form-check-input" type="radio" name="subject"
                                        id="careerInquiry" value="Career Inquiry">
                                    <label class="form-check-label" for="careerInquiry">
                                        Career Inquiry
                                    </label>
                                </div>
                            </div>
                        </div>


                        <div class="mt-4">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control @error('message') is-invalid @enderror" id="message" rows="4"
                                placeholder="Write your message..." required>{{ old('message') }}</textarea>
                            @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-5">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection