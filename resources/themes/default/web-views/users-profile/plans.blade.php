@extends('layouts.front-end.app')

@section('title', translate('my_Plans'))

@section('content')
<div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
    <div class="row">
        @include('web-views.partials._profile-aside')

        <section class="col-lg-9 __customer-profile px-0">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-4">{{ translate('my_Plans') }}</h3>
                </div>
                <div class="card-body">
                    <div class="card mb-4 border shadow-lg p-3 ">
                        <div class="card-header">
                            <h5 class="mb-0">{{ translate('membership_plans') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($subscriptions->count() > 0)
                            @foreach($subscriptions as $sub)
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                <div>
                                    <h6 class="mb-1">{{ $sub->category->name ?? 'Unknown Category' }}</h6>
                                    <small class="text-muted">
                                        ${{ number_format($sub->monthly_fee, 2) }} / month
                                        @if($sub->auto_renew)
                                        <span class="badge bg-success ms-2">{{ translate('auto_renew_on') }}</span>
                                        @else
                                        <span class="badge bg-danger ms-2">{{ translate('auto_renew_off') }}</span>
                                        @endif
                                    </small>
                                </div>
                                <div>
                                                                            @if($sub->auto_renew)

                                    <button class="btn btn-sm {{ $sub->auto_renew ? 'btn-outline-danger' : 'btn-outline-success' }} toggle-auto-renew"
                                        data-id="{{ $sub->id }}"
                                        data-current="{{ $sub->auto_renew ? 'on' : 'off' }}">
                                        {{ translate('cancel_auto_renew') }}
                                    </button>
                                                                            @endif

                                </div>
                            </div>
                            @endforeach
                            @else
                            <p class="text-muted">{{ translate('no_active_membership') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- 2. Joining Fee Card -->
                    <div class="card mb-4 border shadow-lg p-3">
                        <div class="card-header">
                            <h5 class="mb-0">{{ translate('joining_fees_paid') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($joiningPayments->count() > 0)
                            @foreach($joiningPayments as $payment)
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                <div>
                                    <small class="text-muted">One-time joining fee</small>
                                </div>
                                <strong class="text-success">${{ number_format($payment->amount, 2) }}</strong>
                            </div>
                            @endforeach
                            @else
                            <p class="text-muted">{{ translate('no_joining_fee_paid_yet') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="card border shadow-lgm p-3">
                        <div class="card-header text-dark">
                            <h5 class="mb-0">{{ translate('expert_consultation_fees') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($expertPayments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap">{{ translate('question') }}</th>
                                            <th class="text-nowrap">{{ translate('category') }}</th>
                                            <th class="text-nowrap">{{ translate('date') }}</th>
                                            <th class="text-nowrap">{{ translate('status') }}</th>
                                            <th class="text-nowrap">{{ translate('amount_paid') }}</th>
                                            <th class="text-nowrap">{{ translate('action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($expertPayments as $payment)
                                        <tr>
                                            <td>
                                                {{ Str::limit(optional($payment->chatSession->firstMessage)->message ?? 'N/A', 50) }}
                                            </td>
                                            <td>{{ $payment->chatSession->category->name ?? '-' }}</td>
                                            <td>{{ $payment->paid_at->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $payment->chatSession->status == 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($payment->chatSession->status) }}
                                                </span>
                                            </td>
                                            <td><strong>${{ number_format($payment->expert_fee, 2) }}</strong></td>
                                            <td>
                                                @php
                                                $session = $payment->chatSession;
                                                $canRequestRefund = $session->status === 'ended' &&
                                                $session->ended_at &&
                                                $session->ended_at->gt(now()->subHours(24));

                                                $refundRequest = $session->refundRequest; // hasOne relation se direct object milega
                                                @endphp

                                                @if($refundRequest)
                                                <!-- Agar refund request already hai → status badge dikhao -->
                                                <span class="badge bg-{{ 
            $refundRequest->status === 'pending' ? 'warning' : 
            ($refundRequest->status === 'approved' ? 'success' : 'danger') 
        }}">
                                                    Refund {{ ucfirst($refundRequest->status) }}
                                                </span>
                                                @elseif($canRequestRefund)
                                                <!-- Sirf eligible case mein button dikhao -->
                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm refund-btn"
                                                    data-chat-id="{{ $session->id }}"
                                                    data-amount="{{ number_format($payment->expert_fee, 2) }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#refundModal">
                                                    Refund
                                                </button>
                                                @endif
                                                <!-- Kuch aur nahi dikhega (na "-" na kuch) -->
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $expertPayments->links() }}
                            </div>
                            @else
                            <p class="text-muted">{{ translate('no_expert_consultation_yet') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>
</div>
<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalLabel">Request Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="refundForm">
                @csrf
                <input type="hidden" name="chat_session_id" id="refundChatId">
                <div class="modal-body">
                    <p><strong>Amount:</strong> $<span id="refundAmount">0.00</span></p>
                    <div class="mb-3">
                        <label for="refundReason" class="form-label">Reason for Refund</label>
                        <textarea class="form-control" id="refundReason" name="reason" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Submit Refund Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).on('click', '.toggle-auto-renew', function() {
        let button = $(this);
        let id = button.data('id');
        let current = button.data('current'); // 'on' or 'off'
        let isTurningOn = current === 'off';

        Swal.fire({
            title: isTurningOn ?
                '{{ translate("enable_auto_renew") }}?' : '{{ translate("cancel_auto_renew") }}?',
            text: isTurningOn ?
                '{{ translate("you_will_be_charged_next_month") }}' : '{{ translate("you_wont_be_charged_next_month") }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: isTurningOn ?
                '{{ translate("yes_enable_it") }}' : '{{ translate("yes_cancel_it") }}',
            cancelButtonText: '{{ translate("no_keep_current") }}',
            confirmButtonColor: isTurningOn ? '#28a745' : '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("subscription.cancel-auto-renew", ":id") }}'.replace(':id', id), {
                    _token: '{{ csrf_token() }}'
                }, function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload(); // Simple reload to update status
                    } else {
                        toastr.error(response.message || 'Something went wrong');
                    }
                }).fail(function() {
                    toastr.error('Request failed. Please try again.');
                });
            }
        });
    });


    $(document).ready(function() {
        $('.refund-btn').on('click', function() {
            let chatId = $(this).data('chat-id');
            let amount = $(this).data('amount');

            $('#refundChatId').val(chatId);
            $('#refundAmount').text(amount);

            $('#refundModal').modal('show');
        });

        $('#refundForm').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '{{ route("user.refund.request.store") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#refundModal').modal('hide');
                        location.reload(); // ya table refresh karo
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors;
                    if (errors?.reason) {
                        toastr.error(errors.reason[0]);
                    } else {
                        toastr.error('Something went wrong');
                    }
                }
            });
        });
    });
</script>
@endpush