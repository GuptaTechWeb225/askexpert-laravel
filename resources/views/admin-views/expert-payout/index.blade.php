@extends('layouts.back-end.app')
@section('title', translate('Revenue & Payouts'))
@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset('public/assets/back-end/img/expert-category.png') }}" alt="">
            {{ translate('Revenue & Payouts') }}
        </h2>

    </div>
    <div class="row">
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('Pending Payouts')}}</h5>
                <div class="mt-2 d-flex justify-content-between align-items-center w-100">
                    <h2 class="business-analytics__title">${{ number_format($pendingPayouts, 2) }}</h2>
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/categories/categories-pricing-icon-1.png')}}" width="40" height="40" class="" alt="">
                </div>

            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('Total Paid to Experts')}}</h5>
                <div class="mt-2 d-flex justify-content-between align-items-center w-100">
                    <h2 class="business-analytics__title">${{ number_format($totalPaid, 2) }}</h2>
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/categories/customer-icon-1.png')}}" width="40" height="40" class="" alt="">
                </div>

            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('Platform Commission Earned')}}</h5>
                <div class="mt-2 d-flex justify-content-between align-items-center w-100">
                    <h2 class="business-analytics__title">${{ number_format($platformCommission, 2) }}</h2>
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/categories/categories-pricing-icon-3.png')}}" width="40" height="40" class="" alt="">
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('Total Revenue')}}</h5>
                <div class="mt-2 d-flex justify-content-between align-items-center w-100">
                    <h2 class="business-analytics__title">${{ number_format($totalRevenue, 2) }}</h2>
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/categories/categories-pricing-icon-4.png')}}" width="40" height="40" class="" alt="">
                </div>

            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header align-items-center">
            <form method="GET">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select name="category" class="form-control  select2-enable">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search expert..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn--primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="table-responsive datatable-custom">
            <table
                style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>SL</th>
                        <th>Expert Name</th>
                        <th>Category</th>
                        <th>Total Sessions</th>
                        <th>Earnings</th>
                        <th>Payout Due</th>
                        <th>Last Payout</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($experts as $index => $expert)
                    <tr>
                        <td>{{ $experts->firstItem() + $index }}</td>
                        <td>{{ $expert->f_name }} {{ $expert->l_name }}</td>
                        <td>{{ $expert->category?->name ?? 'N/A' }}</td>
                        <td>{{ $expert->earnings()->count() }}</td>
                        <td>${{ number_format($expert->earnings()->sum('total_amount'), 2) }}</td>
                        <td>${{ number_format($expert->earnings()->where('status','pending')->sum('total_amount'), 2) }}</td>
                        <td>{{ $expert->earnings()->where('status','paid')->latest('paid_at')->first()?->paid_at?->format('M d, Y') ?? 'Never' }}</td>
                        <td>
                            <button class="btn btn-outline--primary btn-sm" onclick="viewExpert({{ $expert->id }})">
                                <i class="fa fa-eye"></i>
                            </button>
                            <a href="{{ route('admin.expert-payouts.setup', $expert->id) }}" class="btn btn-outline-dark btn-sm">
                                <i class="tio-tune"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $experts->links() !!}
            </div>
        </div>
        @if(count($experts)==0)
        @include('layouts.back-end._empty-state',['text'=>'No_Payout_Found'],['image'=>'default'])
        @endif
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="viewModalContent"></div>
    </div>
</div>
@endsection


@push('script')
<script>
function viewExpert(id) {
    $.get("{{ url('admin/expert-payouts/view') }}/" + id, function(data) {
        $('#viewModalContent').html(data.view);
        $('#viewModal').modal('show');
    });
}
</script>
@endpush