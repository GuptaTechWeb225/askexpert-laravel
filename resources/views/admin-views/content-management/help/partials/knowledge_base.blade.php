@php $items = app('App\Http\Controllers\Admin\Cms\HelpController')::getSectionDataStatic('knowledge_base'); @endphp

<div class="d-flex justify-content-between mb-3">
    <h6>Knowledge Base</h6>
    <button onclick="openModal('knowledge_base', 0, true)" class="btn btn-success btn-sm">Add Article</button>
</div>

<div class="row g-3">
    @foreach($items as $id => $item)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body d-flex flex-column">
                <h6 class="card-title">{{ $item['title'] ?? '' }}</h6>
                <p class="card-text flex-grow-1">{{ Str::limit($item['short_desc'] ?? '', 80) }}</p>
                <div class="mt-2">
                    <button onclick="openModal('knowledge_base', {{ $id }})" class="btn btn-sm btn-warning">                                    <i class="tio-edit"></i>
</button>
                    <button onclick="deleteItem('knowledge_base', {{ $id }})" class="btn btn-sm btn-danger">                                    <i class="tio-delete"></i>
</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>