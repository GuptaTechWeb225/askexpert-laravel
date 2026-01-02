@php use Illuminate\Support\Str; @endphp
@extends('layouts.back-end.app-expert')

@section('title', translate('rewards_Performance'))

@section('content')

<style>
    #top-rewards-chart {
        max-width: 100%;
        overflow-x: auto;
    }
</style>



<div class="content container-fluid">
    <div class="mb-4">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img width="20" src="{{dynamicAsset(path: 'public/assets/back-end/img/customer.png')}}" alt="">
            {{translate('Reward_Analytics')}}
        </h2>
    </div>
    <div class="mb-4">
        <div class="row g-2">
            <!-- Card 2: New This Month -->
            <div class="col-12 col-sm-6 col-lg">
                <div class="card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/3rd-party.png')}}" class="stat-image" alt="New This Month">
                    <div class="mt-3 stat-value">₹ {{ $totalBillAmount }}</div>
                    <div class="stat-title">Total Bill Value</div>
                </div>
            </div>

            <!-- Card 3: Repeat Visitors -->
            <div class="col-12 col-sm-6 col-lg">
                <div class="card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/refund-request.png')}}" class="stat-image" alt="Repeat Visitors">
                    <div class="mt-3 stat-value">₹ {{ number_format($avgBillValue,2)  }}</div>
                    <div class="stat-title">Avg Bill per Customer</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg">
                <div class="card p-3 align-items-center">
                    <img src="{{dynamicAsset('public/assets/back-end/img/total-customer.png')}}" class="stat-image" alt="Total Customers">
                    <div class="mt-3 stat-value">₹ {{ $rewardValue }}</div>
                    <div class="stat-title">Rewards Value</div>
                </div>
            </div>

        </div>
    </div>
    <div class="row g-2 my-2 align-items-stretch">
        <div class="col-lg-8">
            <div class="card graph-card h-100 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fz-18">Top 5 Rewards</h5>
                    <div class="col-md-6 d-flex justify-content-center justify-content-md-end order-stat mb-0">
                        <ul class="option-select-btn earn-statistics-option">
                            <li>
                                <label class="basic-box-shadow">
                                    <input type="radio" name="statistics" value="YearStatic" hidden="" checked>
                                    <span data-date-type="YearStatic" class="year-statistics">{{translate('this_Year')}}</span>
                                </label>
                            </li>
                            <li>
                                <label class="basic-box-shadow">
                                    <input type="radio" name="statistics" value="MonthStatic" hidden="">
                                    <span data-date-type="MonthStatic" class="month-statistics">{{translate('this_Month')}}</span>
                                </label>
                            </li>

                            <li>
                                <label class="basic-box-shadow">
                                    <input type="radio" name="statistics" hidden="" value="WeekStatic">
                                    <span data-date-type="WeekStatic" class="week-statistics">{{translate('This_Week')}}</span>
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div id="top-rewards-chart"></div>
                </div>
            </div>
        </div>

        <!-- Popular Rewards Donut Chart Card -->
        <div class="col-lg-4 d-flex">
            <div class="card remove-card-shadow w-100 h-100 d-flex flex-column">
                <div class="card-header">
                    <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                        {{ translate('popular Rewards') }}
                    </h4>
                </div>
                <div class="card-body justify-content-center d-flex flex-column">
                    <div>
                        <div class="position-relative">
                            <div id="chart" class="apex-pie-chart d-flex justify-content-center"></div>
                            <div class="total--orders">
                                <h3>{{ array_sum($userData) }}</h3>
                                <span class="text-capitalize">{{ translate('total_Rewards') }}</span>
                            </div>
                        </div>
                        <div class="apex-legends flex-column">
                            @foreach($userData as $index => $count)
                            <div class="before-bg-{{ $index }}">
                                <span class="text-capitalize">
                                    {{ $userLabels[$index] ?? 'Other' }} ({{ $count }})
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 mr-auto">
                {{translate('Rewards_list')}}
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

        </div>
        <div class="table-responsive datatable-custom">
            <table
                style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>SL</th>
                        <th>Coupon Code</th>
                        <th>Usage Count</th>
                        <th>Total Amount</th>
                        <th>Avg. Bill Value</th>
                        <th>Usage %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($couponStateTable as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row['coupon_code'] }}</td>
                        <td>{{ $row['usage_count'] }}</td>
                        <td>{{ number_format($row['total_amount'], 2) }}</td>
                        <td>{{ number_format($row['avg_bill_value'], 2) }}</td>
                        <td>{{ $row['usage_percentage'] }}%</td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        @if(count($couponStateTable)==0)
        @include('layouts.back-end._empty-state',['text'=>'No_users_found'],['image'=>'default'])
        @endif
    </div>
</div>

<span id="user-overview-data"
    style="background-color: #000;"
    data-customer="{{ $userData[0] ?? 0 }}"
    data-customer-title="{{ $userLabels[0] ?? '' }}"
    data-vendor="{{ $userData[1] ?? 0 }}"
    data-vendor-title="{{ $userLabels[1] ?? '' }}"
    data-delivery-man="{{ $userData[2] ?? 0 }}"
    data-delivery-man-title="{{ $userLabels[2] ?? '' }}"
    data-other="{{ $userData[3] ?? 0 }}"
    data-other-title="{{ $userLabels[3] ?? 'Other' }}">
</span>
@endsection

@push('script')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/apexcharts.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/dashboard.js')}}"></script>

<script type="text/javascript">
    changeInputTypeForDateRangePicker($('input[name="order_date"]'));
    changeInputTypeForDateRangePicker($('input[name="customer_joining_date"]'));
</script>

<script>
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


<script>
    function UserOverViewChart() {
        const userOverViewData = $("#user-overview-data");

        var options = {
            series: [
                userOverViewData.data("customer"), // free meals
                userOverViewData.data("vendor"), // gift card
                userOverViewData.data("delivery-man"), // discount
                userOverViewData.data("other") // other
            ],
            labels: [
                userOverViewData.data("customer-title"),
                userOverViewData.data("vendor-title"),
                userOverViewData.data("delivery-man-title"),
                userOverViewData.data("other-title")
            ],
            chart: {
                width: 320,
                type: "donut",
            },
            dataLabels: {
                enabled: false,
            },
            colors: ["#7bc4ff", "#f9b530", "#1c1a93", "#4CAF50"], // 4th color added
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200,
                    },
                },
            }, ],
            legend: {
                show: false,
            },
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();
    }
    UserOverViewChart();
</script>

<script>
    const topRewards = {
        week: @json($topRewardsWeek),
        month: @json($topRewardsMonth),
        year: @json($topRewardsYear)
    };

    console.log("Week Data:", topRewards.week);
    console.log("Month Data:", topRewards.month);
    console.log("Year Data:", topRewards.year);

    function formatChartData(data) {
        return {
            series: [{
                data: data.map(item => ({
                    x: item.coupon_code,
                    y: item.usage_count,
                    label: item.coupon_code + " (" + item.usage_count + ")"
                }))
            }],
            categories: data.map(item => item.coupon_code)
        };
    }

    function getColumnWidth(dataLength) {
        // Agar data 1 ya 2 hai to width chhoti rakho
        if (dataLength <= 2) return '10%';
        if (dataLength === 3) return '15%';
        return '25%'; // default
    }

    var initialData = formatChartData(topRewards.year);

    var topRewardsOptions = {
        chart: {
            type: 'bar',
            height: 350,
            width: "100%",
            toolbar: {
                show: false
            },
        },
        series: initialData.series,
        plotOptions: {
            bar: {
                horizontal: false,
                distributed: true,
                borderRadius: 5,
                columnWidth: getColumnWidth(initialData.series[0].data.length),
                dataLabels: {
                    position: 'top'
                }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function(val, opts) {
                return opts.w.config.series[opts.seriesIndex].data[opts.dataPointIndex].label;
            },
            offsetY: -30, // upar thoda gap diya
            style: {
                fontSize: '12px',
                colors: ['#333'],
                fontWeight: '600'
            }
        },
        colors: ['#F19332', '#FAD85D', '#C3322F', '#50AF00', '#004D9B', '#9B51E0'],
        legend: {
            show: false
        },
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 4
        },
        xaxis: {
            categories: initialData.categories,
            labels: {
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold'
                }
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: '14px',
                    fontWeight: 'bold'
                }
            }
        },
        tooltip: {
            y: {
                formatter: val => val + " Uses"
            }
        }
    };

    var topRewardsChart = new ApexCharts(document.querySelector("#top-rewards-chart"), topRewardsOptions);
    topRewardsChart.render();

    document.querySelectorAll("input[name='statistics']").forEach(el => {
        el.addEventListener("change", function() {
            let type = this.value;
            let formatted;
            if (type === "WeekStatic") formatted = formatChartData(topRewards.week);
            else if (type === "MonthStatic") formatted = formatChartData(topRewards.month);
            else formatted = formatChartData(topRewards.year);

            // Update series
            topRewardsChart.updateSeries(formatted.series);

            // Update column width dynamically
            topRewardsChart.updateOptions({
                xaxis: {
                    categories: formatted.categories
                },
                plotOptions: {
                    bar: {
                        columnWidth: getColumnWidth(formatted.series[0].data.length)
                    }
                }
            });
        });
    });
</script>
@endpush