@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app-expert')

@section('title', translate('Transaction'))

@section('content')


<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('Transaction')}}
            <span class="badge badge-soft-dark radius-50">{{ $totalTransactions }}</span>
        </h2>
    </div>
    <div class="mb-4">
        <div class="row g-2">
            <!-- Card 1: Total Unique Customers -->
            <div class="col-12 col-sm-6 col-lg">
                <div class="stat-card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/total-customer.png')}}" class="stat-image" alt="Total Customers">
                    <div class="mt-3 stat-value">{{ $totalTransactions }}</div>
                    <div class="stat-title">Total Transactions</div>
                </div>
            </div>

            <!-- Card 2: New This Month -->
            <div class="col-12 col-sm-6 col-lg">
                <div class="stat-card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/3rd-party.png')}}" class="stat-image" alt="New This Month">
                    <div class="mt-3 stat-value">{{ $totalBillAmount }}</div>
                    <div class="stat-title">Total Bill Amount</div>
                </div>
            </div>

            @if(App\Utils\Helpers::addon_permission_check('Average Bills'))
            <div class="col-12 col-sm-6 col-lg">
                <div class="stat-card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/refund-request.png')}}" class="stat-image" alt="Repeat Visitors">
                    <div class="mt-3 stat-value">{{ number_format($avgBillValue,2)  }}</div>
                    <div class="stat-title">Avg Bill Value</div>
                </div>
            </div>

            @else
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/refund-request.png')}}" class="stat-image" alt="Repeat Visitors">
                    <p class="text-muted text--primary btn-dashed-outline mb-0 rounded   p-2">
                        Buy a <strong>paid membership plan</strong> to view this
                    </p>
                    <div class="stat-title">Avg Bill Value</div>
                </div>
            </div>
            @endif
            <!-- Card 4: Avg Visits per User -->
            <div class="col-12 col-sm-6 col-lg">
                <div class="stat-card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/total-sale.png')}}" class="stat-image" alt="Avg Visits">
                    <div class="mt-3 stat-value">{{ $pointsAwarded }}</div>
                    <div class="stat-title">Points Awarded</div>
                </div>
            </div>
            <!-- Card 5: Redemption Rate -->
            <div class="col-12 col-sm-6 col-lg">
                <div class="stat-card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/business_analytics.png')}}" class="stat-image" alt="Redemption Rate">
                    <div class="mt-3 stat-value">{{ $pointsRedeemed}}</div>
                    <div class="stat-title">Points Redeemed</div>
                </div>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 mr-auto">
                {{translate('Transaction_list')}}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $totalTransactions }}</span>
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
                    href="{{ route('restaurant.transaction.export') }}">
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
                        <th>{{translate('Date')}}</th>
                        <th>{{translate('user')}}</th>
                        <th>{{translate('bill_ammount')}}</th>
                        <th>{{translate('Point_Earn')}} </th>
                        <th>{{translate('Point_Redeemed')}} </th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $key=>$customer)
                    <tr>
                        <td>{{ $transactions->firstItem() + $key }}</td> {{-- Correct serial number --}}
                        <td>{{ $customer->created_at->format('d M Y, h:i A') }}</td>

                        <td>
                            <a href="{{route('restaurant.customer.view',[$customer->customer->id])}}"
                                class="title-color hover-c1 d-flex align-items-center gap-10">
                                <img src="{{ getStorageImages(path:$customer->customer->image_full_url,type:'backend-profile') }}"
                                    class="avatar rounded-circle" alt="" width="40">
                                {{ Str::limit($customer->customer->f_name." ".$customer->customer->l_name, 20) }}
                            </a>
                        </td>
                        <td> <label class="btn text-info bg-soft-info font-weight-bold px-3 py-1 mb-0 fz-12">{{ $customer->final_amount }}</label></td>
                        <td><label class="btn bg-success text-dark font-weight-bold px-3 py-1 mb-0 fz-12">{{ $customer->coins_earned }}</label></td>
                        <td><label class="btn bg-c1 text-light font-weight-bold px-3 py-1 mb-0 fz-12">{{ $customer->coins_used }}</label></td>

                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a title="{{translate('view')}}" class="btn btn-outline-danger btn-sm square-btn"
                                    href="{{route('restaurant.customer.view',[$customer->customer->id])}}">
                                    <i class="tio-invisible"></i>
                                </a>

                            </div>

                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $transactions->links() !!}
            </div>
        </div>
        @if(count($transactions)==0)
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