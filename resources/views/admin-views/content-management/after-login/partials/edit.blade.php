{{-- resources/views/admin-views/content-management/home/partials/edit-modal-form.blade.php --}}

@php
$data = $data ?? [];
@endphp

{{-- HERO --}}
@if($section == 'hero')
<div class="mb-3">
    <label>Background Image</label>
    <input type="file" name="bg_image" class="form-control" accept="image/*">
    @if($data['bg_image'] ?? '')
    <img src="{{ asset($data['bg_image']) }}" class="mt-2 img-thumbnail" width="150">
    @endif
</div>
<div class="mb-3">
    <label>Heading</label>
    <input name="heading" class="form-control" value="{{ old('heading', $data['heading'] ?? '') }}">
</div>
<div class="mb-3">
    <label>Paragraph</label>
    <textarea name="paragraph" class="form-control" rows="3">{{ old('paragraph', $data['paragraph'] ?? '') }}</textarea>
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


@if($section == 'my_questions')
<div class="mb-3">
    <label>Background Image</label>
    <input type="file" name="bg_image" class="form-control" accept="image/*">
    @if($data['bg_image'] ?? '')
    <img src="{{ asset($data['bg_image']) }}" class="mt-2 img-thumbnail" width="150">
    @endif
</div>
<div class="mb-3">
    <label>Heading</label>
    <input name="heading" class="form-control" value="{{ old('heading', $data['heading'] ?? '') }}">
</div>
<div class="mb-3">
    <label>Paragraph</label>
    <textarea name="paragraph" class="form-control" rows="3">{{ old('paragraph', $data['paragraph'] ?? '') }}</textarea>
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

@if($section == 'my_experts')
<div class="mb-3">
    <label>Background Image</label>
    <input type="file" name="bg_image" class="form-control" accept="image/*">
    @if($data['bg_image'] ?? '')
    <img src="{{ asset($data['bg_image']) }}" class="mt-2 img-thumbnail" width="150">
    @endif
</div>
<div class="mb-3">
    <label>Heading</label>
    <input name="heading" class="form-control" value="{{ old('heading', $data['heading'] ?? '') }}">
</div>
<div class="mb-3">
    <label>Paragraph</label>
    <textarea name="paragraph" class="form-control" rows="3">{{ old('paragraph', $data['paragraph'] ?? '') }}</textarea>
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