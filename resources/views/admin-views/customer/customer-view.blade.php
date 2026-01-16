@extends('layouts.back-end.app')

@section('title', translate('user_Details'))

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
                        {{translate('Customer Profile')}}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2 mb-2">
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-sm border border-2 border-light rounded-1 h-100">
                <div class="view-profile-left p-4 d-flex flex-column align-items-center h-100">
                    <img src="{{ getStorageImages(path: $customer->image_full_url, type: 'backend-profile') }}"
                        alt="Profile Photo" class="mb-3 rounded-pill" style="width: 120px; height: 120px; object-fit: cover;">

                    @if($customer->is_active)
                    <button class="btn btn-outline-success my-3">Active</button>
                    @else
                    <button class="btn btn-outline-danger my-3">Inactive</button>
                    @endif

                    <h2 class="mb-1 text-dark fs-24 text-center">
                        {{ $customer->f_name . ' ' . $customer->l_name }}
                    </h2>
                    <hr class="border border-1 border-muted w-100 mt-3">
                    <div class="d-flex">
                        @if($customer->phone)
                        <a href="tel:{{ $customer->phone }}" class="btn btn-outline--primary mr-3 mt-2" title="Call Customer">
                            <i class="fa-solid fa-phone-volume"></i>
                        </a>
                        @endif

                        @if($customer->email)
                        <a href="mailto:{{ $customer->email }}" class="btn btn-outline--primary mr-3 mt-2" title="Send Message">
                            <i class="fa-regular fa-message"></i>
                        </a>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-md-7">
            <div class="card shadow-sm border border-2 border-light rounded h-100">
                <div class="view-profile-right p-4 h-100">
                    <h3 class="mb-3 bg--primary p-3 rounded text-white fs-5">Personal Information</h3>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control"
                                value="{{ $customer->f_name . ' ' . $customer->l_name }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" class="form-control"
                                value="{{ $customer->phone ?? 'N/A' }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="text" class="form-control"
                                value="{{ $customer->email ?? 'N/A' }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Join Date</label>
                            <input type="text" class="form-control"
                                value="{{ $customer->created_at ? $customer->created_at->format('d-M-Y') : 'N/A' }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Subscription</label>
                            <input type="text"
                                class="form-control"
                                value="{{ $customer->hasAnyActiveMembership() ? 'Active' : 'Expired' }}"
                                readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control"
                                value="{{ $customer->is_active ? 'Active' : 'Inactive' }}" readonly>
                        </div>
                        @if ($customer->hasAnyActiveMembership())

                        <div class="col-md-6">
                            <label class="form-label">Subscription Fee</label>
                            <input type="text"
                                class="form-control"
                                value="${{ $customer->monthly_subscription_fee ?? 0 }}"
                                readonly>

                        </div>
                        @endif
                        @if ($customer->payments())
                        <div class="col-md-6">
                            <label class="form-label">Joining Fee</label>
                            <input type="text" class="form-control"
                                value="${{ $customer->joining_fee_amount ?? 0  }}" readonly>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2">

        <div class="col-lg-12">
            <div class="card">
                <div class="p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h5 class="card-title m-0">{{translate('Question History')}} <span class="badge badge-secondary">{{ $chatSessions->count() }}</span> </h5>
                    <!-- <div class="d-flex flex-wrap gap-2">
                        <div class="row">
                            <div class="col-auto">
                                <form action="{{ url()->current() }}" method="GET">
                                    <div class="input-group input-group-merge input-group-custom">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                <i class="tio-search"></i>
                                            </div>
                                        </div>
                                        <input id="datatableSearch_" type="search" name="searchValue"
                                            class="form-control"
                                            placeholder="{{translate('search_Question')}}" aria-label="Search transaction"
                                            value="{{ request('searchValue') }}"
                                            required>
                                        <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div> -->
                </div>
                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('date')}}</th>
                                <th>{{translate('Question Title')}}</th>
                                <th>{{translate('Category')}}</th>
                                <th>{{translate('Expert')}}</th>
                                <th>{{translate('Mode')}}</th>
                                <th>{{translate('Charge')}}</th>
                                <th>{{translate('Status')}}</th>
                                <!-- <th>{{translate('Action')}}</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($chatSessions as $key => $chat)
                            <tr>
                                <td>{{ $chatSessions->firstItem() + $key }}</td>

                                <td>{{ $chat->started_at?->format('d M Y, h:i A') ?? '-' }}</td>

                                <td>
                                    {{ Str::limit($chat->firstMessage?->message ?? '—', 40) }}
                                </td>

                                <td>{{ $chat->category?->name ?? '-' }}</td>

                                <td>{{ $chat->expert?->f_name. ' ' .$chat->expert?->l_name ?? '-' }}</td>

                                <td>
                                    <span class="badge bg-info">
                                        {{ ucfirst($chat->mode ?? 'chat') }}
                                    </span>
                                </td>
                                <td>
                                    ${{ ucfirst($chat->total_charged) }}
                                </td>

                                <td>
                                    <span class="badge 
                                  {{ $chat->status === 'completed' ? 'bg-success' : 'bg-warning' }}">
                                        {{ ucfirst($chat->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
                <div class="table-responsive mt-4">
                    <div class="d-flex justify-content-lg-end">
                        {!! $chatSessions->links() !!}
                    </div>
                </div>
                @if(count($chatSessions)==0)
                @include('layouts.back-end._empty-state',['text'=>'no_Question_history_found'],['image'=>'default'])
                @endif
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