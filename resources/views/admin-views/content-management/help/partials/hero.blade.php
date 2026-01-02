@php $hero = app('App\Http\Controllers\Admin\Cms\HelpController')::getSectionDataStatic('hero')[0] ?? []; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6>Hero Section</h6>
    <button onclick="openModal('hero', 0)" class="btn btn-sm btn-primary">                                    <i class="tio-edit"></i>
Edit</button>
</div>

<table class="table table-bordered">
    <tr><th>Heading</th><td>{{ $hero['heading1'] ?? '' }} {{ $hero['heading2'] ?? '' }}</td></tr>
    <tr><th>Paragraph</th><td>{{ Str::limit($hero['paragraph'] ?? '', 100) }}</td></tr>
    <tr><th>Image</th><td>
        @if($hero['bg_image'] ?? '')
            <img src="{{ asset($hero['bg_image']) }}" width="100" class="img-thumbnail">
        @endif
    </td></tr>
</table>