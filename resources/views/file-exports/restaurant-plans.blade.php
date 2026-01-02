<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Restaurant Name</th>
            <th>Owner</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Plan</th>
            <th>Status</th>
            <th>Purchase Date</th>
            <th>Expiry Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($plans as $key => $plan)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $plan->restaurant->restaurant_name ?? '' }}</td>
                <td>{{ $plan->restaurant->owner_name ?? '' }}</td>
                <td>{{ $plan->restaurant->email ?? '' }}</td>
                <td>{{ $plan->restaurant->phone ?? '' }}</td>
                <td>{{ $plan->plan->plan_name ?? '' }}</td>
                <td>{{ ucfirst($plan->status) }}</td>
                <td>{{ $plan->created_at?->format('d M Y') }}</td>
                <td>{{ $plan->expiry_date?->format('d M Y') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
