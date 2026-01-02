@php $buttons = app('App\Http\Controllers\Admin\Cms\HelpController')::getSectionDataStatic('quick_buttons'); @endphp

<div class="d-flex justify-content-between mb-3">
    <h6>Quick Buttons</h6>
    <button onclick="openModal('quick_buttons', 0, true)" class="btn btn-success btn-sm">Add New</button>
</div>

@foreach($buttons as $id => $btn)
<div class="border p-3 mb-2 d-flex justify-content-between align-items-center">
    <div>
        <strong>{{ $btn['text'] ?? '' }}</strong> → {{ $btn['link'] ?? '' }}
    </div>
    <div>
        <button onclick="openModal('quick_buttons', {{ $id }})" class="btn btn-sm btn-warning">                                    <i class="tio-edit"></i>
</button>
        <button onclick="deleteItem('quick_buttons', {{ $id }})" class="btn btn-sm btn-danger">                                    <i class="tio-delete"></i>
</button>
    </div>
</div>
@endforeach