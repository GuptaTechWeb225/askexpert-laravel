{{-- resources/views/admin-views/content-management/home/partials/edit-modal-form.blade.php --}}

@php
    $data = $data ?? [];
@endphp

{{-- HERO --}}
@if($section == 'hero')
    <div class="mb-3">
        <label>Heading</label>
        <input name="heading" class="form-control" value="{{ old('heading', $data['heading'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Paragraph</label>
        <textarea name="paragraph" class="form-control" rows="3">{{ old('paragraph', $data['paragraph'] ?? '') }}</textarea>
    </div>
    <div class="mb-3">
        <label>Background Video</label>
        <input type="file" name="bg_video" class="form-control" accept="video/mp4">
        @if($data['bg_video'] ?? '') <small>Current: {{ basename($data['bg_video']) }}</small> @endif
    </div>
    <div class="mb-3">
        <label>Search Placeholder</label>
        <input name="search_placeholder" class="form-control" value="{{ old('search_placeholder', $data['search_placeholder'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Start Chat Link</label>
        <input name="start_chat_link" class="form-control" value="{{ old('start_chat_link', $data['start_chat_link'] ?? '') }}">
    </div>
@endif

{{-- QUICK BUTTONS --}}
@if($section == 'quick_buttons')
    <div class="mb-3">
        <label>Button Text</label>
        <input name="text" class="form-control" value="{{ old('text', $data['text'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Link</label>
        <input name="link" class="form-control" value="{{ old('link', $data['link'] ?? '') }}">
    </div>
@endif

{{-- EXPERT CATEGORIES --}}
@if($section == 'expert_categories')
    <div class="mb-3">
        <label>Name</label>
        <input name="name" class="form-control" value="{{ old('name', $data['name'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Expert Count</label>
        <input name="expert_count" type="number" class="form-control" value="{{ old('expert_count', $data['expert_count'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Link</label>
        <input name="link" class="form-control" value="{{ old('link', $data['link'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Icon</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        @if($data['image'] ?? '') <img src="{{ asset($data['image']) }}" width="50" class="me-2"> @endif
    </div>
@endif

{{-- POPULAR QUESTIONS --}}
@if($section == 'popular_questions')
    <div class="mb-3">
        <label>Title</label>
        <input name="title" class="form-control" value="{{ old('title', $data['title'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $data['description'] ?? '') }}</textarea>
    </div>
    <div class="mb-3">
        <label>Image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        @if($data['image'] ?? '') <img src="{{ asset($data['image']) }}" width="100"> @endif
    </div>
    <div class="mb-3">
        <label>Link</label>
        <input name="link" class="form-control" value="{{ old('link', $data['link'] ?? '') }}">
    </div>
@endif

{{-- HOW IT WORKS --}}
@if($section == 'how_it_works')
    <div class="mb-3">
        <label>Title</label>
        <input name="title" class="form-control" value="{{ old('title', $data['title'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $data['description'] ?? '') }}</textarea>
    </div>
    <div class="mb-3">
        <label>Image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        @if($data['image'] ?? '') <img src="{{ asset($data['image']) }}" width="100"> @endif
    </div>
@endif

{{-- WHY LOVE --}}
@if($section == 'why_love')
    <div class="mb-3">
        <label>Title</label>
        <input name="title" class="form-control" value="{{ old('title', $data['title'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $data['description'] ?? '') }}</textarea>
    </div>
    <div class="mb-3">
        <label>Icon</label>
        <input type="file" name="icon" class="form-control" accept="image/*">
        @if($data['icon'] ?? '') <img src="{{ asset($data['icon']) }}" width="50"> @endif
    </div>
@endif

{{-- TESTIMONIALS --}}
@if($section == 'testimonials')
    <div class="mb-3">
        <label>Title</label>
        <input name="title" class="form-control" value="{{ old('title', $data['title'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $data['description'] ?? '') }}</textarea>
    </div>
    <div class="mb-3">
        <label>Name</label>
        <input name="name" class="form-control" value="{{ old('name', $data['name'] ?? '') }}">
    </div>
@endif

{{-- EXPERTS --}}
@if($section == 'experts')
    <div class="mb-3">
        <label>Name</label>
        <input name="name" class="form-control" value="{{ old('name', $data['name'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Title</label>
        <input name="title" class="form-control" value="{{ old('title', $data['title'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Rating (1-5)</label>
        <input name="rating" type="number" step="0.1" min="1" max="5" class="form-control" value="{{ old('rating', $data['rating'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $data['description'] ?? '') }}</textarea>
    </div>
    <div class="mb-3">
        <label>Image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        @if($data['image'] ?? '') <img src="{{ asset($data['image']) }}" width="60" class="rounded-circle"> @endif
    </div>
@endif