@extends('layouts.back-end.app')

@section('title', translate('Manage Question'))

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
<style>
    .hs-unfold-content.dropdown-unfold {
        position: absolute !important;
        z-index: 1050 !important;
    }
</style>
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
            {{translate('Manage Question')}}
        </h2>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.expert.questions') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Date Range') }}</label>
                        <div class="position-relative">
                            <span class="tio-calendar icon-absolute-on-right cursor-pointer"></span>
                            <input type="text" name="date_range" class="js-daterangepicker-with-range form-control cursor-pointer"
                                value="{{ request('date_range') }}" placeholder="Select Date Range" readonly>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Category') }}</label>
                        <select name="category" class="form-control js-select2-custom">
                            <option value="">{{ translate('All Categories') }}</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ translate('Status') }}</label>
                        <select name="status" class="form-control js-select2-custom">
                            <option value="">{{ translate('All') }}</option>
                            <option value="waiting" {{ request('status') == 'waiting' ? 'selected' : '' }}>Waiting</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="ended" {{ request('status') == 'ended' ? 'selected' : '' }}>Ended</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <div class="btn--container justify-content-end">
                            <a href="{{ route('admin.expert.questions') }}" class="btn btn-secondary px-5">
                                {{ translate('Reset') }}
                            </a>
                            <button type="submit" class="btn btn--primary">{{ translate('Filter') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 mr-auto">

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
                        <th>{{translate('Customer')}}</th>
                        <th>{{translate('Quetion_Title')}}</th>
                        <th>{{translate('Category')}}</th>
                        <th>{{translate('Date')}}</th>
                        <th>{{translate('Type')}}</th>
                        <th>{{translate('Expert')}}</th>
                        <th>{{translate('Status')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($questions as $key => $session)
                    <tr>
                        <td>{{ $questions->firstItem() + $key }}</td>
                        <td>
                            {{ $session->customer?->f_name ?? 'N/A' }} {{ $session->customer?->l_name ?? '' }}
                        </td>
                        <td>
                            {{ $session->firstMessage?->message ?? 'No message yet' }}
                        </td>
                        <td>
                            {{ $session->category?->name ?? 'Not Assigned' }}
                        </td>
                        <td>
                            {{ $session->started_at->format('d M Y, h:i A') }}
                        </td>
                        <td>
                            <!-- Agar type hai toh daal, warna remove kar -->
                            <span class="badge badge-soft-info">Chat</span>
                        </td>
                        <td>
                            @if($session->expert)
                            {{ $session->expert->f_name }} {{ $session->expert->l_name }}
                            @else
                            <span class="text-warning">Not Assigned</span>
                            @endif
                        </td>
                        <td>
                            @if($session->ended_at)
                            <span class="badge badge-soft-danger">Ended</span>
                            @elseif($session->expert_id)
                            <span class="badge badge-soft-success">Active</span>
                            @else
                            <span class="badge badge-soft-warning">Waiting</span>
                            @endif
                        </td>
                          <td class="text-center">
    <div class="hs-unfold">
        <a class="js-hs-unfold-invoker text-dark"
           href="javascript:"
           data-hs-unfold-options='{
                "target": "#sessionDropdown{{ $session->id }}",
                "type": "css-animation",
                 "smartPositionOff": true
           }'>
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </a>

        <div id="sessionDropdown{{ $session->id }}"
             class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-start z-999">
            <a class="dropdown-item" href="#" onclick="viewChatDetails({{ $session->id }})">
                <i class="fa-solid fa-eye me-2"></i> View Detail
            </a>
            <a class="dropdown-item" href="#" onclick="setSessionId({{ $session->id }})" data-bs-toggle="modal" data-bs-target="#assignExpertModal">
                <i class="fa-solid fa-user-tie me-2"></i> Assign to Expert
            </a>
            <a class="dropdown-item" href="#" onclick="setSessionIdForCategory({{ $session->id }})" data-bs-toggle="modal" data-bs-target="#assignCategoryModal">
                <i class="fa-solid fa-handshake-angle me-2"></i> Assign Category
            </a>
        </div>
    </div>
</td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $questions->links() !!}
            </div>
        </div>
        @if(count($questions)==0)
        @include('layouts.back-end._empty-state',['text'=>'No_requests_found'],['image'=>'default'])
        @endif
    </div>
</div>

<div class="modal fade" id="viewDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Question Details</h5>
                <button type="button" class="btn-close btn btn-close-circle bg--primary" data-bs-dismiss="modal" aria-label="Close"> <i class="tio-clear"></i> </button>
            </div>
            <div class="modal-body" id="viewDetailBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Expert Modal -->
<div class="modal fade" id="assignExpertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Expert</h5>
                <button type="button" class="btn-close btn btn-close-circle bg--primary" data-bs-dismiss="modal" aria-label="Close"> <i class="tio-clear"></i> </button>
            </div>
            <form id="assignExpertForm">
                <div class="modal-body">
                    <input type="hidden" id="sessionIdForExpert" name="session_id">
                    <div class="mb-3">
                        <label>Select Expert</label>
                        <select name="expert_id" class="form-control js-select2-custom" required>
                            <option value="">Choose Expert</option>
                            @foreach(\App\Models\Expert::where('is_active', 1)->get() as $expert)
                            <option value="{{ $expert->id }}">{{ $expert->f_name }} {{ $expert->l_name }} ({{ $expert->category?->name }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Category Modal -->
<div class="modal fade" id="assignCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Category</h5>
                <button type="button" class="btn-close btn btn-close-circle bg--primary" data-bs-dismiss="modal" aria-label="Close"> <i class="tio-clear"></i> </button>
            </div>
            <form id="assignCategoryForm">
                <div class="modal-body">
                    <input type="hidden" id="sessionIdForCategory" name="session_id">
                    <div class="mb-3">
                        <label>Select Category</label>
                        <select name="category_id" class="form-control js-select2-custom" required>
                            <option value="">Choose Category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn--primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<span id="detailRoute" data-url="{{ route('admin.expert.question.detail', ['id' => 'IDPLACEHOLDER']) }}" style="display:none;"></span>

@endsection
@push('script_2')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function setSessionId(id) {
        $('#sessionIdForExpert').val(id);
    }

    function setSessionIdForCategory(id) {
        $('#sessionIdForCategory').val(id);
    }

    function viewChatDetails(id) {
        let urlTemplate = document.getElementById('detailRoute').dataset.url;
        let url = urlTemplate.replace('IDPLACEHOLDER', id);

        $('#viewDetailBody').html('<div class="text-center py-5"><div class="spinner-border" role="status"><span class="visually-hidden"></span></div></div>');
        $('#viewDetailModal').modal('show');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#viewDetailBody').html(data);
            },
            error: function(xhr) {
                console.log(xhr);
                $('#viewDetailBody').html('<div class="alert alert-danger">Failed to load details</div>');
            }
        });
    }


    $('#assignExpertForm').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to assign this expert?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, assign!',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.expert.question.assign-expert') }}",
                    type: 'POST',
                    data: formData + '&_token={{ csrf_token() }}',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Success!', res.message, 'success');
                            $('#assignExpertModal').modal('hide');
                            location.reload(); // ya better: sirf row update kar
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    });

    // Assign Category
    $('#assignCategoryForm').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();

        Swal.fire({
            title: 'Are you sure?',
            text: "Change category for this question?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, update!',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.expert.question.assign-category') }}",
                    type: 'POST',
                    data: formData + '&_token={{ csrf_token() }}',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Success!', res.message, 'success');
                            $('#assignCategoryModal').modal('hide');
                            location.reload(); // ya row update
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    }
                });
            }
        });
    });
</script>
@endpush