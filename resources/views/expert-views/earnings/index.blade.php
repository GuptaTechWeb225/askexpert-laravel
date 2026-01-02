@extends('layouts.back-end.app-expert')
@section('title', translate('Earning'))
@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')


<div class="content container-fluid">
    <div class="main-content">
        <!-- banner card -->
        <div class="mt-4">
            <div class="banner-container d-flex justify-content-between position-relative" style="background-image: url('{{ asset('assets/back-end/img/home-banner-bg-1.jpg') }}');">
                <div class="banner-content">
                    <div class="banner-text">
                        <h3>Welcome Back, {{ auth('expert')->user()->f_name }} {{ auth('expert')->user()->l_name }}</h3>
                        <p>Have a good day.</p>
                    </div>
                </div>

                <div class="position-relative" id="status-container">
                    @if(auth('expert')->user()->is_online)
                    <button class="btn btn-success px-3 toggle-status-btn d-flex align-items-center gap-2" data-status="1">
                        <span class="spinner-border" role="status"></span> Go Offline
                    </button>
                    @else
                    <button class="btn btn--primary px-3 toggle-status-btn " data-status="0">
                        <i class="fa-solid fa- tower-broadcast"></i> Go Live
                    </button>
                    @endif
                    <span class="live-indicator" style="{{ auth('expert')->user()->is_online ? '' : 'display:none;' }}"></span>
                </div>
            </div>
        </div>

        <div class="py-2 mt-3">
            <div class="row g-4">
                <!-- Total Earnings -->
                <div class="col-md-3 col-sm-6">
                    <div class="card shadow-sm px-4 py-3 border-light rounded-4">
                        <p class="fs-5 mb-0">Total Earnings</p>
                        <div class="mt-2 d-flex justify-content-between">
                            <p class="fs-5 text-dark">${{ number_format($totalEarned, 2) }}</p>
                            <img src="{{ asset('assets/images/earning.png') }}" alt="">
                        </div>
                    </div>
                </div>

                <!-- This Month -->
                <div class="col-md-3 col-sm-6">
                    <div class="card shadow-sm px-4 py-3 border-light rounded-4">
                        <p class="fs-5 mb-0">This Month</p>
                        <div class="mt-2 d-flex justify-content-between">
                            <p class="fs-5 text-dark">${{ number_format($thisMonthEarned, 2) }}</p>
                            <img src="{{ asset('assets/images/earning.png') }}" alt="">
                        </div>
                    </div>
                </div>

                <!-- Pending Payout -->
                <div class="col-md-3 col-sm-6">
                    <div class="card shadow-sm px-4 py-3 border-light rounded-4">
                        <p class="fs-5 mb-0">Pending Payout</p>
                        <div class="mt-2 d-flex justify-content-between">
                            <p class="fs-5 text-dark">${{ number_format($pendingPayout, 2) }}</p>
                            <img src="{{ asset('assets/images/earning.png') }}" alt="">
                        </div>
                    </div>
                </div>

                <!-- Withdrawn -->
                <div class="col-md-3 col-sm-6">
                    <div class="card shadow-sm px-4 py-3 border-light rounded-4">
                        <p class="fs-5 mb-0">Withdrawn</p>
                        <div class="mt-2 d-flex justify-content-between">
                            <p class="fs-5 text-dark">${{ number_format($withdrawn, 2) }}</p>
                            <img src="{{ asset('assets/images/earning.png') }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- table section -->
        <section class="card rounded-3 my-3 p-3">

            <div class="row mt-3 bg-white">
                <div class="col-lg-12 col-xl-12">
                    <div class="bg-white shadow-sm rounded p-3 mb-3">
                        <div class="row align-items-end">
                            <!-- Category & Status Filters (Auto Submit) -->
                            <div class="col-md-6 mb-3">
                                <form method="GET" action="{{ route('expert.earnings') }}" id="filterForm">
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label">Category</label>
                                            <select name="category" class="form-control select2-enable" onchange="this.form.submit()">
                                                <option value="">All Categories</option>
                                                @foreach($categories as $id => $name)
                                                <option value="{{ $id }}" {{ request('category') == $id ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-6">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-control select2-enable" onchange="this.form.submit()">
                                                <option value="">All Status</option>
                                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Hidden input to preserve search if needed -->
                                    @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                    @endif
                                </form>
                            </div>

                            <!-- Search Bar (Separate Form with Button) -->
                            <div class="col-md-6 mb-3">
                                <form method="GET" action="{{ route('expert.earnings') }}">
                                    <div class="input-group common-searchbar">
                                        <input type="text" name="search" class="form-control"
                                            placeholder="Search by question..." value="{{ request('search') }}">

                                        <button class="btn btn--primary" type="submit">
                                            <i class="fa-solid fa-magnifying-glass"></i> Search
                                        </button>

                                        <!-- Preserve category & status in search form -->
                                        @if(request('category'))
                                        <input type="hidden" name="category" value="{{ request('category') }}">
                                        @endif
                                        @if(request('status'))
                                        <input type="hidden" name="status" value="{{ request('status') }}">
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table style="text-align: {{Session::get('direction') === " rtl" ? 'right' : 'left' }};"
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                            <thead class="thead-light text-capitalize">
                                <tr>
                                    <th>SL</th>
                                    <th>Date</th>
                                    <th>Question Title</th>
                                    <th>Question ID</th>
                                    <th>Category</th>
                                    <th>Mode</th>
                                    <th>Amount Earned</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($earnings as $index => $earning)


                                <tr>
                                    <td>{{ $earnings->firstItem() + $index }}</td>
                                    <td>{{ $earning->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($earning->chat && $earning->chat)
                                        {{ Str::limit($earning->chat->messages->first()->message, 30) }}
                                        @else
                                        Not Found
                                        @endif
                                    </td>
                                    <td>#Q{{ $earning->chat_session_id }}</td>
                                    <td>{{ $earning->category?->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($earning->chat)
                                        {{ ucfirst(str_replace('_', ' ', $earning->chat->communication_mode ?? 'chat')) }}
                                        @else
                                        Text
                                        @endif
                                    </td>
                                    <td class="text-center">${{ number_format($earning->total_amount, 2) }}</td>
                                    <td class="text-center">
                                        <span class="px-2 py-1 badge bg-{{ $earning->status == 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($earning->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-lg-end">
                            {!! $earnings->links() !!}
                        </div>
                    </div>
                    @if(count($earnings)==0)
                    @include('layouts.back-end._empty-state',['text'=>'No_earnings_found'],['image'=>'default'])
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>

@endsection

@push('script_2')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/apexcharts.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/dashboard.js')}}"></script>
<script>
    $(document).on('click', '.toggle-status-btn', function() {
        let currentStatus = $(this).data('status'); // 1 means Online, 0 means Offline
        let titleText = currentStatus == 1 ? "Want to go Offline?" : "Want to go Live?";
        let confirmText = currentStatus == 1 ? "Yes, Go Offline" : "Yes, Go Live";
        let btnColor = currentStatus == 1 ? "#d33" : "#377dff";

        Swal.fire({
            title: titleText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: btnColor,
            cancelButtonColor: '#secondary',
            confirmButtonText: confirmText
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('expert.dashboard.update-status') }}",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);

                            let btnHtml = '';
                            if (response.new_status == 1) {
                                btnHtml = `<button class="btn btn-success px-3 toggle-status-btn d-flex align-items-center gap-2" data-status="1">
                     <span class="spinner-border" role="status"></span> Go Offline
                    </button>`;
                                $('.live-indicator').show();
                            } else {
                                btnHtml = `<button class="btn btn--primary px-3 toggle-status-btn" data-status="0">
                                              <i class="fa-solid fa-tower-broadcast"></i> Go Live
                                           </button>`;
                                $('.live-indicator').hide();
                            }

                            // Update the container
                            $('.toggle-status-btn').parent().html(btnHtml + `<span class="live-indicator" style="${response.new_status == 1 ? '' : 'display:none;'}"></span>`);
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Action failed. Try again.', 'error');
                    }
                });
            }
        })
    });
</script>
<script>
    $(document).ready(function () {
        $('.select2-enable').select2({
            placeholder: "Select...",
            allowClear: true
        });

        $('select[name="category"], select[name="status"]').on('change', function () {
            $('#filterForm').submit();
        });
    });
</script>

@endpush
