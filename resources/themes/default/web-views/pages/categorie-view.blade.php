@extends('layouts.front-end.app')

@section('title', $categorie->name)

@section('content')

<div class="ec-hero-section hero-computer" style="background-image: url('{{ $categorie->cms_image_url }}');">
    <div class="ec-content-container">
        <div class="ec-ask-expert-container">
            <div class="ec-expert-header">
                <div class="ec-categories">
                    <div class="ec-dropdown">
                        <button class="ec-category-btn">{{ $categorie->name }}<i class="fa-solid fa-caret-down"></i></button>
                        <div class="ec-dropdown-content category-dropdown">
                            @foreach ($categories as $cat)
                            <a href="{{ route('category.view', $cat->id) }}">{{ $cat->name }}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class="ec-dropdown">
                        <button class="ec-more-btn">More <i class="fa-solid fa-caret-down"></i></button>
                        <div class="ec-dropdown-content">
                            @foreach ($categories as $cat)
                            <div class="ec-dropdown-column">
                                <h4>{{ $cat->name }}</h4>
                                @foreach($cat->sub_categorys as $sub)
                                <a href="{{ route('category.view', $cat->id) }}">{{ $sub }}</a>
                                @endforeach
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
                @if(!empty($expert))
                <div class="ec-expert-info">
                    <img src="{{ getStorageImages(path: $expert->image_full_url, type: 'avatar') }}"
                        alt="{{ $expert->f_name ?? 'Expert' }}"
                        class="ec-expert-avatar">

                    <div class="ec-expert-details">
                        <p class="ec-expert-name">
                            {{ trim(($expert->f_name ?? '') . ' ' . ($expert->l_name ?? '')) }}, {{ $expert->category?->name ?? 'General' }}
                        </p>
                        <!-- <p class="ec-expert-stats">
                            {{ $expert->chats->count() ?? 0 }} satisfied customers
                        </p> -->
                        <p class="ec-expert-specialty">
                            {{ $expert->primary_specialty 
                    ?? 'Certified expert with professional experience' }}
                        </p>
                    </div>
                </div>
                @else
                <div class="ec-expert-info">

                    <img src="https://i.pravatar.cc/150?img=16" alt="Dr. Andy" class="ec-expert-avatar">
                    <div class="ec-expert-details">
                        <p class="ec-expert-name">Alan, IT Director</p>
                        <p class="ec-expert-stats">2,865 satisfied customers</p>
                        <p class="ec-expert-specialty">
                            Computer specialist, MIT graduate, emphasis in Hardware, Networking, and Security
                        </p>
                    </div>
                </div>
                @endif

            </div>
            <div class="ec-expert-chat-area" id="ec-expert-chat-area">
                <div class="ec-message-container ec-bot-side ec-welcome-message">
                    @if(!empty($expert))

                    <img src="{{ getStorageImages(path: $expert->image_full_url, type: 'avatar') }}"
                        alt="{{ $expert->f_name ?? 'Expert' }}"
                        class="ec-message-avatar">
                    @else

                    <img src="{{ asset('assets/front-end/img/chat-avtar.png') }}" alt="Pearl Chatbot" class="">
                    @endif


                    <div class="ec-message ec-bot">
                        <p class="">Welcome! What's going on?</p>
                    </div>
                </div>
            </div>
            <div class="ec-expert-input-footer start-chat">
                <input type="text" id="userQuestion" placeholder="Type your question here...">
                <button class="ec-icon-btn ec-send-btn" id="startChatBtn">
                    <i class="fa-solid fa-paper-plane material-icons"></i>
                </button>
            </div>
            <p class="ec-online-status">{{ $categorie->name }} expert is Online Now</p>
        </div>

        <div class="ec-hero-text">
            <h1>{{ \Illuminate\Support\Str::limit($categorie->cms_heading, 50) }}</h1>
            <p>{{ \Illuminate\Support\Str::limit($categorie->cms_description, 150) }}</p>
        </div>

    </div>
</div>
<section class="container slider-container my-5">
    <h2 class="section-title">Expert Categories</h2>
    <div class="swiper expert-categories-slider">
        <div class="swiper-wrapper">
            @foreach($categories as $cat)
            <div class="swiper-slide page-link">
                <img src="{{ $cat->icon_url }}" alt="{{ $cat->name ?? '' }}" class="card-image">
                <div class="slide-content">
                    <a href="{{ route('category.view', $cat->id) }}"> <strong class="d-block mb-2 text-dark">{{ $cat['name'] ?? '' }}</strong>
                    </a>
                    <span class="text-muted">
                        {{ $cat->experts_count ?? 1 }} Expert{{ ($cat->experts_count ?? 1) != 1 ? 's' : '' }}
                    </span>
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
        <h2 class="section-title">Popular questions at AskExpert</h2>
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
@endsection