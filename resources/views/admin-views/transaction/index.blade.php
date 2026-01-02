@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Analytics'))

@section('content')


<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('transactions')}}
        </h2>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ url()->current() }}" method="GET">
                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">{{translate('transaction_Date')}}</label>
                        <div class="position-relative">
                            <span class="tio-calendar icon-absolute-on-right cursor-pointer"></span>
                            <input type="text" name="transaction_date" class="js-daterangepicker-with-range form-control cursor-pointer" value="{{request('transaction_date')}}" placeholder="{{ translate('Select_Date') }}" autocomplete="off" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{translate('Choose_First')}}</label>
                        <input type="number" class="form-control" min="1" value="{{ request('choose_first') }}" placeholder="{{translate('Ex')}} : {{translate('100')}}" name="choose_first">
                    </div>
                    <div class="col-md-12">
                        <label class="d-md-block">&nbsp;</label>
                        <div class="btn--container justify-content-end">
                            <a href="{{ route('admin.transaction.index') }}"
                                class="btn btn-secondary px-5">
                                {{ translate('reset') }}
                            </a>
                            <button type="submit" class="btn btn--primary">{{translate('Filter')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>



    <div class="card">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 mr-auto">
                {{translate('transactions')}} <span class="badge badge-secondary">{{$totalCount}}</span>
            </h5>

            <form action="{{ url()->current() }}" method="GET">
                <input type="hidden" name="choose_first" value="{{request('choose_first')}}">
                <input type="hidden" name="transaction_date" value="{{request('transaction_date')}}">
                <div class="input-group input-group-merge input-group-custom">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            <i class="tio-search"></i>
                        </div>
                    </div>
                    <input id="datatableSearch_" type="search" name="searchValue"
                        class="form-control"
                        placeholder="{{translate('search_transactions')}}" aria-label="Search transaction"
                        value="{{ request('searchValue') }}"
                        required>
                    <button type="submit" class="btn btn--primary">{{translate('search')}}</button>
                </div>
            </form>

            <div class="dropdown">
                <a type="button" class="btn btn-outline--primary text-nowrap"
                    href="{{ route('admin.transaction.export', [
            'transaction_date' => request('transaction_date'),
            'choose_first'     => request('choose_first'),
            'searchValue'      => request('searchValue')
       ]) }}">
                    <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" alt="" class="excel">
                    <span class="ps-2">{{ translate('export') }}</span>
                </a>
            </div>

        </div>
        <div class="table-responsive datatable-custom">
            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{translate('sl')}}</th>
                        <th>{{translate('restaurant_Name')}}</th>
                        <th>{{translate('total_ammount')}}</th>
                        <th>{{translate('Fainal_ammount')}}</th>
                        <th>{{translate('point_Used')}}</th>
                        <th>{{translate('point_Earned')}}</th>
                        <th>{{translate('coupon_code')}}</th>
                        <th>{{translate('date')}}</th>
                        <th>{{translate('status')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $key => $order)
                    <tr>
                        <td>{{ $key + 1  }}</td>
                        <td>
                            <a href="{{route('admin.restaurant.view',[$order->restaurant?->id])}}"
                                class="title-color hover-c1 d-flex align-items-center gap-10">
                                <img src="{{getStorageImages(path:$order->restaurant?->image_full_url,type:'backend-profile')}}"
                                    class="avatar rounded-circle " alt="" width="40">
                                <label class="btn text-info bg-soft-info font-weight-bold px-3 py-1 mb-0 fz-12">

                                    {{Str::limit( $order->restaurant?->restaurant_name,20)}}
                                </label>

                            </a>
                        </td>
                        <td>₹{{ number_format($order->original_amount,2) }}</td>
                        <td>₹{{ number_format($order->final_amount,2) }}</td>
                        <td> <label class="btn bg-c1 text-light font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ $order->coins_used }} </label>
                        </td>

                        <td> <label class="btn bg-success text-dark font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ $order->coins_earned }} </label>
                        </td>

                        <td>{{ $order->coupon_code ?? 'No Coupon Use' }}</td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td> <label class="btn text-info bg-soft-info font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ $order->status }} </label>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-responsive mt-4">
            <div class="d-flex justify-content-lg-end">
                {!! $orders->links() !!}
            </div>
        </div>
        @if(count($orders)==0)
        @include('layouts.back-end._empty-state',['text'=>'no_transaction_found'],['image'=>'default'])
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