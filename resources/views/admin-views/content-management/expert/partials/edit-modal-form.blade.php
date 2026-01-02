@php $data = $data ?? []; @endphp

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
    <div class="mb-3"><label>Apply Button Link</label><input name="apply_link" class="form-control" value="{{ old('apply_link', $data['apply_link'] ?? '') }}"></div>
@endif

{{-- WHY JOIN ---------------------------------------------------- --}}
@if($section == 'why_join')
    <div class="mb-3"><label>Icon</label>
        <input type="file" name="icon" class="form-control" accept="image/*">
        @if($data['icon'] ?? '') <img src="{{ asset($data['icon']) }}" width="50" class="mt-2"> @endif
    </div>
    <div class="mb-3"><label>Title</label><input name="title" class="form-control" value="{{ old('title', $data['title'] ?? '') }}"></div>
    <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $data['description'] ?? '') }}</textarea></div>
@endif

{{-- HOW IT WORKS ---------------------------------------------------- --}}
@if($section == 'how_it_works')
    @if($item_id == 0)   {{-- main text block --}}
        <div class="mb-3"><label>Badge Number (10k+)</label><input name="badge_number" class="form-control" value="{{ old('badge_number', $data['badge_number'] ?? '') }}"></div>
        <div class="mb-3"><label>Badge Text</label><input name="badge_text" class="form-control" value="{{ old('badge_text', $data['badge_text'] ?? '') }}"></div>
        <div class="mb-3"><label>Join Button Link</label><input name="join_link" class="form-control" value="{{ old('join_link', $data['join_link'] ?? '') }}"></div>
    @else               {{-- extra images (female / male) --}}
        <div class="mb-3"><label>Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            @if($data['image'] ?? '') <img src="{{ asset($data['image']) }}" width="120" class="mt-2"> @endif
        </div>
        <div class="mb-3"><label>Alt Text</label><input name="alt" class="form-control" value="{{ old('alt', $data['alt'] ?? '') }}"></div>
    @endif
@endif

{{-- TESTIMONIALS ---------------------------------------------------- --}}
@if($section == 'testimonials')
    <div class="mb-3"><label>Expert Image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        @if($data['image'] ?? '') <img src="{{ asset($data['image']) }}" width="80" class="mt-2"> @endif
    </div>
    <div class="mb-3"><label>Name & Title</label><input name="name" class="form-control" value="{{ old('name', $data['name'] ?? '') }}"></div>
    <div class="mb-3"><label>Quote</label><textarea name="quote" class="form-control" rows="3">{{ old('quote', $data['quote'] ?? '') }}</textarea></div>
@endif

{{-- CTA CARD ---------------------------------------------------- --}}
@if($section == 'cta')
    <div class="mb-3"><label>Title</label><input name="title" class="form-control" value="{{ old('title', $data['title'] ?? '') }}"></div>
    <div class="mb-3"><label>Paragraph</label><textarea name="paragraph" class="form-control" rows="2">{{ old('paragraph', $data['paragraph'] ?? '') }}</textarea></div>
    <div class="mb-3"><label>Button Text</label><input name="btn_text" class="form-control" value="{{ old('btn_text', $data['btn_text'] ?? '') }}"></div>
    <div class="mb-3"><label>Button Link</label><input name="btn_link" class="form-control" value="{{ old('btn_link', $data['btn_link'] ?? '') }}"></div>
@endif