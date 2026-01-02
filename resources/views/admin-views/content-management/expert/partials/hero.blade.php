@php $hero = app('App\Http\Controllers\Admin\Cms\ExpertCmsController')::getSectionDataStatic('hero')[0] ?? []; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Hero Section</h6>
    <button onclick="openModal('hero',0)" class="btn btn-sm btn-primary"><i class="tio-edit"></i> Edit</button>
</div>

<table class="table table-borderless table-align-middle">
    <tbody>
        <tr><td><strong>Heading 1</strong></td><td>{{ $hero['heading1'] ?? '-' }}</td></tr>
        <tr><td><strong>Heading 2</strong></td><td>{{ $hero['heading2'] ?? '-' }}</td></tr>
        <tr><td><strong>Paragraph</strong></td><td>{!! nl2br($hero['paragraph'] ?? '-') !!}</td></tr>
        <tr><td><strong>Background</strong></td><td>
            @if($hero['bg_image'] ?? '')
                <img src="{{ asset($hero['bg_image']) }}" width="120" class="rounded">
            @else
                <span class="badge badge-soft-secondary">No image</span>
            @endif
        </td></tr>
    </tbody>
</table>