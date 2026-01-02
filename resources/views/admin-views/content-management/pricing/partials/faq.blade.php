@php $faqs = app('App\Http\Controllers\Admin\Cms\PricingController')::getSectionDataStatic('faq'); @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0"></h6>
    <button onclick="openModal('faq', 0, true)" class="btn btn-sm btn-success">Add New</button>
</div>

@foreach($faqs as $id => $faq)
<div class="card mb-2">
    <div class="card-body">
        <strong>Q: {{ $faq['question'] ?? '' }}</strong>
        <p>A: {{ $faq['answer'] ?? '' }}</p>
        <div class="text-end">
            <button onclick="openModal('faq', {{ $id }})" class="btn btn-sm btn-outline-warning">                            <i class="tio-edit"></i>
</button>
            <form method="POST"
                action="{{ route('admin.pricing-cms.destroy', ['faq', $id]) }}"
                style="display:inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                    onclick="return confirm('Are you sure you want to delete this item?')">
                    <i class="tio-delete"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endforeach