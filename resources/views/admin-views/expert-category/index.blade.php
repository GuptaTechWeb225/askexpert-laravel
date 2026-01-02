@extends('layouts.back-end.app')
@section('title', translate('Expert Categories'))
@push('css_or_js')
<link href="{{ dynamicAsset(path: 'public/assets/back-end/css/tags-input.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}" rel="stylesheet">
<link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset('public/assets/back-end/img/expert-category.png') }}" alt="">
            {{ translate('Categories & Pricing') }}
        </h2>

    </div>
    <div class="row">
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('total_categories')}}</h5>
                <div class="mt-2 d-flex justify-content-between align-items-center w-100">
                    <h2 class="business-analytics__title">{{ $totalCategories->count() }}</h2>
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/categories/categories-pricing-icon-1.png')}}" width="40" height="40" class="" alt="">
                </div>

            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('Custom Pricing Enabled')}}</h5>
                <div class="mt-2 d-flex justify-content-between align-items-center w-100">
                    <h2 class="business-analytics__title">{{ $categories->count() }}</h2>
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/categories/customer-icon-1.png')}}" width="40" height="40" class="" alt="">
                </div>

            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('Default Pricing Applied')}}</h5>
                <div class="mt-2 d-flex justify-content-between align-items-center w-100">
                    <h2 class="business-analytics__title">{{ $categories->count() }}</h2>
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/categories/categories-pricing-icon-3.png')}}" width="40" height="40" class="" alt="">
                </div>
            </a>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-4">
            <a class="business-analytics card">
                <h5 class="business-analytics__subtitle">{{translate('Inactive Categories')}}</h5>
                <div class="mt-2 d-flex justify-content-between align-items-center w-100">
                    <h2 class="business-analytics__title">{{ $inactiveCategories->count() }}</h2>
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/categories/categories-pricing-icon-4.png')}}" width="40" height="40" class="" alt="">
                </div>

            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header gap-3 align-items-center">
            <h5 class="mb-0 mr-auto">
                {{translate('Categories')}}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $categories->total() }}</span>
            </h5>

            <form id="filterForm" action="{{ url()->current() }}" method="GET">
                <div class="d-flex gap-4">
                    <div>
                        <select name="filter_category" id="filter_category" class="js-select2-custom form-control">
                            <option value="">{{ translate('All Categories') }}</option>
                            @foreach($allCategories as $cat)
                            <option value="{{ $cat->id }}" {{ request('filter_category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group input-group-merge input-group-custom">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="tio-search"></i>
                            </div>
                        </div>
                        <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                            placeholder="{{ translate('search_by_Name')}}" aria-label="Search category" value="{{ request('searchValue') }}">
                        <button type="submit" class="btn btn--primary">{{ translate('search')}}</button>
                    </div>
                </div>

            </form>

            <div class="dropdown">
                <a type="button" class="btn btn-outline--primary text-nowrap" href="{{route('admin.expert-category.create')}}">
                    <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/add-btn.png')}}" alt="" class="category">
                    <span class="ps-2">{{ translate('add_new') }}</span>
                </a>
            </div>
        </div>
        <div class="table-responsive datatable-custom">
            <table
                style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                <thead class="thead-light thead-50 text-capitalize">
                    <tr>
                        <th>{{ translate('SL') }}</th>
                        <th>{{ translate('category_Name') }}</th>
                        <th>{{ translate('joining_Fee') }}</th>
                        <th>{{ translate('monthly_Fee') }}</th>
                        <th>{{ translate('expert_Fee') }}</th>
                        <th>{{ translate('Active_Experts') }}</th>
                        <th>{{ translate('status') }}</th>
                        <th>{{ translate('action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $key => $category)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $category->name }}</td>
                        <td>${{ $category->joining_fee }}</td>
                        <td>${{ $category->monthly_subscription_fee }}</td>
                        <td>${{ $category->expert_fee }} / per question</td>
                        <td>{{ $category->expertsCount() }}</td>
                        <td>
                            <label class="switcher">
                                <input type="checkbox" class="switcher_input status-toggle" {{ $category->is_active ? 'checked' : '' }} data-id="{{ $category->id }}">
                                <span class="switcher_control"></span>
                            </label>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.expert-category.edit', $category->id) }}" class="btn btn-outline--primary btn-sm square-btn">
                                    <i class="tio-edit"></i>
                                </a>

                                <button type="button" class="btn btn-outline-danger btn-sm square-btn delete-btn" data-id="{{ $category->id }}">
                                    <i class="tio-delete"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-responsive mt-4">
            <div class="px-4 d-flex justify-content-lg-end">
                {!! $categories->links() !!}
            </div>
        </div>
        @if(count($categories)==0)
        @include('layouts.back-end._empty-state',['text'=>'No_Data_Found'],['image'=>'default'])
        @endif
    </div>
</div>
@endsection


@push('script')

<script>
    $(document).ready(function() {
        // 🔹 Initialize select2
        $('#filter_category').select2({
            placeholder: "{{ translate('Select Category') }}",
            allowClear: true
        });

        $('#filter_category').on('change', function() {
            $('#filterForm').submit();
        });

        $(document).on('change', '.status-toggle', function() {
            let checkbox = $(this);
            let id = checkbox.data('id');
            let newStatus = checkbox.is(':checked') ? 'activate' : 'deactivate';

            Swal.fire({
                title: `Are you sure you want to ${newStatus} this category?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#5d0000',
                cancelButtonColor: '#d33',
                confirmButtonText: `Yes, ${newStatus} it!`
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(`{{ url('admin/expert-category/toggle') }}/${id}`, {
                        _token: '{{ csrf_token() }}'
                    }, function(res) {
                        toastr[res.success ? 'success' : 'error'](res.message);
                    });
                } else {
                    checkbox.prop('checked', !checkbox.is(':checked'));
                }
            });
        });

        // 🔹 SweetAlert Delete
        $(document).on('click', '.delete-btn', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#5d0000',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('admin/expert-category') }}/${id}`,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            toastr[res.success ? 'success' : 'error'](res.message);
                            if (res.success) location.reload();
                        }
                    });
                }
            });
        });
    });
</script>
@endpush