@extends('layouts.back-end.app')
@section('title', translate('Refund Requests'))
@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="content container-fluid">
    <h4 class="fs-5 fw-bold mt-3 top-heading">
        <i class="fa-solid fa-file-invoice-dollar me-2"></i>Refund Requests
    </h4>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <select name="status" class="form-control select2-enable">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-4 ml-auto text-end">
                <div class="input-group input-group-merge input-group-custom">
                            <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="{{ request('search') }}">
                            <button class="btn btn--primary" type="submit">
                                <i class="fa-solid fa-magnifying-glass"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

         <div class="table-responsive datatable-custom">
            <table
                style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>SL</th>
                        <th>Name</th>
                        <th>Post</th>
                        <th>Request ID</th>
                        <th>Question Title</th>
                        <th>Refund Reason</th>
                        <th>Amount</th>
                        <th>Request Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($refunds as $index => $refund)
                    <tr>
                        <td>{{ $refunds->firstItem() + $index }}</td>
                        <td>{{ $refund->user->f_name }} {{ $refund->user->l_name }}</td>
                        <td>User</td>
                        <td>#R{{ $refund->id }}</td>
                        <td>{{ Str::limit($refund->chatSession->firstMessage?->message ?? 'N/A', 40) }}</td>
                        <td>{{ Str::limit($refund->reason, 30) }}</td>
                        <td>${{ number_format($refund->requested_amount, 2) }}</td>
                        <td>{{ $refund->created_at->format('M d, Y') }}</td>
                        <td>
                            <span class="px-2 py-1 badge bg-{{ 
                                        $refund->status == 'approved' ? 'success' : 
                                        ($refund->status == 'rejected' ? 'danger' : 'warning') 
                                    }}">
                                {{ ucfirst($refund->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <a class="btn btn-outline-dark btn-sm" href="{{ route('admin.refunds.view', $refund->id) }}">
                                    <i class="tio-visible me-2"></i>
                                </a>
                                @if($refund->status === 'pending')
                                <a class="btn btn-outline-dark btn-sm text-success approve-refund" href="#"
                                    data-id="{{ $refund->id }}">
                                    <i class="fa-solid fa-circle-check me-2"></i>
                                </a>
                                <a class="btn btn-outline-dark btn-sm text-danger reject-refund" href="#"
                                    data-id="{{ $refund->id }}">
                                    <i class="fa-solid fa-circle-xmark me-2"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">No refund requests found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end mt-4">
            {{ $refunds->links() }}
        </div>
    </div>
</div>

<!-- Approve Modal -->
<!-- Approve Refund Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel">
    <div class="modal-dialog modal-dialog-scrollable modal-md">
        <div class="border border-success modal-content overflow-auto rounded-4">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="approveModalLabel">Choose Refund Type</h5>
    <button type="button" class="btn-close btn btn-status" data-bs-dismiss="modal"> <i class="tio-clear"></i> </button>
            </div>

            <form id="approveForm">
                @csrf
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="refund_request_id" id="approveRefundId">

                <div class="modal-body">
                    <p class="text-muted mb-4">Select what you want to refund for this user:</p>

                    <ul class="list-group list-group-flush g-3">
                        <!-- Joining Fee -->
                        <li class="list-group-item d-flex justify-content-between align-items-center border rounded py-3 mb-3">
                            <div>
                                <h6 class="mb-1">Joining Fee</h6>
                                <small class="text-muted" id="joiningFeeText">Loading...</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input refund-checkbox" type="checkbox" name="refund_types[]" value="joining_fee" id="chkJoining">
                                <label class="form-check-label" for="chkJoining"></label>
                            </div>
                        </li>

                        <!-- Expert Fee (Current Question) -->
                        <li class="list-group-item d-flex justify-content-between align-items-center border rounded py-3 mb-3">
                            <div>
                                <h6 class="mb-1">Expert Consultation Fee</h6>
                                <small class="text-muted">For this question</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input refund-checkbox" type="checkbox" name="refund_types[]" value="expert_fee" id="chkExpert" checked>
                                <label class="form-check-label" for="chkExpert"></label>
                            </div>
                        </li>

                        <!-- Cancel Monthly Subscription -->
                        <li class="list-group-item d-flex justify-content-between align-items-center border rounded py-3">
                            <div>
                                <h6 class="mb-1">Cancel Monthly Subscription</h6>
                                <small class="text-muted">Stop future recurring payments</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="cancel_subscription" value="1" id="chkCancelSub">
                                <label class="form-check-label" for="chkCancelSub"></label>
                            </div>
                        </li>
                    </ul>

                    <div class="mt-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between">
                            <strong>Total Refund Amount:</strong>
                            <strong>$<span id="totalRefundAmount">0.00</span></strong>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label>Admin Note (Optional)</label>
                        <textarea name="admin_note" class="form-control" rows="3" placeholder="e.g. Refund approved as per user request"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary px-5">Apply Refund</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm">
                @csrf
                <input type="hidden" name="action" value="reject">
                <div class="modal-header">
                    <h5>Reject Refund</h5>
    <button type="button" class="btn-close btn btn-status" data-bs-dismiss="modal"> <i class="tio-clear"></i> </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject this refund request?</p>
                    <div class="mb-3">
                        <label>Admin Note (Required)</label>
                        <textarea name="admin_note" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Yes, Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection
@push('script')
<script>
$(document).ready(function() {
    let currentRefundId = null;
    let joiningFeeAmount = 0;
    let expertFeeAmount = 0;

    // Approve Modal Open
    $('.approve-refund').click(function(e) {
        e.preventDefault();
        currentRefundId = $(this).data('id');
        $('#approveRefundId').val(currentRefundId);

        $.get('/admin/refunds/' + currentRefundId + '/data', function(data) {
            joiningFeeAmount = parseFloat(data.joining_fee) || 0;
            expertFeeAmount = parseFloat(data.expert_fee) || 0;

            $('#joiningFeeText').text(joiningFeeAmount > 0 ? '$' + joiningFeeAmount.toFixed(2) : 'No joining fee paid');
            $('#chkJoining').prop('disabled', joiningFeeAmount === 0);
            $('#chkJoining').prop('checked', joiningFeeAmount > 0);
            $('#chkExpert').prop('checked', true);

            calculateTotal();
        });

        $('#approveForm')[0].reset();
        $('#totalRefundAmount').text('0.00');
        $('#approveModal').modal('show');
    });

    // Reject Modal Open
    $('.reject-refund').click(function(e) {
        e.preventDefault();
        currentRefundId = $(this).data('id');
        $('#rejectRefundId').val(currentRefundId);
        $('#rejectForm')[0].reset();
        $('#rejectModal').modal('show');
    });

    $('.refund-checkbox').on('change', function() {
        calculateTotal();
    });

    function calculateTotal() {
        let total = 0;
        if ($('#chkJoining').is(':checked')) total += joiningFeeAmount;
        if ($('#chkExpert').is(':checked')) total += expertFeeAmount;
        $('#totalRefundAmount').text(total.toFixed(2));
    }

    // Approve Form Submit
    $('#approveForm').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&total_amount=' + $('#totalRefundAmount').text();

        $.ajax({
            url: '/admin/refunds/' + currentRefundId + '/process',
            method: 'POST',
            data: formData,
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#approveModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(res.message || 'Something went wrong');
                }
            },
            error: function() {
                toastr.error('Request failed');
            }
        });
    });

    // Reject Form Submit
    $('#rejectForm').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: '/admin/refunds/' + currentRefundId + '/reject',
            method: 'POST',
            data: formData,
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#rejectModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(res.message || 'Something went wrong');
                }
            },
            error: function() {
                toastr.error('Request failed');
            }
        });
    });
});
</script>
@endpush