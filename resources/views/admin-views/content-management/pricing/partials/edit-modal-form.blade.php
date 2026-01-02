@php $data = $data ?? []; @endphp

{{-- HERO --}}
@if($section == 'hero')
    <div class="mb-3">
        <label>Background Image</label>
        <input type="file" name="bg_image" class="form-control" accept="image/*">
        @if($data['bg_image'] ?? '') 
            <img src="{{ asset($data['bg_image']) }}" class="mt-2 img-thumbnail" width="150">
        @endif
    </div>
    <div class="mb-3"><label>Heading Line 1</label><input name="heading1" class="form-control" value="{{ $data['heading1'] ?? '' }}"></div>
    <div class="mb-3"><label>Heading Line 2</label><input name="heading2" class="form-control" value="{{ $data['heading2'] ?? '' }}"></div>
    <div class="mb-3"><label>Paragraph</label><textarea name="paragraph" class="form-control" rows="3">{{ $data['paragraph'] ?? '' }}</textarea></div>
    <div class="mb-3"><label>Search Placeholder</label><input name="search_placeholder" class="form-control" value="{{ $data['search_placeholder'] ?? '' }}"></div>
    <div class="mb-3"><label>Start Chat Link</label><input name="start_chat_link" class="form-control" value="{{ $data['start_chat_link'] ?? '' }}"></div>
@endif

{{-- ALL PLANS --}}
@if($section == 'all_plans')
    <div class="mb-3">
        <label>Icon</label>
        <input type="file" name="icon" class="form-control" accept="image/*">
        @if($data['icon'] ?? '') 
            <img src="{{ asset($data['icon']) }}" class="mt-2 img-thumbnail" width="60">
        @endif
    </div>
    <div class="mb-3"><label>Description</label><input name="description" class="form-control" value="{{ $data['description'] ?? '' }}"></div>
@endif

{{-- FAQ --}}
@if($section == 'faq')
    <div class="mb-3"><label>Question</label><input name="question" class="form-control" value="{{ $data['question'] ?? '' }}"></div>
    <div class="mb-3"><label>Answer</label><textarea name="answer" class="form-control" rows="3">{{ $data['answer'] ?? '' }}</textarea></div>
@endif