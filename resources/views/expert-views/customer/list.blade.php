@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app-expert')

@section('title', translate('users_List'))

@section('content')

@php
$totalUniqueCustomers = $totalCustomers;
$newThisMonth = $customers->filter(function($customer){
return $customer->created_at >= now()->startOfMonth();
})->count();
$repeatVisitors = $totalUniqueCustomers - $newThisMonth;
$avgVisits = $totalUniqueCustomers > 0 ? $customers->sum('visits') / $totalUniqueCustomers : 0;
$totalCoinsEarned = $customers->sum('points_earned');
$totalCoinsUsed = $customers->sum('points_redeemed');
$redemptionRate = $totalCoinsEarned > 0 ? ($totalCoinsUsed / $totalCoinsEarned) * 100 : 0;
@endphp
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('users_List')}}
            <span class="badge badge-soft-dark radius-50">{{ $totalCustomers }}</span>
        </h2>
    </div>
    <div class="mb-4">
        <div class="row g-2">
            <!-- Card 1: Total Unique Customers -->
            <div class="col-12 col-sm-6 col-lg">
                <div class="stat-card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/total-customer.png')}}" class="stat-image" alt="Total Customers">
                    <div class="mt-3 stat-value">{{ $totalUniqueCustomers }}</div>
                    <div class="stat-title">Total Unique Customers</div>
                </div>
            </div>

            <!-- Card 2: New This Month -->
            <div class="col-12 col-sm-6 col-lg">
                <div class="stat-card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/3rd-party.png')}}" class="stat-image" alt="New This Month">
                    <div class="mt-3 stat-value">{{ $newThisMonth }}</div>
                    <div class="stat-title">New This Month</div>
                </div>
            </div>

            <!-- Card 3: Repeat Visitors -->
            <div class="col-12 col-sm-6 col-lg">
                <div class="stat-card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/refund-request.png')}}" class="stat-image" alt="Repeat Visitors">
                    <div class="mt-3 stat-value">{{ $repeatVisitors }}</div>
                    <div class="stat-title">Repeat Visitors</div>
                </div>
            </div>

            <!-- Card 4: Avg Visits per User -->
            <div class="col-12 col-sm-6 col-lg">
                <div class="stat-card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/total-sale.png')}}" class="stat-image" alt="Avg Visits">
                    <div class="mt-3 stat-value">{{ number_format($avgVisits,1) }}</div>
                    <div class="stat-title">Avg Visits per User</div>
                </div>
            </div>

            <!-- Card 5: Redemption Rate -->
            <div class="col-12 col-sm-6 col-lg">
                <div class="stat-card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/business_analytics.png')}}" class="stat-image" alt="Redemption Rate">
                    <div class="mt-3 stat-value">{{ number_format($redemptionRate,1) }}%</div>
                    <div class="stat-title">Redemption Rate</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 mr-auto">
                {{translate('User_list')}}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $totalCustomers }}</span>
            </h5>

            <form action="{{ url()->current() }}" method="GET">
                <div class="input-group input-group-merge input-group-custom">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <i class="tio-search"></i>
                        </div>
                    </div>
                    <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                        placeholder="{{ translate('search_by_Name_or_Email_or_Phone')}}" aria-label="Search orders" value="{{ request('searchValue') }}">
                    <button type="submit" class="btn btn--primary">{{ translate('search')}}</button>
                </div>
            </form>
            <div class="dropdown">
                <a type="button" class="btn btn-outline--primary text-nowrap"
                    href="{{ route('restaurant.customer.export') }}">
                    <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" alt="" class="excel">
                    <span class="ps-2">{{ translate('export') }}</span>
                </a>

            </div>
        </div>
        <div class="table-responsive datatable-custom">
            <table
                style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('name')}}</th>
                        <th>{{translate('contact_info')}}</th>
                        <th>{{translate('Visits')}} </th>
                        <th>{{translate('Point_Earn')}} </th>
                        <th>{{translate('Point_Redeemed')}} </th>
                        <th>{{translate('Last_Visit')}} </th>
                        <th class="text-center">{{translate('status')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $key=>$customer)
                    <tr>
                        <td>{{ $customers->firstItem() + $key }}</td>

                        <td>
                            <a href="{{route('restaurant.customer.view',[$customer->id])}}"
                                class="title-color hover-c1 d-flex align-items-center gap-10">
                                <img src="{{ getStorageImages(path:$customer->image_full_url,type:'backend-profile') }}"
                                    class="avatar rounded-circle" alt="" width="40">
                                {{ Str::limit($customer->f_name." ".$customer->l_name, 20) }}
                            </a>
                        </td>

                        <td>
                            <div class="mb-1">
                                <strong><a class="title-color hover-c1" href="mailto:{{ $customer->email }}">
                                        {{ $customer->email }}
                                    </a></strong>
                            </div>
                            <a class="title-color hover-c1" href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                        </td>

                        <td>{{ $customer->visits }}</td>
                        <td>{{ $customer->points_earned }}</td>
                        <td>{{ $customer->points_redeemed }}</td>
                        <td>{{ $customer->last_visit ? $customer->last_visit->format('d M Y, h:i A') : 'No Visit' }}</td>

                        <td class="text-center">
                            @if($customer->email == 'walking@customer.com')
                            <div class="badge badge-soft-version">{{ translate('default') }}</div>
                            @else
                            <form action="{{route('admin.customer.status-update')}}" method="post"
                                id="customer-status{{$customer->id}}-form" class="customer-status-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $customer->id }}">
                                <label class="switcher mx-auto">
                                    <input type="checkbox" class="switcher_input toggle-switch-message"
                                        id="customer-status{{$customer->id}}" name="is_active" value="1"
                                        {{ $customer->is_active == 1 ? 'checked':'' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </form>
                            @endif
                        </td>

                        <td class="text-center">
                            <a title="{{translate('view')}}" class="btn btn-outline-info btn-sm square-btn"
                                href="{{route('restaurant.customer.view',[$customer->id])}}">
                                <i class="tio-invisible"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $customers->links() !!}
            </div>
        </div>
        @if(count($customers)==0)
        @include('layouts.back-end._empty-state',['text'=>'No_users_found'],['image'=>'default'])
        @endif
    </div>
</div>
@endsection

@push('script')
<script type="text/javascript">
    changeInputTypeForDateRangePicker($('input[name="order_date"]'));
    changeInputTypeForDateRangePicker($('input[name="customer_joining_date"]'));
</script>
@endpush