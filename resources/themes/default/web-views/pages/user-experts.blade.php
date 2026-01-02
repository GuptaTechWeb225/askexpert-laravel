@extends('layouts.front-end.app')

@section('title', translate('my_Experts'))

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
</style>
@endpush
@php
$buttons = app('App\Http\Controllers\Admin\Cms\HomeController')::getSectionDataStatic('quick_buttons');
$hero = app('App\Http\Controllers\Admin\Cms\AfterLoginCmsController')::getSectionDataStatic('my_experts')[2] ?? [];
@endphp
@section('content')
<section class="hero-section">
    <img src="{{ asset($hero['bg_image'] ?? 'assets/img/home-hero.png') }}" class="bg-img">
    <div class="overlay"></div>
    <div class="hero-content ">
        <div>
            <div>
                <h2 class="text-white mb-3">{!! $hero['heading'] ?? 'Welcome back' !!},<h2 class="text-white mb-3">{!! $hero['paragraph'] ?? 'Ready to get expert advice today? ' !!}</h2>
                </h2>
            </div>
            <div class="mb-4 d-flex flex-wrap gap-2 quick-quetions ">
                @foreach($buttons as $btn)
                <a href="{{ $btn['link'] ?? '#' }}" class="btn btn-outline-light btn-sm rounded-4">
                    <i class="bi bi-search"></i> {{ $btn['text'] ?? '' }}
                </a>
                @endforeach
            </div>
            <div class="input-group shadow-lg start-chat start-chat-home">
                <input type="text" id="userQuestion" class="form-control" placeholder="What can we help with Today">
                <button id="startChatBtn" class="btn btn-primary px-4">
                    Start Chat
                </button>
            </div>
        </div>
    </div>
</section>

<section class="container table-section my-3 py-3">

    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="bg-white shadow-sm rounded p-3 mb-3">
                <h2 class="table-heading mb-0">Favourite Experts</h2>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow-sm rounded p-3 mb-3">
                <form id="myExpertsFilterForm" method="GET">
                    <div class="row align-items-center g-3">
                        <div class="col-md-3">
                            <select name="my_category_id" class="form-select select2-category" id="myCategorySelect">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('my_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select name="my_status" class="form-select" id="myStatusSelect">
                                <option value="">All Status</option>
                                <option value="active" {{ request('my_status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="pending" {{ request('my_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="ended" {{ request('my_status') == 'ended' ? 'selected' : '' }}>Ended</option>
                            </select>
                        </div>
                        <div class="col-md-4 ">
                            <button type="submit" class="btn btn--primary me-2">Apply Filter</button>
                            <a href="{{ request()->url() }}" class="btn btn-secondary">Reset</a>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" name="my_search" class="form-control" placeholder="Search by name or email"
                                    value="{{ request('my_search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>


                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="table-responsive shadow-sm rounded">
                <table class="table table-borderless align-middle text-nowrap mb-0">
                    <thead class="border-bottom">
                        <tr>
                            <th>SL</th>
                            <th>Expert Assigned</th>
                            <th>Date Asked</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Favorites</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($chatExperts as $index => $chat)
                        <tr>
                            <td>{{ $chatExperts->firstItem() + $index }}</td>
                            <td>{{ $chat->expert->f_name ?? '-' }} {{ $chat->expert->l_name ?? '' }}</td>
                            <td>{{ $chat->started_at?->format('M d, Y') ?? '-' }}</td>
                            <td>{{ $chat->category->name ?? 'General' }}</td>
                            <td>
                                <span class="badge {{ $chat->status == 'active' ? 'bg-success' : ($chat->status == 'pending' ? 'bg-warning' : 'bg-secondary') }}">
                                    {{ ucfirst($chat->status) }}
                                </span>
                            </td>
                            <td class="text-danger"><i class="fa-solid fa-heart"></i></td>
                            <td>
                                <a href="{{ route('chat.view', $chat->id) }}" class="btn btn-sm view-btn btn-outline-accent" >
                                    <i class="fa-solid fa-message"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No expert conversations found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="custom-pagination-wrapper d-flex justify-content-end mt-4">
                {{ $chatExperts->appends(request()->except('chat_experts_page'))->links() }}
            </div>
        </div>
    </div>
    </section>

<section class="container table-section my-3 py-3">

    <div class="row mt-5">
        <div class="col-lg-12">
            <div class="bg-white shadow-sm rounded p-3 mb-3">
                <h2 class="table-heading mb-0">All Experts</h2>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow-sm rounded p-3 mb-3">
                <form id="allExpertsFilterForm" method="GET">
                    <div class="row align-items-center g-3">
                        <div class="col-md-4">
                            <select name="all_category_id" class="form-select select2-category" id="allCategorySelect">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('all_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn--primary me-2">Apply Filter</button>
                            <a href="{{ request()->url() }}" class="btn btn-secondary">Reset</a>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" name="all_search" class="form-control" placeholder="Search by name or email"
                                    value="{{ request('all_search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i>Search
                                </button>
                            </div>
                        </div>


                    </div>
                </form>
            </div>

            <div class="table-responsive shadow-sm rounded">
                <table class="table table-borderless align-middle text-nowrap mb-0">
                    <thead class="border-bottom">
                        <tr>
                            <th>SL</th>
                            <th>Expert</th>
                            <th>Category</th>
                            <th>Primary Specialty</th>
                            <th>Experience</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allExperts as $index => $expert)
                        <tr>
                            <td>{{ $allExperts->firstItem() + $index }}</td>
                            <td><strong>{{ $expert->f_name }} {{ $expert->l_name }}</strong></td>
                            <td>{{ $expert->category->name ?? 'General' }}</td>
                            <td>{{ $expert->primary_specialty ?? '-' }}</td>
                            <td>{{ $expert->experience ? $expert->experience . ' years' : '-' }}</td>
                            <td><a class="btn btn-sm btn-outline-accent" href="{{ route('category.view', $expert->category_id ?? 1)}}"> <i class="fa-solid fa-message"></i>
                                </a></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No experts found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="custom-pagination-wrapper d-flex justify-content-end mt-4">
                {{ $allExperts->appends(request()->except('all_experts_page'))->links() }}
            </div>
        </div>
    </div>
</section>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-category').select2({
            placeholder: "Select Category",
            allowClear: true
        });

        // Auto submit on category/status change
        $('#myCategorySelect, #myStatusSelect, #allCategorySelect').on('change', function() {
            $(this).closest('form')[0].submit();
        });

        // Enter key in search box also submits
        $('input[name="my_search"], input[name="all_search"]').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $(this).closest('form')[0].submit();
            }
        });
    });
</script>
@endpush