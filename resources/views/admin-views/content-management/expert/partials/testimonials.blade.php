@php $items = app('App\Http\Controllers\Admin\Cms\ExpertCmsController')::getSectionDataStatic('testimonials'); @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">What Experts Say</h6>
    <button onclick="openModal('testimonials',0,true)" class="btn btn-sm btn-success"><i class="tio-add"></i> Add New</button>
</div>

<table class="table table-hover table-borderless">
    <thead class="thead-light"><tr>
        <th>#</th><th>Image</th><th>Name</th><th>Quote</th><th class="text-center">Action</th>
    </tr></thead>
    <tbody>
        @foreach($items as $id => $t)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>@if($t['image']??'')<img src="{{asset($t['image'])}}" width="60" class="rounded-circle">@endif</td>
            <td>{{ $t['name'] ?? '-' }}</td>
            <td class="small">{{ Str::limit($t['quote'] ?? '-', 80) }}</td>
            <td class="text-center">
                <button onclick="openModal('testimonials',{{ $id }})" class="btn btn-sm btn-outline-warning"><i class="tio-edit"></i></button>
                <button onclick="deleteItem('testimonials',{{ $id }})" class="btn btn-sm btn-outline-danger"><i class="tio-delete"></i></button>
            </td>
        </tr>
        @endforeach
        @if(empty($items))
        <tr><td colspan="5" class="text-center text-muted py-3">No testimonials yet</td></tr>
        @endif
    </tbody>
</table>