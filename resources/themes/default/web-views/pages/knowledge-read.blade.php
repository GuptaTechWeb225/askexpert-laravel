@extends('layouts.front-end.app')

@section('title', $item['title'] ?? 'knowledge-base')

@section('content')
<div class="container py-5"></div>
<section>
    <div class="container py-5">
        <a href="{{ route('knowledge-base.all') }}" class="btn btn-primary mb-3">
            <i class="bi bi-arrow-left"></i> Back to All
        </a>
        <h1 class="section-title mb-4">{{ $item['title'] ?? 'Not Found' }}</h1>
        <div class="mt-4 lead">
            {!! nl2br(e($item['full_answer'] ?? 'No content available.')) !!}
        </div>
    </div>
</section>
@endsection