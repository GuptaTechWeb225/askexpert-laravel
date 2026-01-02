@extends('layouts.front-end.app')

@section('title', 'Careers at NISR')

@section('content')
<div class="container py-5"></div>

<div class="container py-5">
    <h1 class="section-title mb-4">Expert Knowledge Base</h1>
    <div class="row g-4">
        @foreach($kb as $id => $item)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{{ $item['title'] ?? '' }}</h5>
                    <p class="card-text flex-grow-1">{{ $item['short_desc'] ?? '' }}</p>
                    <a href="{{ route('knowledge-base.read', $id) }}" class="btn btn-primary mt-auto">Read More</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
