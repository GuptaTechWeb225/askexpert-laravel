@extends('layouts.back-end.app')

@section('title', translate('expert_Details'))

@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset(path:'public/assets/back-end/css/owl.min.css')}}">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="d-print-none pb-2">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="mb-3">
                    <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                        <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                        {{translate('Expert Profile')}}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2 mb-2">
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-sm border border-2 border-light rounded-1 h-100">
                <div class="view-profile-left p-4 d-flex flex-column align-items-center h-100">
                    <img src="{{ getStorageImages(path: $expert->image_full_url, type: 'backend-profile') }}"
                        alt="Profile Photo" class="mb-3 rounded-pill" style="width: 120px; height: 120px; object-fit: cover;">

                    @if($expert->is_active)
                    <button class="btn btn-outline-success my-3">Active</button>
                    @else
                    <button class="btn btn-outline-danger my-3">Inactive</button>
                    @endif

                    <h1 class="mb-1 text-dark fs-24 text-center">
                        {{ $expert->f_name . ' ' . $expert->l_name }}
                    </h1>

                    <hr class="border border-1 border-muted w-100 mt-3">

                    <div class="d-flex">
                        {{-- Call --}}
                        @if($expert->phone)
                        <a href="tel:{{ $expert->phone }}" class="btn btn-outline--primary mr-3 mt-2" title="Call expert">
                            <i class="fa-solid fa-phone-volume"></i>
                        </a>
                        @endif

                        {{-- Message --}}
                        @if($expert->phone)
                        <a href="sms:{{ $expert->phone }}" class="btn btn-outline--primary mr-3 mt-2" title="Send Message">
                            <i class="fa-regular fa-message"></i>
                        </a>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        <!-- Right Profile -->
        <div class="col-lg-8 col-md-7">
            <div class="card shadow-sm border border-2 border-light rounded h-100">
                <div class="view-profile-right p-4 h-100">
                    <h3 class="mb-3 bg--primary p-3 rounded text-white fs-5">Personal Information</h3>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control"
                                value="{{ $expert->f_name . ' ' . $expert->l_name }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" class="form-control"
                                value="{{ $expert->phone ?? 'N/A' }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="text" class="form-control"
                                value="{{ $expert->email ?? 'N/A' }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control"
                                value="{{ $expert->category->name }}" readonly>
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">Years of Experience</label>
                            <input type="text" class="form-control"
                                value="{{ $expert->experience }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Education Degree</label>
                           <button type="button" class="d-block w-100 btn  border border-primary border-2 rounded-3 p-2 text-center" data-toggle="modal"
                                data-target="#registrationModal{{$expert->id}}">View Degree</button>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Resume / CV</label>
                            <button type="button" class="d-block w-100 btn  border border-primary border-2 rounded-3 p-2 text-center" data-toggle="modal"
                                data-target="#taxModal{{$expert->id}}">View Resume / CV</button>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Certification</label>
                             <button type="button" class="d-block w-100 btn  border border-primary border-2 rounded-3 p-2 text-center" data-toggle="modal"
                                data-target="#vatModal{{$expert->id}}">View Certification</button>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control"
                                value="{{ $expert->is_active ? 'Active' : 'Inactive' }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="">Ratings</label>
                            <div class="border border-1 border-primary rounded px-3 py-2 fs-5 fw-bold d-flex align-items-center gap-2">
                                <img src="{{ asset('assets/back-end/img/rating.png') }}" alt="Rating" class="mb-0" style="width: 24px; height: 24px;">
                                <span>Average Ratings <span>4.7 / 5</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

      <div class="modal fade" id="registrationModal{{$expert->id}}" tabindex="-1"
                        role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Education Degree</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body text-center">
                                    @php $ext = strtolower(getFileType($expert->education_degree)); @endphp

                                    @if(in_array($ext, ['jpg','jpeg','png','gif']))
                                    <img src="{{ asset('storage/' . $expert->education_degree) }}" class="img-fluid" alt="Education Degree">
                                    @elseif(in_array($ext, ['pdf']))
                                    <iframe src="{{ asset('storage/' . $expert->education_degree) }}" width="100%" height="500px"></iframe>
                                    @elseif(in_array($ext, ['doc','docx']))
                                    <a href="{{ asset('storage/' . $expert->education_degree) }}" target="_blank" class="btn btn-primary">
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
                    <div class="modal fade" id="taxModal{{$expert->id}}" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Resume</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body text-center">
                                    @php $ext = strtolower(getFileType($expert->resume)); @endphp

                                    @if(in_array($ext, ['jpg','jpeg','png','gif']))
                                    <img src="{{ asset('storage/' . $expert->resume) }}" class="img-fluid" alt="Resume">
                                    @elseif(in_array($ext, ['pdf']))
                                    <iframe src="{{ asset('storage/' . $expert->resume) }}" width="100%" height="500px"></iframe>
                                    @elseif(in_array($ext, ['doc','docx']))
                                    <a href="{{ asset('storage/' . $expert->resume) }}" target="_blank" class="btn btn-primary">
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
                    <div class="modal fade" id="vatModal{{$expert->id}}" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Certification</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                        aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body text-center">
                                    @php $ext = strtolower(getFileType($expert->certification)); @endphp

                                    @if(in_array($ext, ['jpg','jpeg','png','gif']))
                                    <img src="{{ asset('storage/' . $expert->certification) }}" class="img-fluid" alt="Certification">
                                    @elseif(in_array($ext, ['pdf']))
                                    <iframe src="{{ asset('storage/' . $expert->certification) }}" width="100%" height="500px"></iframe>
                                    @elseif(in_array($ext, ['doc','docx']))
                                    <a href="{{ asset('storage/' . $expert->certification) }}" target="_blank" class="btn btn-primary">
                                        Download Document
                                    </a>
                                    @else
                                    <span class="text-danger">File format not supported</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

    <div class="row g-2">

        <div class="col-lg-12">
            <div class="card">
                <div class="p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h5 class="card-title m-0">{{translate('Individual Rating')}} <span class="badge badge-secondary"></span> </h5>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="row">
                            <div class="col-auto">
                               
                            </div>
                        </div>

                    </div>
                </div>
                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Review ID')}}</th>
                                <th>{{translate('Customer')}}</th>
                                <th>{{translate('Rating')}}</th>
                                <th>{{translate('Review Text')}}</th>
                                <th>{{translate('Date')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                @include('layouts.back-end._empty-state',['text'=>'no_Question_history_found'],['image'=>'default'])
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{dynamicAsset(path:'public/assets/back-end/js/owl.min.js')}}"></script>
<script type="text/javascript">
    'use strict';
    $('.order-statistics-slider, .address-slider').owlCarousel({
        margin: 16,
        loop: false,
        autoWidth: true,
    })
</script>
@endpush