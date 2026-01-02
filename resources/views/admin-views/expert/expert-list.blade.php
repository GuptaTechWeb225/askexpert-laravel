@extends('layouts.back-end.app')

@section('title', translate('Experts'))

@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
            {{translate('Experts')}}
        </h2>
    </div>
    <div class="row">
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('Total Experts')}}</h5>
                <div class="mt-2 d-flex justify-content-between align-items-center w-100">
                    <h2 class="business-analytics__title">{{ $totalExperts }}</h2>
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/categories/customer-icon-1.png')}}" width="45" height="45" class="" alt="">
                </div>

            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('Online Expert Now')}}</h5>
                <div class="mt-2 d-flex justify-content-between align-items-center w-100">
                    <h2 class="business-analytics__title">{{ $onlineExperts }}</h2>
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/categories/customer-icon-2.png')}}" width="45" height="45" class="" alt="">
                </div>

            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('Pending Applications')}}</h5>
                <div class="mt-2 d-flex justify-content-between align-items-center w-100">
                    <h2 class="business-analytics__title">{{ $pendingExperts }}</h2>
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/categories/admin-dash-card-4.png')}}" width="45" height="45" class="" alt="">
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('Blocked Experts')}}</h5>
                <div class="mt-2 d-flex justify-content-between align-items-center w-100">
                    <h2 class="business-analytics__title">{{ $blockExperts }}</h2>
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/categories/customer-icon-3.png')}}" width="45" height="45" class="" alt="">
                </div>

            </a>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ url()->current() }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{translate('Expert_Joining_Date')}}</label>
                        <div class="position-relative">
                            <span class="tio-calendar icon-absolute-on-right cursor-pointer"></span>
                            <input type="text" name="expert_joining_date" class="js-daterangepicker-with-range form-control cursor-pointer" value="{{request('expert_joining_date')}}" placeholder="{{ translate('Select_Date') }}" autocomplete="off" readonly>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{translate('Sort_By_Category') }}</label>
                        <select class="form-control js-select2-custom" name="category">
                            <option value="" disabled selected>Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{translate('expert_Status')}}</label>
                        <select class="form-control js-select2-custom set-filter" name="is_active">
                            <option {{ !request()->has('is_active') ?'selected':''}} disabled>{{ translate('select_status') }}</option>
                            <option {{ request()->has('is_active') && request('is_active') == '' ?'selected':''}} value="">{{ translate('All') }}</option>
                            <option {{ request('is_active')  == '1'?'selected':''}} value="1">{{ translate('Active') }}</option>
                            <option {{ request('is_active')  == '0'?'selected':''}} value="0">{{ translate('Inactive') }}</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="d-md-block">&nbsp;</label>
                        <div class="btn--container justify-content-end">
                            <a href="{{ route('admin.expert.index') }}"
                                class="btn btn-secondary px-5">
                                {{ translate('reset') }}
                            </a>
                            <button type="submit" class="btn btn--primary">{{translate('Filter')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 mr-auto">
                {{translate('Experts')}}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $totalExperts }}</span>
            </h5>

            <form action="{{ url()->current() }}" method="GET">
                <input type="hidden" name="expert_joining_date" value="{{request('expert_joining_date')}}">
                <input type="hidden" name="sort_by" value="{{request('sort_by')}}">
                <input type="hidden" name="category" value="{{request('category')}}">
                <div class="input-group input-group-merge input-group-custom">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <i class="tio-search"></i>
                        </div>
                    </div>
                    <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                        placeholder="{{ translate('search_by_Name_or_Email_or_Phone')}}" aria-label="Search orders" value="{{ request('searchValue') }}">
                    <button type="submit" class="btn btn--primary">{{ translate('search')}}</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table style="text-align: {{Session::get('direction') === " rtl" ? 'right' : 'left' }};"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light text-capitalize">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('expert_Name')}}</th>
                        <th>{{translate('contact_info')}}</th>
                        <th>{{translate('Category')}}</th>
                        <th>{{translate('Join_Date')}}</th>
                        <th>{{translate('Rating')}}</th>
                        <th>{{translate('Status')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($experts as $key => $business)
                    <tr>
                        <td>{{ $experts->firstItem() + $key }}</td>
                        <td>
                            <a href="{{route('admin.expert.view',[$business['id']])}}"
                                class="title-color hover-c1 d-flex align-items-center gap-10">
                                <img src="{{getStorageImages(path:$business->image_full_url,type:'backend-profile')}}"
                                    class="avatar rounded-circle " alt="" width="40">
                                {{Str::limit($business['f_name']." ".$business['l_name'],20)}}
                            </a>
                        </td>
                        <td>
                            <div class="mb-1">
                                <strong><a class="title-color hover-c1"
                                        href="mailto:{{$business->email}}">{{$business->email}}</a></strong>

                            </div>
                            <a class="title-color hover-c1" href="tel:{{$business->phone}}">{{$business->phone}}</a>

                        </td>
                        <td>{{ $business->category->name ?? 'N/A' }}</td>
                        <td> {{ $business->created_at->format('d M Y, h:i A') ?? 'N/A' }}</td>
                        <td>{{ $business->average_rating }} <i class="fa-solid fa-star text--primary"></i> </td>
                        <td>
                            <form action="{{route('admin.expert.status')}}" method="post"
                                id="business-status{{$business['id']}}-form" class="business-status-form">
                                @csrf
                                <input type="hidden" name="id" value="{{$business['id']}}">
                                <label class="switcher mx-auto">
                                    <input type="checkbox" class="switcher_input toggle-switch-message"
                                        id="business-status{{$business['id']}}" name="is_active" value="1"
                                        {{ $business['is_active'] == 1 ? 'checked':'' }}
                                        data-modal-id="toggle-status-modal"
                                        data-toggle-id="business-status{{$business['id']}}"
                                        data-on-image="customer-block-on.png"
                                        data-off-image="customer-block-off.png"
                                        data-on-title="{{translate('want_to_unblock').' '.$business['f_name'].' '.$business['l_name'].'?'}}"
                                        data-off-title="{{translate('want_to_block').' '.$business['f_name'].' '.$business['l_name'].'?'}}"
                                        data-on-message="<p>{{translate('if_enabled_this_expert_will_be_unblocked_and_can_log_in_to_this_system_again')}}</p>"
                                        data-off-message="<p>{{translate('if_disabled_this_expert_will_be_blocked_and_cannot_log_in_to_this_system')}}</p>">
                                    <span class="switcher_control"></span>
                                </label>
                            </form>
                        </td>
                        <td class="">
                            <a title="{{translate('view')}}"
                                class="btn btn-outline--primary btn-sm square-btn"
                                href="{{route('admin.expert.view',[$business['id']])}}">
                                <i class="tio-invisible"></i>
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
        @include('layouts.back-end._empty-state',['text'=>'No_requests_found'],['image'=>'default'])
        @endif
    </div>
</div>


@push('script_2')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.querySelectorAll('.swal-approve-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            Swal.fire({
                title: "Are you sure to approve?",
                text: "",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, approve",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.closest('form').submit();
                }
            });
        });
    });

    // Reject
    document.querySelectorAll('.swal-reject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            Swal.fire({
                title: "Are you sure to reject?",
                input: 'text', // input box
                inputLabel: 'Reason for rejection',
                inputPlaceholder: 'Enter reason here...',
                inputAttributes: {
                    'aria-label': 'Type your reason'
                },
                showCancelButton: true,
                confirmButtonText: "Reject",
                cancelButtonText: "Cancel",
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                preConfirm: (reason) => {
                    if (!reason) {
                        Swal.showValidationMessage('Reason is required');
                    }
                    return reason;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // create hidden input for reason
                    let form = btn.closest('form');
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'reject_reason';
                    input.value = result.value;
                    form.appendChild(input);

                    form.submit();
                }
            });
        });
    });
</script>
@endpush

@endsection