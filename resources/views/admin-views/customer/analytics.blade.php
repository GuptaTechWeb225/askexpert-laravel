@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app')

@section('title', translate('user_Analytics'))

@section('content')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<style>


</style>
<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('user_Analytics')}}
        </h2>
    </div>

    <div class="row g-2 mb-2" id="order_stats">
        <div class="col-sm-6 col-lg-3">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('total_users')}}</h5>
                <h2 class="business-analytics__title">{{ $data['users'] }}</h2>
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/all-orders.png')}}" width="30" height="30" class="business-analytics__img" alt="">
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a class="business-analytics get-view-by-onclick card">
                <h5 class="business-analytics__subtitle">{{translate('total_Restaurants')}}</h5>
                <h2 class="business-analytics__title">{{ $data['restaurants'] }}</h2>
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/total-stores.png')}}" class="business-analytics__img" alt="">
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('total_Points_Awarded')}}</h5>
                <h2 class="business-analytics__title">{{ $data['points'] }}</h2>
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/total-product.png')}}" class="business-analytics__img" alt="">
            </a>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('total_Redemptions')}}</h5>
                <h2 class="business-analytics__title">{{ $data['redemption'] }}</h2>
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/total-customer.png')}}" class="business-analytics__img" alt="">
            </a>
        </div>
    </div>


    <div class="row g-1">
        <div class="col-lg-6 col-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center mb-3">
                    <div class=" d-flex flex-column justify-content-center">
                        <h5 class="mb-0 user-graph-heading">User Retention</h5>
                        <p class="" style="font-size: 13px">Shows how many users return each day after signing up.</p>
                    </div>
                    <div class="col-md-6 d-flex justify-content-center justify-content-md-end order-stat mb-3">
                        <ul class="option-select-btn earn-statistics-option">
                            <li>
                                <label class="basic-box-shadow">
                                    <input type="radio" name="statistics" value="MonthStatic" hidden="" checked>
                                    <span data-date-type="MonthStatic" class="month-statistics">{{translate('this_Month')}}</span>
                                </label>
                            </li>
                            <li>
                                <label class="basic-box-shadow">
                                    <input type="radio" name="statistics" value="WeekStatic" hidden="">
                                    <span data-date-type="WeekStatic" class="week-statistics">{{translate('this_Week')}}</span>
                                </label>
                            </li>

                            <li>
                                <label class="basic-box-shadow">
                                    <input type="radio" name="statistics" hidden="" value="DailyStatic">
                                    <span data-date-type="DailyStatic" class="day-statistics">{{translate('This_day')}}</span>
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
                <div id="retention-curve-chart" class="card-body"></div>
            </div>
        </div>
        <div class="col-lg-6 col-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 user-graph-heading">Restaurant Visited</h5>

                    <div class="col-md-8 d-flex justify-content-center justify-content-md-end order-stat mb-3">
                        <ul class="second-option-select-btn order-statistics-option list-inline">
                            <li>
                                <label class="basic-box-shadow">
                                    <input type="radio" name="statistics4" hidden="" value="visited" checked="">
                                    <span data-date-type="dayVisit" class="visit-statistics">Visited</span>
                                </label>
                            </li>
                            <li>
                                <label class="basic-box-shadow">
                                    <input type="radio" name="statistics4" value="booked" hidden="">
                                    <span data-date-type="dayBookd" class="booked-statistics">Booked</span>
                                </label>
                            </li>
                            <li>
                                <label class="basic-box-shadow">
                                    <input type="radio" name="statistics4" value="ordered" hidden="">
                                    <span data-date-type="dayOrders" class="orders-statistics">Ordered</span>
                                </label>
                            </li>
                            <li>
                                <label class="basic-box-shadow">
                                    <input type="radio" name="statistics4" value="cancelled" hidden="">
                                    <span data-date-type="dayCencelled" class="cencelled-statistics">Cancelled</span>
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
                <div id="restaurant-visited-chart" class="card-body"></div>
            </div>
        </div>

    </div>

    <div class="card">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 mr-auto">
                {{translate('User_list')}}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $customers->total() }}</span>
            </h5>

            <form action="{{ url()->current() }}" method="GET">
                <input type="hidden" name="order_date" value="{{request('order_date')}}">
                <input type="hidden" name="customer_joining_date" value="{{request('customer_joining_date')}}">
                <input type="hidden" name="is_active" value="{{request('is_active')}}">
                <input type="hidden" name="sort_by" value="{{request('sort_by')}}">
                <input type="hidden" name="choose_first" value="{{request('choose_first')}}">
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
                        <th>{{translate('Points')}} </th>
                        <th>{{translate('Last_visit')}} </th>
                        <th>{{translate('Point_earned')}} </th>
                        <th>{{translate('Point_redem')}} </th>
                        <th>{{translate('Last_Active')}} </th>
                        <th class="text-center">{{translate('status')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $key=>$customer)
                    <tr>
                        <td>
                            {{$customers->firstItem()+$key}}
                        </td>
                        <td>
                            <a href="{{route('admin.customer.view',[$customer['id']])}}"
                                class="title-color hover-c1 d-flex align-items-center gap-10">
                                <img src="{{getStorageImages(path:$customer->image_full_url,type:'backend-profile')}}"
                                    class="avatar rounded-circle " alt="" width="40">
                                {{Str::limit($customer['f_name']." ".$customer['l_name'],20)}}
                            </a>
                        </td>
                        <td>
                            <div class="mb-1">
                                <strong><a class="title-color hover-c1"
                                        href="mailto:{{$customer->email}}">{{$customer->email}}</a></strong>

                            </div>
                            <a class="title-color hover-c1" href="tel:{{$customer->phone}}">{{$customer->phone}}</a>

                        </td>
                        <td>
                            <label class="btn text-info bg-soft-info font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{$customer->loyalty_point ?? 0}}
                            </label>
                        </td>

                        <td>
                            <div>
                                @if($customer->lastBillPayment && $customer->lastBillPayment->restaurant_id)
                                <a href="{{ route('admin.restaurant.view', ['user_id' => $customer->lastBillPayment->restaurant_id]) }}"
                                    class="title-color hover-c1 d-flex align-items-center gap-10">
                                    <img src="{{ getStorageImages(path: $customer->lastBillPayment?->restaurant?->image_full_url, type:'backend-profile') }}"
                                        class="avatar rounded-circle " alt="" width="40">
                                    <label class="btn text-info bg-soft-info font-weight-bold px-3 py-1 mb-0 fz-12">
                                        {{ Str::limit($customer->lastBillPayment?->restaurant?->restaurant_name ?? '-', 20) }}
                                    </label>
                                </a>
                                @else
                                <span class="text-danger">No Visits</span>
                                @endif
                            </div>
                        </td>


                        <td>
                            <label class="btn bg-success text-dark font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ $customer->lastBillPayment?->coins_earned ?? 0 }}
                            </label>

                        </td>

                        <td>

                            <label class="btn bg-c1 text-light font-weight-bold px-3 py-1 mb-0 fz-12">
                                {{ $customer->lastBillPayment?->coins_used ?? 0 }}
                            </label>
                        </td>

                        <td>
                            {{ $customer->updated_at ? $customer->updated_at->format('d M Y, h:i A') : 'Inactive' }}
                        </td>
                        <td>
                            @if($customer['email'] == 'walking@customer.com')
                            <div class="text-center">
                                <div class="badge badge-soft-version">{{ translate('default') }}</div>
                            </div>
                            @else
                            <form action="{{route('admin.customer.status-update')}}" method="post"
                                id="customer-status{{$customer['id']}}-form" class="customer-status-form">
                                @csrf
                                <input type="hidden" name="id" value="{{$customer['id']}}">
                                <label class="switcher mx-auto">
                                    <input type="checkbox" class="switcher_input toggle-switch-message"
                                        id="customer-status{{$customer['id']}}" name="is_active" value="1"
                                        {{ $customer['is_active'] == 1 ? 'checked':'' }}
                                        data-modal-id="toggle-status-modal"
                                        data-toggle-id="customer-status{{$customer['id']}}"
                                        data-on-image="customer-block-on.png"
                                        data-off-image="customer-block-off.png"
                                        data-on-title="{{translate('want_to_unblock').' '.$customer['f_name'].' '.$customer['l_name'].'?'}}"
                                        data-off-title="{{translate('want_to_block').' '.$customer['f_name'].' '.$customer['l_name'].'?'}}"
                                        data-on-message="<p>{{translate('if_enabled_this_customer_will_be_unblocked_and_can_log_in_to_this_system_again')}}</p>"
                                        data-off-message="<p>{{translate('if_disabled_this_customer_will_be_blocked_and_cannot_log_in_to_this_system')}}</p>">
                                    <span class="switcher_control"></span>
                                </label>
                            </form>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a title="{{translate('view')}}"
                                    class="btn btn-outline-info btn-sm square-btn"
                                    href="{{route('admin.customer.view',[$customer['id']])}}">
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

<script>
    const restaurantGraphData = @json($restaurantGraphData);

   
    const restaurantChart = new ApexCharts(document.querySelector("#restaurant-visited-chart"), {
        chart: { type: 'line', height: 300, toolbar: { show: false } },
        series: [{ name: 'Visited', data: restaurantGraphData.visited.series }],
        xaxis: { categories: restaurantGraphData.visited.categories },
        stroke: { curve: 'smooth', width: 3, colors: ['#D92D20'] },
        markers: { size: 0 },
        dataLabels: { enabled: false },
        yaxis: {
            labels: {
                formatter: val => val >= 1000 ? `${val/1000}k` : val
            }
        },
        grid: { borderColor: '#eee', strokeDashArray: 5 },
        tooltip: { y: { formatter: val => val >= 1000 ? val/1000 + 'k' : val } }
    });
    restaurantChart.render();

    // click handler for toggle
    document.querySelectorAll("input[name='statistics4']").forEach(item => {
        item.addEventListener("change", function() {
            const selected = this.value;
            const data = restaurantGraphData[selected];

            restaurantChart.updateOptions({
                series: [{ name: data.name, data: data.series }],
                xaxis: { categories: data.categories }
            });
        });
    });

    // === Retention Curve Chart ===
    const retentionData = {
        daily: {
            categories: @json($retentionDaily -> pluck('date')),
            series: @json($retentionDaily -> pluck('retention'))
        },
        weekly: {
            categories: @json($retentionWeekly -> pluck('day')),
            series: @json($retentionWeekly -> pluck('retention'))
        },
        monthly: {
            categories: @json($retentionMonthly -> pluck('day')),
            series: @json($retentionMonthly -> pluck('retention'))
        }
    };

    const retentionChart = new ApexCharts(document.querySelector("#retention-curve-chart"), {
        chart: {
            type: 'area',
            height: 300,
            toolbar: {
                show: false
            }
        },
        series: [{
            name: 'Users Returned',
            data: retentionData.monthly.series
        }],
        xaxis: {
            categories: retentionData.monthly.categories,
            title: {
                text: 'Month', // 👈 Daily = Dates, Weekly = Weeks, Monthly = Months
                style: {
                    fontSize: '12px',
                    fontWeight: 600
                }
            },
            labels: {
                style: {
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            title: {
                text: 'Retention %', // 👈 Y-axis title
                style: {
                    fontSize: '12px',
                    fontWeight: 600
                }
            },
            labels: {
                formatter: val => `${val}%`,
                style: {
                    fontSize: '12px'
                }
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3,
            colors: ['#D92D20']
        },
        markers: {
            size: 4,
            colors: ['#D92D20'],
            strokeWidth: 0,
            hover: {
                sizeOffset: 4
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.4,
                gradientToColors: ['#D92D20'],
                inverseColors: false,
                opacityFrom: 0.4,
                opacityTo: 0,
                stops: [0, 90, 100]
            }
        },
        dataLabels: {
            enabled: false
        },
        tooltip: {
            y: {
                formatter: val => `${val}%`
            }
        },
        grid: {
            borderColor: '#eee'
        }
    });
    retentionChart.render();

    // 👇 radio button select par chart update hoga
    document.querySelectorAll("input[name='statistics']").forEach(radio => {
        radio.addEventListener("change", function() {
            let type = this.value;

            if (type === "DailyStatic") type = "daily";
            if (type === "MonthStatic") type = "monthly";
            if (type === "WeekStatic") type = "weekly";

            retentionChart.updateOptions({
                xaxis: {
                    categories: retentionData[type].categories,
                    title: {
                        text: type === "daily" ? "Date" : type === "weekly" ? "Days" : "Month"
                    }
                },
                series: [{
                    name: 'Users Returned',
                    data: retentionData[type].series
                }]
            });
        });
    });
</script>
@endpush