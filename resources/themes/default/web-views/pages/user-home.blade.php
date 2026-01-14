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
$kb = app('App\Http\Controllers\Admin\Cms\HelpController')::getSectionDataStatic('knowledge_base');
$buttons = app('App\Http\Controllers\Admin\Cms\HomeController')::getSectionDataStatic('quick_buttons');
$hero = app('App\Http\Controllers\Admin\Cms\AfterLoginCmsController')::getSectionDataStatic('hero')[0] ?? [];
@endphp
<section class="hero-section">
    <img src="{{ asset($hero['bg_image'] ?? 'assets/img/home-hero.png') }}" class="bg-img">
    <div class="overlay"></div>
    <div class="hero-content ">
        <div>
            <div>
                <h2 class="text-white mb-3">{!! $hero['heading'] ?? 'Welcome back' !!},<h2 class="text-white mb-3">{!! $hero['paragraph'] ?? 'Ready to get expert advice today? ' !!}</h2>
                </h2>
            </div>
            <div class="mb-4 d-flex flex-wrap gap-2 quick-quetions ">
                @foreach($buttons as $btn)
                <a href="{{ $btn['link'] ?? '#' }}" class="btn btn-outline-light btn-sm rounded-4">
                    <i class="bi bi-search"></i> {{ $btn['text'] ?? '' }}
                </a>
                @endforeach
            </div>
            <div class="input-group shadow-lg start-chat start-chat-home">
                <input type="text" id="userQuestion" class="form-control" placeholder="What can we help with Today">
                <button id="startChatBtn" type="button" class="btn btn-primary px-4">
                    Start Chat
                </button>
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

<section class="expert-section py-5 bg-light">
    <div class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="section-title mb-0">Top Experts Recommended For You</h2>
            <div><a href="{{ route('user.allexpert') }}" class="btn btn-primary px-4">View All</a></div>
        </div>
        <div id="expertSwiper" class="swiper expert-swiper">
            <div class="swiper-wrapper">
                @if (!empty($experts) && $experts->count() > 0)

                @foreach ($experts as $expert)
                <div class="swiper-slide">
                    <div class="expert-card p-3 bg-white shadow-sm rounded h-100 d-flex flex-column w-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ getStorageImages(path: $expert->image_full_url, type: 'avatar') }}" class="rounded-circle"
                                    alt="Michael Chen" width="60" height="60">
                                <div class="mx-3">
                                    <h5 class="fw-bold mb-0">{{ $expert->f_name . ' ' . $expert->l_name }}</h5>
                                    <p class="mb-0 text-muted">{{ $expert->category?->name ?? 'General' }}</p>
                                </div>
                            </div>
                            <span class="btn btn-sm mb-0 mt-3 
                                {{ $expert->is_active ? 'btn-outline-success' : 'btn-outline-danger' }}">
                                {{ $expert->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="star-rating mt-auto">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <a href="{{ route('category.view.expert', [
                                        'category' => $expert->category_id ?? 1,
                                        'expert_id' => $expert->id
                                    ]) }}"
                            class="btn btn-primary w-100 mb-0 mt-3">
                            Start New Session
                        </a>

                    </div>
                </div>
                @endforeach

                @else
                <div class="swiper-slide">
                    <div class="expert-card p-3 bg-white shadow-sm rounded h-100 d-flex flex-column w-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/front-end/img/expert-2.png') }}" class="rounded-circle"
                                    alt="Michael Chen" width="60" height="60">
                                <div class="mx-3">
                                    <h5 class="fw-bold mb-0">Michael Chen</h5>
                                    <p class="mb-0 text-muted">IT Consultant</p>
                                </div>
                            </div>
                            <button class="btn btn-active btn-sm mb-0 mt-3">Active</button>
                        </div>
                        <div class="star-rating mt-auto">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <a href="{{ route('chatbot.full') }}"
                            class="btn btn-primary w-100 mb-0 mt-3">Start New Session</a>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="swiper-slide">
                    <div class="expert-card p-3 bg-white shadow-sm rounded h-100 d-flex flex-column w-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/front-end/img/expert-2.png') }}" class="rounded-circle"
                                    alt="Michael Chen" width="60" height="60">
                                <div class="mx-3">
                                    <h5 class="fw-bold mb-0">Michael Chen</h5>
                                    <p class="mb-0 text-muted">IT Consultant</p>
                                </div>
                            </div>
                            <button class="btn btn-inactive btn-sm mb-0 mt-3">inactive</button>
                        </div>
                        <div class="star-rating mt-auto">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-regular fa-star"></i>
                        </div>
                        <a href="../before-login/chat/chat-boat-full-screen.html"
                            class="btn btn-primary w-100 mb-0 mt-3">Start New Session</a>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="swiper-slide">
                    <div class="expert-card p-3 bg-white shadow-sm rounded h-100 d-flex flex-column w-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/front-end/img/expert-2.png') }}" class="rounded-circle"
                                    alt="Michael Chen" width="60" height="60">
                                <div class="mx-3">
                                    <h5 class="fw-bold mb-0">Michael Chen</h5>
                                    <p class="mb-0 text-muted">IT Consultant</p>
                                </div>
                            </div>
                            <button class="btn btn-active btn-sm mb-0 mt-3">Active</button>
                        </div>
                        <div class="star-rating mt-auto">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-regular fa-star"></i>
                        </div>
                        <a href="../before-login/chat/chat-boat-full-screen.html"
                            class="btn btn-primary w-100 mb-0 mt-3">Start New Session</a>
                    </div>
                </div>

                <!-- Slide 4 -->
                <div class="swiper-slide">
                    <div class="expert-card p-3 bg-white shadow-sm rounded h-100 d-flex flex-column w-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/front-end/img/expert-2.png') }}" class="rounded-circle"
                                    alt="Michael Chen" width="60" height="60">
                                <div class="mx-3">
                                    <h5 class="fw-bold mb-0">Michael Chen</h5>
                                    <p class="mb-0 text-muted">IT Consultant</p>
                                </div>
                            </div>
                            <button class="btn btn-active btn-sm mb-0 mt-3">Active</button>
                        </div>
                        <div class="star-rating mt-auto">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <a href="../before-login/chat/chat-boat-full-screen.html"
                            class="btn btn-primary w-100 mb-0 mt-3">Start New Session</a>
                    </div>
                </div>

                <!-- Slide 5 -->
                <div class="swiper-slide">
                    <div class="expert-card p-3 bg-white shadow-sm rounded h-100 d-flex flex-column w-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/front-end/img/expert-2.png') }}" class="rounded-circle"
                                    alt="Michael Chen" width="60" height="60">
                                <div class="mx-3">
                                    <h5 class="fw-bold mb-0">Michael Chen</h5>
                                    <p class="mb-0 text-muted">IT Consultant</p>
                                </div>
                            </div>
                            <button class="btn btn-active btn-sm mb-0 mt-3">Active</button>
                        </div>
                        <div class="star-rating mt-auto">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-regular fa-star"></i>
                        </div>
                        <a href="../before-login/chat/chat-boat-full-screen.html"
                            class="btn btn-primary w-100 mb-0 mt-3">Start New Session</a>
                    </div>
                </div>

                <!-- Slide 6 -->
                <div class="swiper-slide">
                    <div class="expert-card p-3 bg-white shadow-sm rounded h-100 d-flex flex-column w-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/front-end/img/expert-2.png') }}" class="rounded-circle"
                                    alt="Michael Chen" width="60" height="60">
                                <div class="mx-3">
                                    <h5 class="fw-bold mb-0">Michael Chen</h5>
                                    <p class="mb-0 text-muted">IT Consultant</p>
                                </div>
                            </div>
                            <button class="btn btn-active btn-sm mb-0 mt-3">Active</button>
                        </div>
                        <div class="star-rating mt-auto">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-regular fa-star"></i>
                        </div>
                        <a href="../before-login/chat/chat-boat-full-screen.html"
                            class="btn btn-primary w-100 mb-0 mt-3">Start New Session</a>
                    </div>
                </div>

                @endif
            </div>
            <!-- Pagination -->
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <div class="home-bottom-card container mb-3" style="background-image: url('{{ asset('assets/front-end/img/home-bottom-bg.png') }}');">
        <div class="row d-flex justify-content-between align-items-center">
            <div class="col-12 col-md-8">

                <h2>Need Help?</h2>
                <p>If you have any problem go to help cente</p>
            </div>
            <div class="col-12 col-md-4 mt-3 mt-md-0 text-center text-md-end">
                <a href="{{ route('chatbot.full') }}" class="mb-0 mt-3 px-4">Start New Session</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('script')
<script src="{{theme_asset(path: 'public/assets/front-end/js/owl.carousel.min.js')}}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/home.js') }}"></script>

@endpush