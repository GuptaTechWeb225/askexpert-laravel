@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('Transaction_Analytics'))

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('transaction_Analytics')}}
        </h2>
    </div>

    <div class="card common-card-shadow bg-white p-4 mb-2">
        <div class="daily-bill-chart-container" id="daily-bill-chart"></div>
    </div>
    <div class="card common-card-shadow bg-white p-4 mb-2">
        <div id="reward-type-usage-chart"></div>
    </div>

    <div class="row g-2">

        <div class="col-lg-12">
            <div class="card">
                <div class="p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h5 class="card-title m-0">{{translate('transactions')}} <span class="badge badge-secondary">{{$totalCount}}</span> </h5>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="row">
                            <div class="col-auto">
                                <form action="{{ url()->current() }}" method="GET">
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
    </div>

</div>

@endsection

@push('script')
<script type="text/javascript">
    changeInputTypeForDateRangePicker($('input[name="order_date"]'));
    changeInputTypeForDateRangePicker($('input[name="customer_joining_date"]'));
</script>
<script>
    var options = {
        chart: {
            type: 'area',
            height: 350,
            toolbar: {
                show: false
            }
        },
        series: [{
            name: 'Daily Bill',
            data: @json($amounts) // Laravel se data inject
        }],
        xaxis: {
            categories: @json($dates), // Din ke hisaab se label
            labels: {
                style: {
                    colors: '#333',
                    fontSize: '14px'
                }
            }
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return val.toLocaleString();
                },
                style: {
                    colors: '#333',
                    fontSize: '14px'
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return "₹" + val.toLocaleString();
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3,
            colors: ['#FF7300']
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0,
                stops: [0, 90, 100],
                colorStops: [{
                        offset: 0,
                        color: "#FF7300",
                        opacity: 0.4
                    },
                    {
                        offset: 100,
                        color: "#FF7300",
                        opacity: 0
                    }
                ]
            }
        },
        markers: {
            size: 5,
            colors: ['#FF7300'],
            strokeWidth: 0,
            hover: {
                size: 7
            }
        },
        title: {
            text: 'Daily Bill Value (Last 30 Days)',
            align: 'left',
            style: {
                fontSize: '18px',
                fontWeight: 'bold'
            }
        }
    };

    var chart = new ApexCharts(document.querySelector(".daily-bill-chart-container"), options);
    chart.render();

    // Laravel Blade me controller se data pass ho raha hai
    var rewardDays = @json($rewardDays); // x-axis: days
    var couponUsed = @json($couponUsed); // series 1
    var coinsUsed = @json($coinsUsed); // series 2

    var options = {
        chart: {
            type: 'line',
            height: 350,
            toolbar: {
                show: false
            },
        },
        series: [{
                name: 'Coupon Used',
                data: couponUsed,
                color: '#28a745', // green
            },
            {
                name: 'Coins Used',
                data: coinsUsed,
                color: '#dc3545', // red
            },
        ],
        xaxis: {
            categories: rewardDays, // x-axis days
            labels: {
                style: {
                    colors: '#7e2332',
                    fontSize: '14px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Usage Count',
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold',
                    color: '#333'
                }
            },
            labels: {
                style: {
                    colors: '#7e2332',
                    fontSize: '14px'
                }
            }
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function(val) {
                    return val.toLocaleString();
                }
            }
        },
        markers: {
            size: 0
        },
        stroke: {
            width: 3,
            curve: 'smooth', // smooth line
            colors: ['#28a745', '#dc3545'] // green & red
        },
        fill: {
            type: 'solid',
            opacity: 1 // solid fill
        },
        title: {
            text: 'Reward Type Usage (Last 30 Days)',
            align: 'left',
            style: {
                fontSize: '18px',
                fontWeight: 'bold',
                color: '#333'
            }
        },
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            floating: true,
            offsetY: 30,
            markers: {
                width: 12,
                height: 12,
                radius: 12
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#reward-type-usage-chart"), options);
    chart.render();
</script>
@endpush