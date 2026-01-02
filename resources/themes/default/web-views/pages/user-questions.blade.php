@extends('layouts.front-end.app')

@section('title', translate('my_Questions'))

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
</style>
@endpush

@section('content')
@php
$buttons = app('App\Http\Controllers\Admin\Cms\HomeController')::getSectionDataStatic('quick_buttons');
$hero = app('App\Http\Controllers\Admin\Cms\AfterLoginCmsController')::getSectionDataStatic('my_questions')[1] ?? [];
@endphp
@section('content')
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
                <button id="startChatBtn" class="btn btn-primary px-4">
                    Start Chat
                </button>
            </div>
        </div>
    </div>
</section>
<section class="container table-section my-3 py-3">
    <div class="row mt-3">
        <div class="col-lg-12">

            <!-- Filter Section -->
            <div class="bg-white shadow-sm rounded p-3 mb-3">
                <form id="questionsFilterForm" method="GET">
                    <div class="row align-items-center g-3">
                        <!-- Category -->
                        <div class="col-md-3">
                            <select name="q_category_id" class="form-select select2-category" id="qCategorySelect">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('q_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-3">
                            <select name="q_status" class="form-select" id="qStatusSelect">
                                <option value="">All Status</option>
                                <option value="active" {{ request('q_status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ request('q_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="ended" {{ request('q_status') == 'ended' ? 'selected' : '' }}>Ended</option>
                            </select>
                        </div>
 <div class="col-md-3 ">
                            <button type="submit" class="btn btn--primary me-2">Apply Filter</button>
                            <a href="{{ request()->url() }}" class="btn btn-secondary">Reset</a>
                        </div>
                        <!-- Search -->
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" name="q_search" class="form-control" placeholder="Search question" 
                                       value="{{ request('q_search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i>Search
                                </button>
                            </div>
                        </div>

                        <!-- Buttons -->
                       
                    </div>
                </form>
            </div>

            <!-- Questions Table -->
            <div class="table-responsive shadow-sm rounded">
                <table class="table table-borderless align-middle text-nowrap mb-0">
                    <thead class="border-bottom">
                        <tr>
                            <th>SL</th>
                            <th>Question Title</th>
                            <th>Date Asked</th>
                            <th>Category</th>
                            <th>Expert Assigned</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($questions as $index => $question)
                        <tr>
                            <td>{{ $questions->firstItem() + $index }}</td>
                            <td>{{ Str::limit(optional($question->firstMessage)->message, 60) }}</td>
                            <td>{{ $question->started_at->format('M d, Y') }}</td>
                            <td>{{ $question->category?->name ?? '-' }}</td>
                            <td>{{ optional($question->expert)->f_name . ' ' . optional($question->expert)->l_name ?? 'Not Assigned' }}</td>
                            <td>
                                <span class="badge 
                                    {{ $question->status == 'active' ? 'bg-success' : 
                                       ($question->status == 'pending' ? 'bg-warning' : 'bg-secondary') }}">
                                    {{ ucfirst($question->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('chat.view', $question->id) }}" class="btn btn-sm view-btn">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No questions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="custom-pagination-wrapper d-flex justify-content-end mt-4">
                {{ $questions->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</section>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2-category').select2({
        placeholder: "All Categories",
        allowClear: true
    });

    // Auto submit on Category or Status change
    $('#qCategorySelect, #qStatusSelect').on('change', function() {
        $('#questionsFilterForm')[0].submit();
    });

    // Enter key in search box submits form
    $('input[name="q_search"]').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#questionsFilterForm')[0].submit();
        }
    });
});
</script>
@endpush