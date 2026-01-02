@extends('layouts.back-end.app-expert')

@section('title', translate('user_Details'))

@push('css_or_js')
<link rel="stylesheet" href="{{dynamicAsset(path:'public/assets/back-end/css/owl.min.css')}}">
@endpush

@section('content')
<div class="content container-fluid">
    <div class="d-print-none pb-2">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <div class="mb-3">
                    <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                        <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                        {{translate('user_Details')}}
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-2">
        <div class="col-xl-6 col-xxl-4 col--xxl-4">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="mb-4 d-flex align-items-center gap-2">
                        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/vendor-information.png')}}"
                            alt="">
                        {{translate('user').' # '.$customer['id']}}
                    </h4>

                    <div class="customer-details-new-card">
                        <img src="{{ getStorageImages(path: $customer->image_full_url , type: 'backend-profile') }}"
                            alt="{{translate('image')}}" class="aspect-1">
                        <div class="customer-details-new-card-content">
                            <h6 class="name line--limit-2" data-toggle="tooltip" data-placement="top" title="{{$customer['f_name'].' '.$customer['l_name']}}">{{$customer['f_name'].' '.$customer['l_name']}}</h6>
                            <ul class="customer-details-new-card-content-list">
                                <li>
                                    <span class="key">{{translate('contact')}}</span>
                                    <span class="mr-3">:</span>
                                    <strong class="value">{{!empty($customer['phone']) ? $customer['phone'] : translate('no_data_found')}}</strong>
                                </li>
                                <li>
                                    <span class="key">{{translate('email')}}</span>
                                    <span class="mr-3">:</span>
                                    <strong class="value">{{$customer['email'] ?? translate('no_data_found')}}</strong>
                                </li>
                              
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-xxl-8 col--xxl-8 d-none d-lg-block">
            <div class="card h-100">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-sm-6 col-md-4 col-xl-6 col-xxl-4">
                            <a href="" class="order-stats">
                                <div class="order-stats__content">
                                    <img width="20" src="{{dynamicAsset(path:'public/assets/back-end/img/customer/total-order.png')}}" alt="">
                                    <h6 class="order-stats__subtitle text-capitalize">{{translate('restaurant_visit')}}</h6>
                                </div>
                                <span class="order-stats__title text--title">{{$orderStatusArray['restaurant_visit']}}</span>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-xl-6 col-xxl-4">
                            <a class="order-stats">
                                <div class="order-stats__content">
                                    <img width="20" src="{{dynamicAsset(path:'public/assets/back-end/img/customer/ongoing.png')}}" alt="">
                                    <h6 class="order-stats__subtitle">{{translate('total_reward')}}</h6>
                                </div>
                                <span class="order-stats__title text--title">{{$orderStatusArray['total_reward']}}</span>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-xl-6 col-xxl-4">
                            <a class="order-stats">
                                <div class="order-stats__content">
                                    <img width="20" src="{{dynamicAsset(path:'public/assets/back-end/img/customer/completed.png')}}" alt="">
                                    <h6 class="order-stats__subtitle">{{translate('total_redemption')}}</h6>
                                </div>
                                <span class="order-stats__title text--title">{{$orderStatusArray['total_redemption']}}</span>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-xl-6 col-xxl-4">
                            <a class="order-stats">
                                <div class="order-stats__content">
                                    <img width="20" src="{{dynamicAsset(path:'public/assets/back-end/img/customer/canceled.png')}}" alt="">
                                    <h6 class="order-stats__subtitle">{{translate('table_bookings')}}</h6>
                                </div>
                                <span class="order-stats__title text--title">{{$orderStatusArray['table_bookings']}}</span>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-xl-6 col-xxl-4">
                            <a class="order-stats">
                                <div class="order-stats__content">
                                    <img width="20" src="{{dynamicAsset(path:'public/assets/back-end/img/customer/returned.png')}}" alt="">
                                    <h6 class="order-stats__subtitle">{{translate('repeat_visit')}}</h6>
                                </div>
                                <span class="order-stats__title text--title">{{$orderStatusArray['repeat_visit']}}</span>
                            </a>
                        </div>
                        <div class="col-sm-6 col-md-4 col-xl-6 col-xxl-4">
                            <a class="order-stats">
                                <div class="order-stats__content">
                                    <img width="20" src="{{dynamicAsset(path:'public/assets/back-end/img/customer/failed.png')}}" alt="">
                                    <h6 class="order-stats__subtitle">{{translate('booking_cancelled')}}</h6>
                                </div>
                                <span class="order-stats__title text--title">{{$orderStatusArray['booking_cancelled']}}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h5 class="card-title m-0">{{translate('transactions')}} <span class="badge badge-secondary">{{$orders->total()}}</span> </h5>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="row">
                            <div class="col-auto">
                               
                            </div>
                        </div>
                       
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
                                <th>{{translate('date')}}</th>
                                <th>{{translate('status')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $key => $order)
                            <tr>
                                <td>{{ $orders->firstItem() + $key }}</td>
                                <td>
                                    <a href=""
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
    </div>
</div>
@endsection

@push('script')
<script src="{{dynamicAsset(path:'public/assets/back-end/js/owl.min.js')}}"></script>
<script type="text/javascript">
    'use strict';
    $('.order-statistics-slider, .address-slider').owlCarousel({
        margin: 16,
        loop: false,
        autoWidth: true,
    })
</script>
@endpush