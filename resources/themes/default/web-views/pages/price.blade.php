@extends('layouts.front-end.app')

@section('title', translate('Prices'))

@section('content')
@php
$hero = app('App\Http\Controllers\Admin\Cms\PricingController')::getSectionDataStatic('hero')[0] ?? [];
$plans = app('App\Http\Controllers\Admin\Cms\PricingController')::getSectionDataStatic('all_plans');
$faqs = app('App\Http\Controllers\Admin\Cms\PricingController')::getSectionDataStatic('faq');
$buttons = app('App\Http\Controllers\Admin\Cms\HomeController')::getSectionDataStatic('quick_buttons');
@endphp

<section class="hero-section">
    <img src="{{ asset($hero['bg_image'] ?? 'assets/img/pricing/pricing-hero.png') }}" class="bg-img">
    <div class="overlay"></div>
    <div class="hero-content">
        <h2 class="text-white mb-3">{{ $hero['heading1'] ?? 'Get Expert Advice – When You Need It,' }}</h2>
        <h2 class="text-white mb-3">{{ $hero['heading2'] ?? 'How You Need It.' }}</h2>
        <p class="text-white my-4">{{ $hero['paragraph'] ?? 'Choose a plan that fits your life. No hidden fees. Cancel anytime.' }}</p>

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
</section>
<section class="py-2">
    <div class="container my-5">
        <h1 class="section-title text-start mb-5">Expert Fee</h1>
        <div class="row g-4">
            @foreach ($categories as $categorie )
            <div class="col-sm-6 col-lg-4">
                <div class="card price-card h-100">
                    <div class="price-card-header">
                        <h4>{{ $categorie->name }}</h4>
                            <img src="{{ $categorie->card_image_url }}" alt="{{ $categorie->name }}">
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $categorie->name }}</h5>
                    <p class="price">${{ $categorie->monthly_subscription_fee }}<span class="fs-6">/month</span></p>
                    <p class="join-fee">(plus ${{ $categorie->joining_fee }} join fee)</p>
                    <p class="description">
                        {{ \Illuminate\Support\Str::limit(strip_tags($categorie->card_description), 45, '...') }}
                    </p>
                    <a href="{{ route('category.view', $categorie->id) }}" class="btn btn-outline-primary w-100">Ask a {{ $categorie->name }}</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    </div>
</section>
<section class="difference-section">
    <div class="container my-5">
        <h1 class="section-title text-start mb-5">What’s Included in All Plans</h1>
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-5 g-4">
            @foreach($plans as $plan)
            <div class="col {{ $loop->iteration % 2 == 0 ? 'card-shift-down' : '' }}">
                <div class="card h-100 all-plans-card">
                    <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                        <div class="img-placeholder mb-3">
                            <img src="{{ asset($plan['icon'] ?? 'assets/img/pricing/all-plans.png') }}" alt="">
                        </div>
                        <p class="card-text text-muted">{{ $plan['description'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="pricing-faq py-5">
    <div class="container">
        <h2 class="section-title mb-5">Pricing FAQ</h2>
        <div class="accordion custom-accordion" id="faqAccordion">
            @foreach($faqs as $index => $faq)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{ $loop->index }}">
                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                        {{ $faq['question'] ?? '' }}
                    </button>
                </h2>
                <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                    data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        {{ $faq['answer'] ?? '' }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection