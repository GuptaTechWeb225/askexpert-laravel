<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-muted mb-3">Customer Information</h6>
                <p><strong>Name:</strong> {{ $session->customer?->f_name . ' ' . $session->customer?->l_name ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $session->customer?->phone ?? 'Not available' }}</p>
                <p><strong>Email:</strong> {{ $session->customer?->email ?? 'Not available' }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-muted mb-3">Question Information</h6>
                <p><strong>Title:</strong> {{ Str::limit($session->firstMessage?->message ?? 'No message yet', 120) }}</p>
                <p><strong>Category:</strong> {{ $session->category?->name ?? 'Not Assigned' }}</p>
                <p><strong>Started At:</strong> {{ $session->started_at->format('d M Y, h:i A') }}</p>
                <p><strong>Total Messages:</strong> {{ $totalMessages }}</p>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-muted mb-3">Expert & Status</h6>
                <p><strong>Assigned Expert:</strong> 
                    @if($session->expert)
                        {{ $session->expert->f_name . ' ' . $session->expert->l_name }}
                        <small class="text-muted">({{ $session->expert->category?->name ?? 'No category' }})</small>
                    @else
                        <span class="badge btn btn-outline--warning p-1 ">Not Assigned</span>
                    @endif
                </p>
                <p><strong>Chat Status:</strong> 
                    @if($session->ended_at)
                        <span class="badge btn btn-outline--danger p-1 ">Ended</span>
                        <small>Ended at: {{ $session->ended_at->format('d M Y, h:i A') }}</small>
                    @elseif($session->expert_id)
                        <span class="badge btn btn-outline--success p-1">Active</span>
                    @else
                        <span class="badge btn btn-outline--warning p-1 ">Waiting for Assignment</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
