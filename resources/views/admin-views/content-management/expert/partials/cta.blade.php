@php $cta = app('App\Http\Controllers\Admin\Cms\ExpertCmsController')::getSectionDataStatic('cta')[0] ?? []; @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Bottom CTA Card</h6>
    <button onclick="openModal('cta',0)" class="btn btn-sm btn-primary"><i class="tio-edit"></i> Edit</button>
</div>

<table class="table table-borderless">
    <tbody>
        <tr><td><strong>Title</strong></td><td>{{ $cta['title'] ?? '-' }}</td></tr>
        <tr><td><strong>Paragraph</strong></td><td>{!! nl2br($cta['paragraph'] ?? '-') !!}</td></tr>
        <tr><td><strong>Button</strong></td><td>{{ $cta['btn_text'] ?? '-' }} → {{ $cta['btn_link'] ?? '-' }}</td></tr>
    </tbody>
</table>