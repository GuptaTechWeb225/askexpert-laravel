@php use App\Utils\Helpers; @endphp
@extends('layouts.back-end.app')
@section('title', translate('dashboard'))
@push('css_or_js')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@endpush

@section('content')

<div class="content container-fluid">
    <div class="mt-4">
        <div class="banner-container d-flex justify-content-between" style="background-image: url('{{ asset('assets/back-end/img/home-banner-bg-1.jpg') }}');">
            <div class="banner-content">
                <div class="banner-text">
                    <h3>Welcome Back, Mr. Admin👋</h3>
                    <p>Have a good day.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="py-2 mt-3">
        <div class="row g-3">
            <!-- Card 1 -->
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm px-4 py-3 border-light rounded-4">
                    <p class="fs-5 mb-0">Total Questions Today</p>
                    <div class="mt-2 d-flex justify-content-between">
                        <p class="fs-5 text-dark">{{ $totalQuestions }}</p>
                        <img src="{{ asset('assets/back-end/img/dahboard/admin-dash-card-3.png') }}" alt="">
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm px-4 py-3 border-light rounded-4">
                    <p class="fs-5 mb-0">Pending Questions</p>
                    <div class="mt-2 d-flex justify-content-between">
                        <p class="fs-5 text-dark">{{ $pendingQuestions }}</p>
                        <img src="{{ asset('assets/back-end/img/dahboard/admin-dash-card-4.png') }}" alt="">
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm px-4 py-3 border-light rounded-4">
                    <p class="fs-5 mb-0">Refund Requests</p>
                    <div class="mt-2 d-flex justify-content-between">
                        <p class="fs-5 text-dark">{{ $refundRequests }}</p>
                        <img src="{{ asset('assets/back-end/img/dahboard/admin-dash-card-1.png') }}" alt="">
                    </div>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm px-4 py-3 border-light rounded-4">
                    <p class="fs-5 mb-0"> Revenue Today</p>
                    <div class="mt-2 d-flex justify-content-between">
                        <p class="fs-5 text-dark">$ {{ $revenueToday }}</p>
                        <img src="{{ asset('assets/back-end/img/dahboard/admin-dash-card-2.png') }}" alt="">
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="row gx-2 align-items-stretch mb-5">
        <!-- Graph -->
        <div class="col-12 col-lg-8 d-flex">
            <div class="card bar-card shadow rounded-3 my-3 py-3 border border-2 border-light h-100 w-100">
                <div
                    class="card-header chart-card-header bg-white pt-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                    <span>Plan Statistics</span>

                    <!-- Bootstrap button group (radio toggle) -->
                    <div class="" role="group" aria-label="Statistics Filter">
                        <input type="radio" class="btn-check" name="statistics4" id="year" value="year"
                            autocomplete="off" checked>
                        <label class="btn btn-outline--primary" for="year">This Year</label>

                        <input type="radio" class="btn-check" name="statistics4" id="month" value="month"
                            autocomplete="off">
                        <label class="btn btn-outline--primary" for="month">This Month</label>

                        <input type="radio" class="btn-check" name="statistics4" id="week" value="week"
                            autocomplete="off">
                        <label class="btn btn-outline--primary" for="week">This Week</label>
                    </div>
                </div>

                <div class="card-body">
                    <div id="dashboard-chart"></div>
                </div>
            </div>

        </div>

        <!-- Activity Feed -->
        <div class="col-12 col-lg-4 ">
            <div class="card shadow rounded-3 my-3 p-4 border border-2 border-light h-100 w-100">
                <h4 class="mb-3">Activity Feed</h4>

                <div class="row g-3">

                    @foreach($activities as $activity)
                    <div class="col-12 border border-2 border-light rounded shadow-sm">
                        <div class="d-flex align-items-center p-2 justify-content-between">

                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/front-end/img/activity-feed-icon1.png') }}"
                                    class="me-2" width="26" height="26">

                                <p class="mb-0 fw-semibold">
                                    {{ $activity->description }}
                                </p>
                            </div>

                            <!-- 🔥 ONLY TIME -->
                            <small class="text-muted">
                                {{ $activity->created_at->format('h:i A') }}
                            </small>

                        </div>
                    </div>
                    @endforeach
                </div>
                @if(count($activities)==0)
                @include('layouts.back-end._empty-state',['text'=>'No_data_found'],['image'=>'default'])
                @endif


            </div>
        </div>

    </div>
    <div class="card">
        <div class="card-header align-items-center">
            <div class="row w-0">
                <div class="col-md-12">
                    <form id="expertFilterForm" action="{{ url()->current() }}" method="GET">
                        <div class="row">
                            <div class="col-md-4 mb-2 mb-lg-0">
                                <select class="form-control js-select2-custom"
                                    name="category"
                                    onchange="this.form.submit()">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Status --}}
                            <div class="col-md-4 mb-2 mb-lg-0">
                                <select class="form-control js-select2-custom"
                                    name="status"
                                    onchange="this.form.submit()">
                                    <option value="">All</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Online</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Offline</option>
                                </select>
                            </div>

                            {{-- Search --}}
                            <div class="col-md-4 mb-2 mb-lg-0">
                                <div class="input-group input-group-merge input-group-custom">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="tio-search"></i>
                                        </div>
                                    </div>

                                    <input type="search"
                                        name="searchValue"
                                        class="form-control"
                                        placeholder="{{ translate('search_by_Name_or_Email_or_Phone') }}"
                                        value="{{ request('searchValue') }}">

                                    <button type="submit" class="btn btn--primary">
                                        {{ translate('search') }}
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

        </div>
        <div class="table-responsive">
            <table style="text-align: {{Session::get('direction') === " rtl" ? 'right' : 'left' }};"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light text-capitalize">
                    <tr>
                        <th scope="col">SL</th>
                        <th scope="col">Expert Name</th>
                        <th scope="col">Category</th>
                        <th scope="col">Status</th>
                        <th scope="col">Rating</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($experts as $idx => $expert)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $expert->f_name. ' ' .$expert->l_name }}</td>
                        <td>{{ $expert->category?->name ?? 'General' }}</td>
                        <td>{{ $expert->is_online ? 'Online' : 'Offline' }}</td>
                        <td><i class="fa-solid fa-star text--primary"></i> {{ $expert->average_rating }}</td>
                        <td class="">
                            <a title="{{translate('view')}}"
                                class="btn btn-outline--primary btn-sm square-btn"
                                href="{{route('admin.expert.view',[$expert['id']])}}">
                                <i class="tio-invisible"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(count($experts)==0)
        @include('layouts.back-end._empty-state',['text'=>'No_data_found'],['image'=>'default'])
        @endif

    </div>
</div>
@endsection


@push('script_2')
<script>
    // ApexCharts line chart options
    var lineChartOptions = {
        chart: {
            type: 'line',
            height: 250,
            toolbar: {
                show: false
            }
        },
        series: [{
            name: 'Revenue',
            data: []
        }],
        colors: ['#900000'],
        stroke: {
            curve: 'smooth',
            width: 3
        },
        markers: {
            size: 4,
            colors: ['#ffffff'],
            strokeColors: '#900000',
            strokeWidth: 2,
            hover: {
                size: 7
            }
        },
        tooltip: {
            enabled: true,
            y: {
                formatter: function(val) {
                    return '$ ' + Number(val).toFixed(2);
                }
            }
        },
        xaxis: {
            categories: []
        },
        yaxis: {
            min: 0,
            tickAmount: 5,
            labels: {
                formatter: function(val) {
                    return '$ ' + val;
                }
            }
        }
    };

    // Render chart
    var lineChart = new ApexCharts(
        document.querySelector("#dashboard-chart"),
        lineChartOptions
    );
    lineChart.render();

    // Map backend categories to labels
    function mapCategories(dates, filter) {
        if (filter === 'week') {
            return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        } else if (filter === 'month') {
            return ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        } else {
            return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ];
        }
    }

    // Fetch and update chart data
    function updateChartData(filter) {
        $.ajax({
            url: '/admin/dashboard/graph-data',
            type: 'GET',
            data: {
                filter: filter
            },
            success: function(res) {
                let categories = mapCategories(res.categories, filter);

                lineChart.updateOptions({
                    series: [{
                        name: 'Revenue',
                        data: res.data
                    }],
                    xaxis: {
                        categories: categories
                    }
                });
            },
            error: function() {
                console.error('Failed to load graph data');
            }
        });
    }

    // Filter buttons (week / month / year)
    document.querySelectorAll('input[name="statistics4"]').forEach((input) => {
        input.addEventListener('change', function() {
            updateChartData(this.value);
            updateActiveButton(this.value);
        });
    });

    // Highlight active filter button
    function updateActiveButton(activeValue) {
        document.querySelectorAll('.btn-check').forEach((input) => {
            input.checked = (input.value === activeValue);
        });
    }

    // Initial load (Year)
    updateChartData('year');
    updateActiveButton('year');
</script>

@endpush