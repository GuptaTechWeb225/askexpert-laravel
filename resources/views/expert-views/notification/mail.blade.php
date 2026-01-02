@php
use Illuminate\Support\Str;
@endphp
@extends('layouts.back-end.app-expert')
@section('title', translate('add_new_mail'))
@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">

@endpush
@section('content')

<div class="content container-fluid">
    <div class="mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/push_notification.png')}}" alt="">
            {{translate('send_Mail')}}
        </h2>
    </div>
    <div class="row gx-2 gx-lg-3">
        <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
            <div class="card">
                <div class="card-body">
                    <form id="mailForm" method="post" class="text-start"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            {{-- Left side --}}
                            <div class="col-md-6">
                                {{-- Subject --}}
                                <div class="form-group">
                                    <label class="title-color text-capitalize">{{translate('subject')}}</label>
                                    <input type="text" name="subject" class="form-control"
                                        placeholder="{{translate('Flat 200 off on order above 2000')}}" required>
                                </div>

                                {{-- Body --}}
                                <div class="form-group">
                                    <label class="title-color text-capitalize">{{translate('body')}}</label>
                                    <textarea name="body" class="form-control text-area-max-min" rows="5" required></textarea>
                                </div>

                                {{-- Sent To --}}
                                <div class="form-group">
                                    <label class="title-color text-capitalize">{{translate('send_to')}}</label>
                                    <select name="sent_to" id="sent_to" class="form-control" required>
                                        <option value="all">{{translate('All Customers')}}</option>
                                        <option value="selected">{{translate('Selected Customers')}}</option>
                                    </select>
                                </div>

                                {{-- Receiver IDs (only if selected) --}}
                                <div class="form-group d-none" id="receiver_ids_box">
                                    <label class="title-color text-capitalize">{{translate('select_customers')}}</label>
                                    <select name="receiver_ids[]" class="js-select2-custom form-control" multiple>
                                        @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Right side --}}
                            <div class="col-md-6">
                                {{-- Image --}}
                                <div class="form-group">
                                    <div class="d-flex justify-content-center">
                                        <img class="upload-img-view mb-4" id="viewer"
                                            src="{{dynamicAsset(path: 'public/assets/back-end/img/900x400/img1.jpg')}}"
                                            alt="{{translate('image')}}" />
                                    </div>
                                    <label class="title-color text-capitalize">{{translate('image')}}</label>
                                    <span class="text-info">({{translate('ratio').' 1:1'}})</span>
                                    <div class="custom-file text-left">
                                        <input type="file" name="image" class="custom-file-input image-input"
                                            data-image-id="viewer"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label">{{translate('choose_File')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="button" id="sendMailBtn" class="btn btn--primary">
                                {{translate('send_Mail')}}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
            <div class="card">
                <div class="px-3 py-4">
                    <div class="row align-items-center">
                        <div class="col-sm-4 col-md-6 col-lg-8 mb-2 mb-sm-0">
                            <h5 class="mb-0 text-capitalize d-flex align-items-center gap-2">
                                {{ translate('push_notification_table')}}
                                <span
                                    class="badge badge-soft-dark radius-50 fz-12 ml-1">{{ $notifications->total() }}</span>
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
                                        aria-label="Search orders" value="{{ $searchValue }}" required>
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
                                <th>{{translate('SL')}} </th>
                                <th>{{translate('subject')}} </th>
                                <th>{{translate('body')}} </th>
                                <th>{{translate('image')}} </th>
                                <th>{{translate('status')}} </th>
                                <th class="text-center">{{translate('action')}} </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $key=>$notification)
                            <tr>
                                <td>{{$notifications->firstItem()+ $key}}</td>
                                <td>
                                    <span class="d-block">
                                        {{Str::limit($notification['subject'],30)}}
                                    </span>
                                </td>
                                <td>
                                    {{Str::limit($notification['body'],40)}}
                                </td>
                                <td>
                                    <img class="min-w-75" width="75" height="75"
                                        src="{{ getStorageImages(path: $notification->image_full_url, type: 'backend-basic') }}" alt="">
                                </td>
                                @php
                                $statusClass = match($notification['status']) {
                                'pending' => 'bg-warning text-dark',
                                'sent' => 'bg-success text-light',
                                'failed' => 'bg-danger text-light',
                                default => 'bg-secondary text-light',
                                };
                                @endphp

                                <td>
                                    <label class="btn {{ $statusClass }} font-weight-bold px-3 py-1 mb-0 fz-12">
                                        {{ ucfirst($notification['status']) }}
                                    </label>
                                </td>


                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">

                                        <a class="btn btn-outline-danger btn-sm delete-data-without-form"
                                            title="{{translate('delete')}}"
                                            data-action="{{route('restaurant.notification.mail.delete')}}"
                                            data-id="{{$notification['id']}}')">
                                            <i class="tio-delete"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table class="mt-4">
                        <tfoot>
                            {!! $notifications->links() !!}
                        </tfoot>
                    </table>
                </div>
                @if(count($notifications) <= 0)
                    @include('layouts.back-end._empty-state',['text'=>'no_data_found'],['image'=>'default'])
                    @endif
            </div>
        </div>
    </div>
</div>
<span id="get-resend-notification-route-and-text" data-text="{{translate("resend_notification")}}" data-action="{{ route("restaurant.notification.resend-notification") }}"></span>
@endsection

@push('script')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/notification.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.getElementById('sent_to').addEventListener('change', function() {
        let box = document.getElementById('receiver_ids_box');
        if (this.value === 'selected') {
            box.classList.remove('d-none');
        } else {
            box.classList.add('d-none');
        }
    });
</script>

<script>
    document.getElementById("sendMailBtn").addEventListener("click", function() {
        let form = document.getElementById("mailForm");
        let formData = new FormData(form);

        Swal.fire({
            title: "Confirm Send?",
            text: "Do you want to send this mail?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, Send",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch("{{ route('restaurant.notification.mail.store') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            Swal.fire({
                                title: "Sent!",
                                html: `
                                    <p>${data.message}</p>
                                    ${data.mails_left !== undefined ? `<p><b>Mails Left:</b> ${data.mails_left}</p>` : ""}
                                `,
                                icon: "success"
                            });
                            form.reset();
                        } else {
                            // yaha error ke time pe pura data dikhayenge
                            let errorDetails = `<p>${data.message}</p>`;

                            if (data.mails_left !== undefined) {
                                errorDetails += `<p><b>Mails Left:</b> ${data.mails_left}</p>`;
                            }
                            if (data.needed !== undefined) {
                                errorDetails += `<p><b>Mails Needed:</b> ${data.needed}</p>`;
                            }

                            Swal.fire({
                                title: "Error!",
                                html: errorDetails,
                                icon: "error"
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    });
            }
        });
    });
</script>

@endpush