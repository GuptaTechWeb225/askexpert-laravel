@extends('layouts.front-end.app')

@section('title', translate('About Us'))

@section('content')
@php
$about = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('hero')[0] ?? [];
$buttons = app('App\Http\Controllers\Admin\Cms\HomeController')::getSectionDataStatic('quick_buttons');
$mission = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('our_mission')[0] ?? [];
$missionImages = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('our_mission');
$difference = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('difference');
$weHelp = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('we_help');
$achievements = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('achievements');
$experts = $data['experts'] ?? [];
@endphp

<section class="hero-section">
    <img src="{{ asset($about['bg_image'] ?? 'assets/img/about-hero.png') }}" class="bg-img">
    <div class="overlay"></div>
    <div class="hero-content">
        <h2 class="text-white mb-2">{{ $about['heading1'] ?? '' }}</h2>
        <h2 class="text-white mb-2">{{ $about['heading2'] ?? '' }}</h2>
        <p class="text-white">{!! nl2br($about['paragraph'] ?? '') !!}</p>
        <div class="mb-4 d-flex flex-wrap gap-2 quick-quetions ">
            @foreach($buttons as $btn)
            <a href="{{ $btn['link'] ?? '#' }}" class="btn btn-outline-light btn-sm rounded-4">
                <i class="bi bi-search"></i> {{ $btn['text'] ?? '' }}
            </a>
            @endforeach
        </div>
        <div class="input-group shadow-lg start-chat start-chat-home">
            <input type="text" id="userQuestion" class="form-control" placeholder="{{ $about['search_placeholder'] ?? 'What can we help with Today ?' }}">
            <button id="startChatBtn" class="btn btn-primary px-4">
                Start Chat
            </button>
        </div>
    </div>
</section>

<!-- Our Mission -->
<section class="container-fluid popular-questions-container py-5">
    <div class="container">
        <h2 class="section-title text-primary mb-5">{{ $mission['title'] ?? 'Our Mission' }}</h2>
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex flex-row flex-nowrap justify-content-center align-items-center overflow-auto">
                    @foreach($missionImages as $index => $img)
                    @if(isset($img['image']))
                    <div class="img-wrapper {{ $loop->even ? 'mt-5' : '' }} me-3">
                        <img src="{{ asset($img['image']) }}" class="img-fluid rounded shadow" style="width:150px;">
                    </div>
                    @endif
                    @endforeach
                </div>

            </div>
            <div class="col-md-6 d-flex justify-content-center align-items-center mt-4 mt-md-0">
                <div class="our-mission-content">

                    <p>{!! nl2br($mission['paragraph1'] ?? '') !!}</p>
                    <p>{!! nl2br($mission['paragraph2'] ?? '') !!}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- What Makes Us Different -->
<section class="difference-section">
    <div class="container">
        <h2 class="section-title">What Makes Us Different?</h2>
        <div class="row text-center">
            @foreach($difference as $item)
            <div class="col-12 col-sm-6 col-md-3">
                <div class="difference-box">
                    <img src="{{ asset($item['icon'] ?? '') }}" alt="">
                    <h5>{{ $item['title'] ?? '' }}</h5>
                    <p>{{ $item['description'] ?? '' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Who We Help -->
<section class="we-help-section py-5">
    <div class="container">
        <h2 class="section-title mb-5">Who We Help</h2>
        <div class="testimonial-slider-container">

            <div class="swiper" id="we-help-section">
                <div class="swiper-wrapper">
                    @foreach($weHelp as $item)
                    <div class="swiper-slide">
                        <div class="we-help-card p-3 rounded-4">
                            <div class="flex-grow-1 ms-4">

                                <h5 class="fw-bold">{{ $item['title'] ?? '' }}</h5>
                                <p class="small text-muted">{{ $item['description'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
</section>
<section class="expert-section py-5 bg-light">
    <div class="container">
        <h2 class="section-title mb-4">Our Experts</h2>
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
<section class="achievements py-5 px-4">
    <div class="container achievements-container" style="background-image: url('{{ asset('assets/front-end/img/achievement-bg.png') }}');">
        <div class="row justify-content-center g-4">
            @foreach($achievements as $item)
            <div class="col-12 col-md-6 col-lg-3">
                <div class="stat d-flex flex-column flex-md-row align-items-center justify-content-center justify-content-md-start text-center text-md-start">
                    <div class="achieve-icon-container me-0 me-md-3 mb-3 mb-md-0">
                        <img src="{{ asset($item['icon'] ?? '') }}" alt="">
                    </div>
                    <div>
                        <h2>{{ $item['number'] ?? '' }}</h2>
                        <p>{{ $item['text'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection