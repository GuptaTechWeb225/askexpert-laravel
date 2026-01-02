@extends('layouts.back-end.app')
@section('title', translate('View Refund Request'))
@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="content container-fluid">
    <h4 class="fs-5 fw-bold mt-3 top-heading">
        <i class="fa-solid fa-file-invoice-dollar me-2"></i>View Refund Request
    </h4>

    <div class="my-3 p-4 card rounded-2 border border-2 border-light">
        <div class="bg-soft-secondary border-bottom border-color-c1 my-4 px-3 py-3 rounded">
            <p class="mb-0 fs-5 fw-bold">Request Summary</p>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label>Request ID</label>
                <input type="text" class="form-control" value="#R-{{ $refund->id }}" disabled>
            </div>
            <div class="col-md-4">
                <label>Request Date & Time</label>
                <input type="text" class="form-control" value="{{ $refund->created_at->format('M d, Y, h:i A') }}" disabled>
            </div>
            <div class="col-md-4">
                <label>Status</label>
                <input type="text" class="form-control" value="{{ ucfirst($refund->status) }}" disabled>
            </div>
        </div>

        <div class="bg-soft-secondary border-bottom border-color-c1 my-4 px-3 py-3 rounded">
            <p class="mb-0 fs-5 fw-bold">Customer Details</p>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label>Customer Name</label>
                <input type="text" class="form-control" value="{{ $refund->user->f_name }} {{ $refund->user->l_name }}" disabled>
            </div>
            <div class="col-md-4">
                <label>Email</label>
                <input type="text" class="form-control" value="{{ $refund->user->email }}" disabled>
            </div>
        </div>

        <div class="bg-soft-secondary border-bottom border-color-c1 my-4 px-3 py-3 rounded">
            <p class="mb-0 fs-5 fw-bold">Question / Service Details</p>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-6">
                <label>Question Title</label>
                <input type="text" class="form-control" value="{{ $refund->chatSession->firstMessage?->message ?? 'N/A' }}" disabled>
            </div>
            <div class="col-md-6">
                <label>Assigned Expert</label>
                <input type="text" class="form-control" value="{{ $refund->chatSession->expert?->f_name ?? 'N/A' }}" disabled>
            </div>
        </div>

        <div class="bg-soft-secondary border-bottom border-color-c1 my-4 px-3 py-3 rounded">
            <p class="mb-0 fs-5 fw-bold">Payment & Refund Details</p>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <label>Amount Requested</label>
                <input type="text" class="form-control" value="${{ number_format($refund->requested_amount, 2) }}" disabled>
            </div>
            <div class="col-md-4">
                <label>Payment Mode</label>
                <input type="text" class="form-control" value="{{ $refund->chatPayment?->payment_mode ?? 'Stripe' }}" disabled>
            </div>
            <div class="col-md-4">
                <label>Transaction ID</label>
                <input type="text" class="form-control" value="{{ $refund->chatPayment?->stripe_payment_intent_id ?? 'N/A' }}" disabled>
            </div>
            <div class="col-md-4">
                <label>Payment Date</label>
                <input type="text" class="form-control" value="{{ $refund->chatPayment?->paid_at->format('d M y h:i A') }}" disabled>

            </div>
            <div class="col-md-12">
                <label>Customer Reason</label>
                <textarea class="form-control" rows="3" disabled>{{ $refund->reason }}</textarea>

            </div>

            @if($refund->admin_note)
            <div class="col-md-12">
                <label>Admin Note</label>
                <textarea class="form-control" rows="3" disabled>{{ $refund->admin_note }}</textarea>
            </div>
            @endif
        </div>
    </div>
</div>


@endsection


@push('script')

@endpush