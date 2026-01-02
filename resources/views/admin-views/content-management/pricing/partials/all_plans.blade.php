@php $plans = app('App\Http\Controllers\Admin\Cms\PricingController')::getSectionDataStatic('all_plans'); @endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0"></h6>
</div>

<table class="table table-hover table-borderless">
    <thead><tr><th>#</th><th>Icon</th><th>Description</th><th>Action</th></tr></thead>
    <tbody>
        @foreach($plans as $id => $plan)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>@if($plan['icon'] ?? '')<img src="{{ asset($plan['icon']) }}" width="40">@endif</td>
            <td>{{ $plan['description'] ?? '' }}</td>
            <td>
                <button onclick="openModal('all_plans', {{ $id }})" class="btn btn-sm btn-outline-warning"><i class="tio-edit"></i></button>
            </td>
        </tr>
        @endforeach
        @if(empty($plans)) <tr><td colspan="4" class="text-center text-muted">No items</td></tr> @endif
    </tbody>
</table>