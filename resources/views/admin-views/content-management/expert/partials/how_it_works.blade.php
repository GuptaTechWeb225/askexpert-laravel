@php 
    $main   = app('App\Http\Controllers\Admin\Cms\ExpertCmsController')::getSectionDataStatic('how_it_works')[0] ?? [];
    $images = app('App\Http\Controllers\Admin\Cms\ExpertCmsController')::getSectionDataStatic('how_it_works');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">How It Works</h6>
    <div class="d-flex gap-2">
        <button onclick="openModal('how_it_works',0)" class="btn btn-sm btn-primary"><i class="tio-edit"></i> Edit Text</button>
    </div>
</div>

{{-- Text preview --}}
<p class="fw-bold">{{ $main['badge_number'] ?? '' }} <small>{{ $main['badge_text'] ?? '' }}</small></p>


<table class="table table-hover table-borderless mt-3">
    <thead class="thead-light"><tr><th>#</th><th>Image</th><th>Alt</th><th class="text-center">Action</th></tr></thead>
    <tbody>
        @foreach($images as $id => $img)
            @if($id > 0)
            <tr>
                <td>{{ $loop->iteration-1 }}</td>
                <td>@if($img['image']??'')<img src="{{asset($img['image'])}}" width="100">@endif</td>
                <td>{{ $img['alt'] ?? '-' }}</td>
                <td class="text-center">
                    <button onclick="openModal('how_it_works',{{ $id }})" class="btn btn-sm btn-outline-warning"><i class="tio-edit"></i></button>
                </td>
            </tr>
            @endif
        @endforeach
        @if(count($images) <= 1)
        <tr><td colspan="4" class="text-center text-muted py-3">No images yet</td></tr>
        @endif
    </tbody>
</table>

