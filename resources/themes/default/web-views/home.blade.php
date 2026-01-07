@extends('layouts.front-end.app')

@section('title', $web_config['company_name'].' '.translate('Ask').' | '.$web_config['company_name'].' '.translate('Things'))

@push('css_or_js')
<meta name="robots" content="index, follow">
<meta property="og:image" content="{{$web_config['web_logo']['path']}}" />
<meta property="og:title" content="Welcome To {{$web_config['company_name']}} Home" />
<meta property="og:url" content="{{env('APP_URL')}}">
<meta name="description" content="{{ $web_config['meta_description'] }}">
<meta property="og:description" content="{{ $web_config['meta_description'] }}">
<meta property="twitter:card" content="{{$web_config['web_logo']['path']}}" />
<meta property="twitter:title" content="Welcome To {{$web_config['company_name']}} Home" />
<meta property="twitter:url" content="{{env('APP_URL')}}">
<meta property="twitter:description" content="{{ $web_config['meta_description'] }}">

@endpush

@section('content')

@php
$hero = $data['hero'] ?? [];
$quick_buttons = app('App\Http\Controllers\Admin\Cms\HomeController')::getSectionDataStatic('quick_buttons');
$popular_questions = $data['popular_questions'] ?? [];
$how_it_works = $data['how_it_works'] ?? [];
$why_love = $data['why_love'] ?? [];
$testimonials = $data['testimonials'] ?? [];
$experts = $data['experts'] ?? [];

@endphp

<section class="hero-section">
    <video autoplay muted loop playsinline class="bg-video">
        <source src="{{ asset($hero['bg_video'] ?? 'dist/assets/img/hero-video.mp4') }}" type="video/mp4">
    </video>
    <div class="overlay"></div>
    <div class="hero-content">
        <h1 class="text-white">{!! $hero['heading'] ?? 'Real Experts <br> Real Answers.' !!}</h1>
        <p class="text-white mt-2">{!! $hero['paragraph'] ?? '' !!}</p>

        <div class="mb-4 d-flex flex-wrap gap-2 quick-quetions">
            @foreach($quick_buttons as $btn)
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
<!-- Expert Categories -->
<section class="container slider-container my-5">
    <h2 class="section-title">Expert Categories</h2>
    <div class="swiper expert-categories-slider">
        <div class="swiper-wrapper">
            @foreach($categories as $cat)
            <div class="swiper-slide page-link">
 <img
                    src="{{ !empty($cat->icon_url) ? $cat->icon_url : asset('assets/back-end/img/placeholder/category.png') }}"
                    alt="{{ $cat->name ?? '' }}"
                    class="card-image">                <div class="slide-content">
                    <a href="{{ route('category.view', $cat->id) }}"> <strong class="d-block mb-2 text-dark">{{ $cat['name'] ?? '' }}</strong>
                    </a>
                    <span class="text-muted">{{ $cat['expert_count'] ?? 0 }} Expert</span>
                </div>
            </div>
            @endforeach
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<!-- Popular Questions -->
<section class="container-fluid py-5">
    <div class="container">
        <h2 class="section-title">Popular questions at JustAnswer</h2>
        <div class="row gx-4 gy-4">
            @foreach($popular_questions as $q)

            <div class="col-md-6 col-lg-3">
                <a href="{{ $q['link'] ?? '#' }}" class="questions-card position-relative text-white">
                    <div class="questions-card-bg">
                        <img src="{{ asset($q['image'] ?? '') }}" alt="{{ $q['title'] ?? '' }}" />
                    </div>
                    <div class="questions-card-overlay">
                        <div class="questions-card-content">
                            <h3>{{ $q['title'] ?? '' }}</h3>
                            <p>{{ $q['description'] ?? '' }}</p>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="how-it-works-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5">
                <h2 class="section-title">How It Works</h2>
                <div class="steps-container">
                    @foreach($how_it_works as $i => $step)
                    <div class="step-trigger-card" data-bs-target="#howItWorksCarousel" data-bs-slide-to="{{ $i }}">
                        <h5>{{ $step['title'] ?? '' }}</h5>
                        <p>{{ $step['description'] ?? '' }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-7 mt-5 mt-lg-0">
                <div id="howItWorksCarousel" class="carousel slide how-it-works-slider" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        @foreach($how_it_works as $i => $step)
                        <button type="button" data-bs-target="#howItWorksCarousel" data-bs-slide-to="{{ $i }}" class="{{ $i == 0 ? 'active' : '' }}"></button>
                        @endforeach
                    </div>
                    <div class="carousel-inner">
                        @foreach($how_it_works as $i => $step)
                        <div class="carousel-item {{ $i == 0 ? 'active' : '' }}">
                            <img src="{{ asset($step['image'] ?? '') }}" class="d-block w-100" alt="">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Love -->
<section class="why-love-section py-5">
    <div class="container">
        <h2 class="section-title">Why you'll love Ask Expert Online</h2>
        <div class="row">
            @foreach($why_love as $item)
            <div class="col-lg-3 col-md-6">
                <div class="feature-card">
                    <div class="feature-card-icon">
                        <img src="{{ asset($item['icon'] ?? '') }}" alt="">
                    </div>
                    <div class="feature-card-content">
                        <h5>{{ $item['title'] ?? '' }}</h5>
                        <p>{{ $item['description'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="happy-members-section py-5">
    <div class="container">
        <h2 class="section-title mb-5">Our Happy Members</h2>
        <div class="swiper" id="happyMembersSwiper">
            <div class="swiper-wrapper">
                @foreach($testimonials as $t)
                <div class="swiper-slide">
                    <div class="testimonial-card p-3 rounded-4 w-100">
                        <div class="flex-grow-1 ms-4">
                            <h5 class="fw-bold">{{ $t['title'] ?? '' }}</h5>
                            <p class="small text-muted">{{ $t['description'] ?? '' }}</p>
                            <p class="mb-0 small member-name">{{ $t['name'] ?? '' }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>

<!-- Experts -->
<section class="expert-section py-5 bg-light">
    <div class="container">
        <h2 class="section-title mb-4 text-center">Our Experts</h2>
        <div id="expertSwiper" class="swiper expert-swiper">
            <div class="swiper-wrapper">
                @foreach($experts as $e)
                <div class="swiper-slide">
                    <div class="expert-card p-4 bg-white shadow-sm rounded h-100 d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ asset($e['image'] ?? '') }}" class="rounded-circle" width="60" height="60">
                            <div class="ms-3">
                                <h5 class="fw-bold mb-0">{{ $e['name'] ?? '' }}</h5>
                                <p class="expert-title mb-0">{{ $e['title'] ?? '' }}</p>
                            </div>
                        </div>
                        <div class="star-rating mb-3">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="tio{{ $i <= ($e['rating'] ?? 0) ? '-star' : '-star' }} -star"></i>
                                @endfor
                        </div>
                        <p class="small text-muted">{{ $e['description'] ?? '' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>
<span id="direction-from-session" data-value="{{ session()->get('direction') }}"></span>
@endsection

@push('script')
<script src="{{theme_asset(path: 'public/assets/front-end/js/owl.carousel.min.js')}}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/home.js') }}"></script>

@endpush