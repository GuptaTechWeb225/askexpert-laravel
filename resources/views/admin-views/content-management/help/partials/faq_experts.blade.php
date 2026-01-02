{{-- resources/views/admin-views/content-management/help/partials/faq_experts.blade.php --}}
@php $items = app('App\Http\Controllers\Admin\Cms\HelpController')::getSectionDataStatic('faq_experts'); @endphp

<div class="d-flex justify-content-between mb-3">
    <h6>Experts FAQ</h6>
    <button onclick="openModal('faq_experts', 0, true)" class="btn btn-success btn-sm">Add FAQ</button>
</div>

@foreach($items as $id => $faq)
<div class="border p-3 mb-2 d-flex justify-content-between">
    <div>
        <strong>Q:</strong> {{ $faq['question'] ?? '' }}<br>
        <small><strong>A:</strong> {{ Str::limit($faq['answer'] ?? '', 100) }}</small>
    </div>
    <div>
        <button onclick="openModal('faq_experts', {{ $id }})" class="btn btn-sm btn-warning">                                    <i class="tio-edit"></i>
</button>
        <button onclick="deleteItem('faq_experts', {{ $id }})" class="btn btn-sm btn-danger">                                    <i class="tio-delete"></i>
</button>
    </div>
</div>
@endforeach