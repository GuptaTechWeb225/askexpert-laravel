@extends('layouts.back-end.app')

@section('title', translate('Expert_Applications'))

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
            {{translate('Expert Applications')}}
        </h2>
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
                        <label class="form-label">{{translate('Sort_By') }}</label>
                        <select class="form-control js-select2-custom" name="sort_by">
                            <option disabled {{ is_null(request('sort_by')) ? 'selected' : '' }}>{{ translate('Select_Customer_sorting_order') }}</option>
                            <option value="asc" {{ request('sort_by') === 'asc' ? 'selected' : '' }}>{{translate('Sort_By_Oldest')}}</option>
                            <option value="desc" {{ request('sort_by') === 'desc' ? 'selected' : '' }}>{{translate('Sort_By_Newest')}}</option>
                        </select>
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
                    <div class="col-md-12">
                        <label class="d-md-block">&nbsp;</label>
                        <div class="btn--container justify-content-end">
                            <a href="{{ route('admin.expert.request') }}"
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
                {{translate('Expert_Applications')}}
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
                        <th>{{translate('Email')}}</th>
                        <th>{{translate('Category')}}</th>
                        <th>{{translate('Applied_On')}}</th>
                        <th>{{translate('Degree')}}</th>
                        <th>{{translate('Resume')}}</th>
                        <th>{{translate('Certificate')}}</th> <!-- Tier Column -->
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($experts as $key => $business)
                    <tr>
                        <td>{{ $experts->firstItem() + $key }}</td>
                        <td>{{ $business->f_name . ' ' . $business->l_name ?? 'N/A' }}</td>
                        <td>{{ $business->email ?? 'N/A' }}</td>
                        <td>{{ $business->category->name ?? 'N/A' }}</td>
                        <td> {{ $business->created_at->format('d M Y, h:i A') ?? 'N/A' }}</td>
                        <td>
                            @if($business->education_degree)
                            <button type="button" class="btn btn-secondary  border-1 border--primary px-3 py-0" data-toggle="modal"
                                data-target="#registrationModal{{$business->id}}">View</button>
                            @endif
                        </td>
                        <td>
                            @if($business->resume)
                            <button type="button" class="btn btn-secondary  border-1 border--primary px-3 py-0" data-toggle="modal"
                                data-target="#taxModal{{$business->id}}">View</button>
                            @endif
                        </td>
                        <td>
                            @if($business->certification)
                            <button type="button" class="btn btn-secondary  border-1 border--primary px-3 py-0" data-toggle="modal"
                                data-target="#vatModal{{$business->id}}">View</button>
                            @endif
                        </td>
                        <td class="d-flex gap-3">
                            <form action="{{ route('admin.expert.approve') }}" method="POST" class="approval-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $business->id }}">
                                <button type="button" class="btn btn-secondary border border-1 border-success px-2 py-0 text-success rounded-3 swal-approve-btn" title="Approve">
                                    <i class="fa-solid fa-check" style="color: green;"></i>
                                </button>
                                <input type="hidden" name="action" value="approve">
                            </form>

                            {{-- Reject Button --}}
                            <form action="{{ route('admin.expert.reject') }}" method="POST" class="approval-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $business->id }}">
                                <button type="button" class="btn btn-secondary border border-1 border-primary px-2 py-0 text-primary rounded-3 swal-reject-btn" title="Reject">
                                    <i class="fa-solid fa-xmark" style="color: red;"></i>
                                </button>
                                <input type="hidden" name="action" value="reject">
                            </form>
                        </td>

                    </tr>


                    <!-- Modal for Education Degree -->
                    <div class="modal fade" id="registrationModal{{$business->id}}" tabindex="-1"
                        role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Education Degree</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body text-center">
                                    @php $ext = strtolower(getFileType($business->education_degree)); @endphp

                                    @if(in_array($ext, ['jpg','jpeg','png','gif']))
                                    <img src="{{ asset('storage/' . $business->education_degree) }}" class="img-fluid" alt="Education Degree">
                                    @elseif(in_array($ext, ['pdf']))
                                    <iframe src="{{ asset('storage/' . $business->education_degree) }}" width="100%" height="500px"></iframe>
                                    @elseif(in_array($ext, ['doc','docx']))
                                    <a href="{{ asset('storage/' . $business->education_degree) }}" target="_blank" class="btn btn-primary">
                                        Download Document
                                    </a>
                                    @else
                                    <span class="text-danger">File format not supported</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Resume -->
                    <div class="modal fade" id="taxModal{{$business->id}}" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Resume</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body text-center">
                                    @php $ext = strtolower(getFileType($business->resume)); @endphp

                                    @if(in_array($ext, ['jpg','jpeg','png','gif']))
                                    <img src="{{ asset('storage/' . $business->resume) }}" class="img-fluid" alt="Resume">
                                    @elseif(in_array($ext, ['pdf']))
                                    <iframe src="{{ asset('storage/' . $business->resume) }}" width="100%" height="500px"></iframe>
                                    @elseif(in_array($ext, ['doc','docx']))
                                    <a href="{{ asset('storage/' . $business->resume) }}" target="_blank" class="btn btn-primary">
                                        Download Document
                                    </a>
                                    @else
                                    <span class="text-danger">File format not supported</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Certification -->
                    <div class="modal fade" id="vatModal{{$business->id}}" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Certification</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body text-center">
                                    @php $ext = strtolower(getFileType($business->certification)); @endphp

                                    @if(in_array($ext, ['jpg','jpeg','png','gif']))
                                    <img src="{{ asset('storage/' . $business->certification) }}" class="img-fluid" alt="Certification">
                                    @elseif(in_array($ext, ['pdf']))
                                    <iframe src="{{ asset('storage/' . $business->certification) }}" width="100%" height="500px"></iframe>
                                    @elseif(in_array($ext, ['doc','docx']))
                                    <a href="{{ asset('storage/' . $business->certification) }}" target="_blank" class="btn btn-primary">
                                        Download Document
                                    </a>
                                    @else
                                    <span class="text-danger">File format not supported</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
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