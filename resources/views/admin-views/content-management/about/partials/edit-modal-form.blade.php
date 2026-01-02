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
    <div class="mb-3"><label>Heading Line 1</label><input name="heading1" class="form-control" value="{{ old('heading1', $data['heading1'] ?? '') }}"></div>
    <div class="mb-3"><label>Heading Line 2</label><input name="heading2" class="form-control" value="{{ old('heading2', $data['heading2'] ?? '') }}"></div>
    <div class="mb-3"><label>Paragraph</label><textarea name="paragraph" class="form-control" rows="4">{{ old('paragraph', $data['paragraph'] ?? '') }}</textarea></div>
    <div class="mb-3"><label>Search Placeholder</label><input name="search_placeholder" class="form-control" value="{{ old('search_placeholder', $data['search_placeholder'] ?? '') }}"></div>
    <div class="mb-3"><label>Start Chat Link</label><input name="start_chat_link" class="form-control" value="{{ old('start_chat_link', $data['start_chat_link'] ?? '') }}"></div>
@endif

{{-- QUICK BUTTONS --}}
@if($section == 'quick_buttons')
    <div class="mb-3"><label>Button Text</label><input name="text" class="form-control" value="{{ old('text', $data['text'] ?? '') }}"></div>
    <div class="mb-3"><label>Link</label><input name="link" class="form-control" value="{{ old('link', $data['link'] ?? '') }}"></div>
@endif

{{-- OUR MISSION --}}
@if($section == 'our_mission')
    @if($item_id == 0)
        <div class="mb-3"><label>Title</label><input name="title" class="form-control" value="{{ old('title', $data['title'] ?? '') }}"></div>
        <div class="mb-3"><label>Paragraph 1</label><textarea name="paragraph1" class="form-control" rows="3">{{ old('paragraph1', $data['paragraph1'] ?? '') }}</textarea></div>
        <div class="mb-3"><label>Paragraph 2</label><textarea name="paragraph2" class="form-control" rows="3">{{ old('paragraph2', $data['paragraph2'] ?? '') }}</textarea></div>
    @else
        <div class="mb-3"><label>Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            @if($data['image'] ?? '') 
                <img src="{{ asset($data['image']) }}" class="mt-2 img-thumbnail" width="100">
            @endif
        </div>
    @endif
@endif

{{-- DIFFERENCE --}}
@if($section == 'difference')
    <div class="mb-3"><label>Icon</label>
        <input type="file" name="icon" class="form-control" accept="image/*">
        @if($data['icon'] ?? '') <img src="{{ asset($data['icon']) }}" width="50" class="mt-2"> @endif
    </div>
    <div class="mb-3"><label>Title</label><input name="title" class="form-control" value="{{ old('title', $data['title'] ?? '') }}"></div>
    <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $data['description'] ?? '') }}</textarea></div>
@endif

{{-- WE HELP --}}
@if($section == 'we_help')
    <div class="mb-3"><label>Title</label><input name="title" class="form-control" value="{{ old('title', $data['title'] ?? '') }}"></div>
    <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $data['description'] ?? '') }}</textarea></div>
@endif

{{-- ACHIEVEMENTS --}}
@if($section == 'achievements')
    <div class="mb-3"><label>Icon</label>
        <input type="file" name="icon" class="form-control" accept="image/*">
        @if($data['icon'] ?? '') <img src="{{ asset($data['icon']) }}" width="50" class="mt-2"> @endif
    </div>
    <div class="mb-3"><label>Number</label><input name="number" class="form-control" value="{{ old('number', $data['number'] ?? '') }}"></div>
    <div class="mb-3"><label>Text</label><input name="text" class="form-control" value="{{ old('text', $data['text'] ?? '') }}"></div>
@endif