@extends('layouts.back-end.app-expert')
@section('title', translate('dashboard'))
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
            <div class="row g-2">
                <!-- Card 1 -->
                <div class="col-md-3 col-sm-6">
                    <div class="card shadow-sm px-4 py-3 border-light rounded-4">
                        <p class="fs-5 mb-0">Assigned Questions</p>
                        <div class="mt-2 d-flex justify-content-between">
                            <p class="fs-5 text-dark">{{ $assignedQuestions->count() }}</p>
                            <img src="{{ asset('assets/back-end/img/expert-dahboard/dash-card-1.png') }}" alt="">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card shadow-sm px-4 py-3 border-light rounded-4">
                        <p class="fs-5 mb-0">Pending Replies</p>
                        <div class="mt-2 d-flex justify-content-between">
                            <p class="fs-5 text-dark">{{ $unreadMessages->count() }}</p>
                            <img src="{{ asset('assets/back-end/img/expert-dahboard/dash-card-2.png') }}" alt="">
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="col-md-3 col-sm-6">
                    <div class="card shadow-sm px-4 py-3 border-light rounded-4">
                        <p class="fs-5 mb-0">Average Rating</p>
                        <div class="mt-2 d-flex justify-content-between">
                            <p class="fs-5 text-dark">{{ $averageRating }}</p>
                            <img src="{{ asset('assets/back-end/img/expert-dahboard/dash-card-3.png') }}" alt="">
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="card shadow-sm px-4 py-3 border-light rounded-4">
                        <p class="fs-5 mb-0">Total Earnings</p>
                        <div class="mt-2 d-flex justify-content-between">
                            <p class="fs-5 text-dark">$ {{ number_format($totalEarning, 2) }}</p>
                            <img src="{{ asset('assets/back-end/img/expert-dahboard/dash-card-4.png') }}" alt="">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- table section -->
        <section class="shadow-lg rounded-3 my-3 p-3">
            @if($assignedChat)
            <div
                class="d-flex flex-column flex-md-row  align-items-center px-3 py-2 bg--primary rounded rounded-md-pill  gap-3">
                <img src="{{ getStorageImages(path: $assignedChat->customer?->image_full_url, type: 'avatar') }}" alt="User"
                    style="width: 40px; height: 40px; border-radius: 50%;">

                <div class="flex-grow-1">
                    <p class="mb-0 text-white" style="font-size: 13px;">
                        <span class="fw-bold">New question assigned -:</span>
                        {{ $assignedChat->messages->first()->message ?? 'No message content' }}
                    </p>
                </div>

                <a href="{{ route('expert.chat.index', [$assignedChat->id]) }}" class="btn border-white rounded bg-white text-muted">
                    Answer
                </a>
                <!-- <i class="tio-sms-chat-outlined text-white" style="font-size: 1.5rem;"></i> -->
                <i class="fa-regular fa-comments text-white" style="font-size: 1.5rem;"></i>
            </div>
            @endif
            <div class="row mt-3 bg-white">
                <div class="col-lg-12 col-xl-12">
                    <div class="table-responsive">
                        <table style="text-align: {{Session::get('direction') === " rtl" ? 'right' : 'left' }};"
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                            <thead class="thead-light text-capitalize">
                                <tr>
                                    <th>SL</th>
                                    <th>User Name</th>
                                    <th>Question Preview</th>
                                    <th>Category</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($oldChats as $key => $chat)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $chat->customer?->f_name ?? 'Not Found' }}</td>
                                    <td class="text-truncate" style="max-width: 200px;">{{ $chat->messages->first()->message ?? '' }}</td>
                                    <td>{{ $chat->category->name ?? 'General' }}</td>
                                    <td>
                                        <a href="{{ route('expert.chat.index', [$chat->id]) }}" class="btn btn-outline--primary py-1">
                                            <i class="tio-sms-chat-outlined"></i> Chat
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-lg-end">
                            {!! $oldChats->links() !!}
                        </div>
                    </div>
                    @if(count($oldChats)==0)
                    @include('layouts.back-end._empty-state',['text'=>'No_chat_found'],['image'=>'default'])
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
@endpush