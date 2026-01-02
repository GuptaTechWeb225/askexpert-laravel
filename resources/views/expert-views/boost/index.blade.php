@php
use Illuminate\Support\Str;
@endphp
@extends('layouts.back-end.app-expert')
@section('title', translate('boost'))
@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">

@endpush
@section('content')

<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/push_notification.png')}}" alt="">
            {{translate('Boost')}}
        </h2>
    </div>
    <div class="card mb-2">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="tio-hot mr-1"></i>
                Boost Your Restaurant
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-8 col-xl-9">
                    <p class="m-0">*By turning on you are show on top of the other restaurants</p>
                </div>

                <div class="col-md-4 col-xl-3">
                    <div class="">
                        <h5 class="mb-3">Your Remaining Boost Days:
                            <span class="text-success">{{ $totalRemainingBoostDays }}</span>
                        </h5>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border rounded mb-2 px-3 py-2">
                        <h5 class="mb-0 font-weight-bold">Boost Mode</h5>
                        <label class="switcher ml-auto mb-0">
                            <input type="checkbox"
                                id="boostModeSwitch"
                                class="switcher_input status-toggle"
                                data-route="{{ route('restaurant.notification.boost.toggle') }}"
                                {{ $boostStatus ? 'checked disabled' : '' }}>

                            <span class="switcher_control"></span>

                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
            <div class="card">
                <div class="px-3 py-4">
                    <div class="row align-items-center">
                        <div class="col-sm-4 col-md-6 col-lg-8 mb-2 mb-sm-0">
                            <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2">
                                {{ translate('boost_Table')}}
                            </h5>
                        </div>
                        <div class="col-sm-8 col-md-6 col-lg-4">
                            <form action="{{ url()->current() }}" method="GET">
                                <div class="input-group input-group-merge input-group-custom">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="tio-search"></i>
                                        </div>
                                    </div>
                                    <input id="datatableSearch_" type="search" name="searchValue"
                                        class="form-control"
                                        placeholder="{{translate('search_by_title')}}"
                                        aria-label="Search orders" required>
                                    <button type="submit"
                                        class="btn btn--primary">{{translate('search')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>SL</th>
                                <th>Boost Days</th>
                                <th>Remaining Days</th>
                                <th>Radius (km)</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($boostHistory as $key=>$boost)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{ $boost->boost_days }}</td>
                                <td>{{ $boost->remaining_days }}</td>
                                <td>{{ $boost->radius_km }}</td>
                                <td>{{ $boost->start_date }}</td>
                                <td>{{ $boost->end_date }}</td>
                                <td>
                                    @if($boost->status == 1)
                                    <span class="badge bg-success">Active</span>
                                    @else
                                    <span class="badge bg-secondary">Expired</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
                @if(count($boostHistory) <= 0)
                    @include('layouts.back-end._empty-state',['text'=>'no_data_found'],['image'=>'default'])
                    @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.getElementById("boostModeSwitch").addEventListener("change", function(e) {
        let route = this.dataset.route;
        let isChecked = this.checked;
        let switchElem = this;

        Swal.fire({
            title: isChecked ? "Activate Boost?" : "Deactivate Boost?",
            text: isChecked ?
                "1 boost day will be consumed. Do you want to continue?" : "Boost will be turned off.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, confirm",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                // Fire AJAX
                fetch(route, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            boost: isChecked ? 1 : 0
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            if (isChecked) {
                                Swal.fire({
                                    title: "Boost Activated 🚀",
                                    html: data.message + "<br><small class='text-muted'>This will automatically disable after 1 day.</small>",
                                    icon: "success"
                                });
                                location.reload();
                            } else {
                                Swal.fire("Done!", data.message, "success");
                                location.reload();

                            }
                        } else {
                            Swal.fire("Error", data.message, "error");
                            switchElem.checked = !isChecked; // agar error to state wapas
                            location.reload();

                        }
                    })
                    .catch(err => {
                        Swal.fire("Error", "Something went wrong", "error");
                        switchElem.checked = !isChecked;
                    });
            } else {
                // cancel kiya to state rollback
                switchElem.checked = !isChecked;
            }
        });
    });
</script>

@endpush