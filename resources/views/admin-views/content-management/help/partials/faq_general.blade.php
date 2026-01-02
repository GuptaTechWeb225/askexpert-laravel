@php $items = app('App\Http\Controllers\Admin\Cms\HelpController')::getSectionDataStatic('faq_general'); @endphp

<div class="d-flex justify-content-between mb-3">
    <h6>General FAQ</h6>
    <button onclick="openModal('faq_general', 0, true)" class="btn btn-success btn-sm">Add FAQ</button>
</div>

@foreach($items as $id => $faq)
<div class="border p-3 mb-2 d-flex justify-content-between">
    <div>
        <strong>Q: {{ $faq['question'] ?? '' }}</strong><br>
        <small>A: {{ Str::limit($faq['answer'] ?? '', 100) }}</small>
    </div>
    <div>
        <button onclick="openModal('faq_general', {{ $id }})" class="btn btn-sm btn-warning">                                    <i class="tio-edit"></i>
</button>
        <button onclick="deleteItem('faq_general', {{ $id }})" class="btn btn-sm btn-danger">                                    <i class="tio-delete"></i>
</button>
    </div>
</div>
@endforeach