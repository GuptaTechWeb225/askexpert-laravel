<div class="modal-header">
    <h5>Payout Summary - {{ $expert->f_name }} {{ $expert->l_name }}</h5>
    <button type="button" class="btn-close btn btn-status" data-bs-dismiss="modal"> <i class="tio-clear"></i> </button>
</div>
<div class="modal-body">
    <p><strong>Category:</strong> {{ $expert->category?->name }}</p>
    <p><strong>Total Sessions:</strong> {{ $totalSessions }}</p>
    <p><strong>Total Earnings:</strong> ${{ number_format($totalEarnings, 2) }}</p>
    <p><strong>Paid Amount:</strong> ${{ number_format($paidAmount, 2) }}</p>
    <p><strong>Pending Payout:</strong> ${{ number_format($pendingAmount, 2) }}</p>
</div>
<div class="modal-footer">
    <a href="{{ route('admin.expert-payouts.setup', $expert->id) }}" class="btn btn-primary">Go to Payout Setup</a>
</div>