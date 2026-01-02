@php $items = app('App\Http\Controllers\Admin\Cms\ExpertCmsController')::getSectionDataStatic('why_join'); @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Why Join</h6>
    <button onclick="openModal('why_join',0,true)" class="btn btn-sm btn-success"><i class="tio-add"></i> Add New</button>
</div>

<table class="table table-hover table-borderless">
    <thead class="thead-light"><tr>
        <th>#</th><th>Icon</th><th>Title</th><th>Description</th><th class="text-center">Action</th>
    </tr></thead>
    <tbody>
        @foreach($items as $id => $i)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>@if($i['icon']??'')<img src="{{asset($i['icon'])}}" width="40">@endif</td>
            <td>{{ $i['title'] ?? '-' }}</td>
            <td>{{ $i['description'] ?? '-' }}</td>
            <td class="text-center">
                <button onclick="openModal('why_join',{{ $id }})" class="btn btn-sm btn-outline-warning"><i class="tio-edit"></i></button>
                <button onclick="deleteItem('why_join',{{ $id }})" class="btn btn-sm btn-outline-danger"><i class="tio-delete"></i></button>
            </td>
        </tr>
        @endforeach
        @if(empty($items))
        <tr><td colspan="5" class="text-center text-muted py-3">No items yet</td></tr>
        @endif
    </tbody>
</table>