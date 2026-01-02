@extends('layouts.back-end.app')
@section('title', translate('Revenue & Payouts'))
@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset('public/assets/back-end/img/expert-category.png') }}" alt="">
            Setup Payout for {{ $expert->f_name }} {{ $expert->l_name }}
        </h2>

    </div>

    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <h5>Pending Sessions ({{ $earnings->total() }})</h5>
                <button class="btn btn-success" id="bulkPayBtn" style="display:none;">
                    Pay Selected (<span id="selectedCount">0</span>)
                </button>
            </div>

            <form id="bulkPayForm">
                @csrf
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>SL</th>
                                <th>Question</th>
                                <th>Amount</th>
                                <th>Rating</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($earnings as $earning)
                            <tr>
                                <td><input type="checkbox" name="earning_ids[]" value="{{ $earning->id }}" class="row-checkbox"></td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ Str::limit($earning->chat?->firstMessage?->message ?? 'N/A', 50) }}</td>
                                <td>${{ number_format($earning->total_amount, 2) }}</td>
                                <td>
                                    @if($earning->chat?->review)
                                    <i class="fa fa-star text-warning"></i> {{ $earning->chat->review->rating }}
                                    @else
                                    No review
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary pay-single" data-id="{{ $earning->id }}" data-amount="{{ number_format($earning->total_amount, 2, '.', '') }}">
                                        Pay Now
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No pending payouts</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            {{ $earnings->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="payForm">
                @csrf
                <!-- earning_ids[] dynamically JS se add honge -->
                <div class="modal-header">
                    <h5>Confirm Payment</h5>
                    <button type="button" class="btn-close btn btn-status" data-bs-dismiss="modal"> <i class="tio-clear"></i> </button>
                </div>
                <div class="modal-body">
                    <p><strong>Total Amount:</strong> $<span id="payTotal">0</span></p>
                    <div class="mb-3">
                        <label>Admin Message (Optional)</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="e.g. Paid via bank transfer"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm & Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        $('#selectAll').on('change', function() {
            $('.row-checkbox').prop('checked', this.checked);
            updateBulkButton();
        });

        $('.row-checkbox').on('change', updateBulkButton);

        function updateBulkButton() {
            let count = $('.row-checkbox:checked').length;
            if (count > 0) {
                $('#selectedCount').text(count);
                $('#bulkPayBtn').show();
            } else {
                $('#bulkPayBtn').hide();
            }
        }

        // Bulk Pay
        $('#bulkPayBtn').click(function() {
            let ids = $('.row-checkbox:checked').map(function() {
                return this.value;
            }).get();

            let total = 0;
            ids.forEach(id => {
                let amount = parseFloat($(`.pay-single[data-id="${id}"]`).data('amount')) || 0;
                total += amount;
            });

            openPayModal(ids, total);
        });

        // Single Pay
        $('.pay-single').click(function() {
            let id = $(this).data('id');
            let amount = parseFloat($(this).data('amount')) || 0;
            openPayModal([id], amount);
        });

        function openPayModal(ids, total) {
            let safeTotal = parseFloat(total) || 0;

            // Clear previous hidden inputs
            $('#payModal .earning-ids-container').remove();

            // Create new container for earning_ids[]
            let container = $('<div class="earning-ids-container"></div>');

            // Add hidden input for each ID as earning_ids[]
            ids.forEach(function(id) {
                container.append(`<input type="hidden" name="earning_ids[]" value="${id}">`);
            });

            // Append to form
            $('#payForm').append(container);

            // Update total display
            $('#payTotal').text(safeTotal.toFixed(2));

            // Show modal
            $('#payModal').modal('show');
        }

        // Form Submit
        $('#payForm').submit(function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('admin.expert-payouts.pay') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        $('#payModal').modal('hide');
                        location.reload();
                    } else {
                        toastr.error(res.message || 'Something went wrong!');
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors || {
                        earning_ids: ['Request failed']
                    };
                    let msg = Object.values(errors).flat().join('<br>');
                    toastr.error(msg);
                }
            });
        });
    });
</script>
@endpush