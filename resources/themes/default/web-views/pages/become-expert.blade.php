{{-- resources/views/front-end/become-expert.blade.php --}}
@extends('layouts.front-end.app')

@section('title', 'Become an Expert')

@section('content')
@php
$hero = app('App\Http\Controllers\Admin\Cms\ExpertCmsController')::getSectionDataStatic('hero')[0] ?? [];
$whyJoin = app('App\Http\Controllers\Admin\Cms\ExpertCmsController')::getSectionDataStatic('why_join');
$howMain = app('App\Http\Controllers\Admin\Cms\ExpertCmsController')::getSectionDataStatic('how_it_works')[0] ?? [];
$howImages = app('App\Http\Controllers\Admin\Cms\ExpertCmsController')::getSectionDataStatic('how_it_works');
$testimonials= app('App\Http\Controllers\Admin\Cms\ExpertCmsController')::getSectionDataStatic('testimonials');
$cta = app('App\Http\Controllers\Admin\Cms\ExpertCmsController')::getSectionDataStatic('cta')[0] ?? [];
@endphp

{{-- HERO ---------------------------------------------------- --}}
<section class="hero-section">
    <img src="{{ asset($hero['bg_image'] ?? 'assets/img/become-expert/expert-hero-bg.png') }}" alt="" class="bg-img">
    <div class="overlay"></div>
    <div class="hero-content">
        <div>
            <h2 class="text-white mb-2">{{ $hero['heading1'] ?? 'Share Your Expertise' }}</h2>
            <h2 class="text-white mb-2">{{ $hero['heading2'] ?? 'Get Paid Helping Others.' }}</h2>
            <p class="text-white my-4">{!! nl2br($hero['paragraph'] ?? '') !!}</p>
        </div>
        <div>
            <a href="{{ route('expert.auth.registration.index') }}"><button class="btn btn-primary px-4">Apply Now</button></a>
        </div>
        <div class="chat-floating-icon"> … </div>
    </div>
</section>
<section class="join-ask-expert py-5">
    <div class="container">
        <h2 class="section-title mb-5">Why Join Ask Expert Online</h2>
        <div class="row text-center">
            @foreach($whyJoin as $item)
            <div class="col-12 col-sm-6 col-md-3">
                <div class="join-expert-box">
                    <img src="{{ asset($item['icon'] ?? '') }}" alt="">
                    <h5>{{ $item['title'] ?? '' }}</h5>
                    <p>{{ $item['description'] ?? '' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <h2 class="section-title mb-5">How It Works</h2>
        <div class="row align-items-center g-4">
            <div class="col-lg-6 col-md-12">
                <div class="left-parent">
                    <div class="works-left-section">
                        <div class="badge-card">
                            <span>{{ $howMain['badge_number'] ?? '10k+' }}</span>
                            <span class="experts-text">{{ $howMain['badge_text'] ?? 'Experts' }}</span>
                            <div class="avatars">
                                <img src="https://randomuser.me/api/portraits/women/1.jpg" alt="">
                                <img src="https://randomuser.me/api/portraits/men/2.jpg" alt="">
                                <img src="https://randomuser.me/api/portraits/women/3.jpg" alt="">
                            </div>
                        </div>

                        @foreach($howImages as $id => $img)
                        @if($id == 1 || $id == 2)
                        <img src="{{ asset($img['image'] ?? '') }}"
                            alt="{{ $img['alt'] ?? '' }}"
                            class="{{ $loop->iteration == 2 ? 'woman-image' : 'man-image' }}">
                        @endif
                        @endforeach


                        <a href="{{route('expert.auth.registration.index')}}" class="action-button btn-primary">Join Now</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12 text-center right-content">

                @if(isset($howImages[3]))
                <img src="{{ asset($howImages[3]['image']) }}"
                    alt="{{ $howImages[3]['alt'] ?? '' }}">
                @endif
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="section-title mb-5">What Experts Say</h2>
        <div class="what-experts-say">
            <div id="what-experts-say" class="swiper">
                <div class="swiper-wrapper">
                    @foreach($testimonials as $t)
                    <div class="swiper-slide">
                        <div class="what-experts-say-card">
                            <div class="text-content">
                                <h5 class="fw-bold">{{ $t['name'] ?? '' }}</h5>
                                <p class="small">{{ $t['quote'] ?? '' }}</p>
                                <span>★★★★★</span>
                            </div>
                            <img src="{{ asset($t['image'] ?? '') }}" alt="" class="expert-image">
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
</section>

<div class="home-bottom-card container mb-5" style="background-image: url('{{ asset('assets/front-end/img/home-bottom-bg.png') }}');">
    <div class="row d-flex justify-content-between align-items-center">
        <div class="col-12 col-md-8">
            <h2>{{ $cta['title'] ?? 'Apply now and start earning within days.' }}</h2>
            <p>{{ $cta['paragraph'] ?? 'Ready to turn your expertise into income?' }}</p>
        </div>
        <div class="col-12 col-md-4 mt-3 mt-md-0 text-center text-md-end">
            <a href="{{ route('expert.auth.registration.index') }}" class="mb-0 px-4 py-3">{{ $cta['btn_text'] ?? 'Apply Now' }}</a>
        </div>
    </div>
</div>
@endsection