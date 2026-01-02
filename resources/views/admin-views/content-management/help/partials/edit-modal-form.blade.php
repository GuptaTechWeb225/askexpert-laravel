@php $data = $data ?? []; @endphp

@if($section == 'hero')
    <div class="mb-3">
        <label class="form-label">Background Image</label>
        <input type="file" name="bg_image" class="form-control" accept="image/*">
        @if($data['bg_image'] ?? '')
            <img src="{{ asset($data['bg_image']) }}" class="mt-2 img-thumbnail" width="150">
        @endif
    </div>
    <div class="mb-3">
        <label class="form-label">Heading Line 1</label>
        <input name="heading1" class="form-control" value="{{ old('heading1', $data['heading1'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Heading Line 2</label>
        <input name="heading2" class="form-control" value="{{ old('heading2', $data['heading2'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Paragraph</label>
        <textarea name="paragraph" class="form-control" rows="4">{{ old('paragraph', $data['paragraph'] ?? '') }}</textarea>
    </div>
@endif

@if($section == 'quick_buttons')
    <div class="mb-3">
        <label class="form-label">Button Text</label>
        <input name="text" class="form-control" value="{{ old('text', $data['text'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Link</label>
        <input name="link" class="form-control" value="{{ old('link', $data['link'] ?? '') }}">
    </div>
@endif

@php $faqSections = ['faq_general', 'faq_billing', 'faq_account', 'faq_experts']; @endphp
@if(in_array($section, $faqSections))
    <div class="mb-3">
        <label class="form-label">Question</label>
        <input name="question" class="form-control" value="{{ old('question', $data['question'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Answer</label>
        <textarea name="answer" class="form-control" rows="5">{{ old('answer', $data['answer'] ?? '') }}</textarea>
    </div>
@endif

@if($section == 'knowledge_base')
    <div class="mb-3">
        <label class="form-label">Title</label>
        <input name="title" class="form-control" value="{{ old('title', $data['title'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Short Description (for card)</label>
        <textarea name="short_desc" class="form-control" rows="3">{{ old('short_desc', $data['short_desc'] ?? '') }}</textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Full Answer (for Read More)</label>
        <textarea name="full_answer" class="form-control" rows="8">{{ old('full_answer', $data['full_answer'] ?? '') }}</textarea>
    </div>
@endif