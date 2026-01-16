@extends('layouts.back-end.app')

@section('title', translate('Dispatch Settings'))

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
                        {{translate('Dispatch Settings')}}
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <form action="{{ route('admin.business-settings.dispatch-update') }}" method="POST">
        @csrf

        <div class="card shadow-sm border border-2 border-light rounded-4 h-100 p-4">

            <h4 class="fs-6 fw-bold my-3 text--primary">
                <img src="{{dynamicAsset('public/assets/back-end/img/dispatch-icon-1.png')}}" alt="" class="me-2">
                Dispatch Mode Control Panel
            </h4>

            <div class="row g-4">

                <!-- Dispatch Mode (Select: Auto/Manual) -->
                <div class="col-md-4 d-flex">
                    <div class="card-section w-100">
                        <div class="">
                            <label class="mb-0">Dispatch Mode</label>
                            <select name="dispatch_mode" class="form-select form-control">
                                <option value="auto" {{ $dispatchSettings['dispatch_mode'] == 'auto' ? 'selected' : '' }}>Auto</option>
                                <option value="manual" {{ $dispatchSettings['dispatch_mode'] == 'manual' ? 'selected' : '' }}>Manual</option>
                            </select>
                        </div>
                        <div class="border border-2 border-light rounded-3 px-3 py-2 mt-2">
                            <small class="text-muted">Auto assigns expert OR lets admin assign manually</small>
                        </div>
                    </div>
                </div>

                <!-- AI Assist -->
                <div class="col-md-4 d-flex">
                    <div class="card-section w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="mb-0">AI Assist</label>
                            <div class="form-check form-switch">
                                <label class="switcher mx-auto">
                                    <input name="ai_assist" class="switcher_input" type="checkbox" value="1"
                                        {{ $dispatchSettings['ai_assist'] ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                        <div class="border border-2 border-light rounded-3 px-3 py-2 mt-2">
                            <small class="text-muted">Use AI to suggest top 3 experts (even in Manual Mode)</small>
                        </div>
                    </div>
                </div>

                <!-- Fallback to Manual -->
                <div class="col-md-4 d-flex">
                    <div class="card-section w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="mb-0">Fallback to Manual</label>
                            <div class="form-check form-switch">
                                <label class="switcher mx-auto">
                                    <input name="fallback_manual" class="switcher_input" type="checkbox" value="1"
                                        {{ $dispatchSettings['fallback_manual'] ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <input name="fallback_manual_time" type="number" class="form-control w-100px"
                                value="{{ $dispatchSettings['fallback_manual_time'] }}" min="1">
                            <small class="text-muted">If no expert accepts in X mins, move to manual</small>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Auto Assignment Rules -->
            <h4 class="fs-6 fw-bold my-5 text--primary">
                <img src="{{dynamicAsset('public/assets/back-end/img/dispatch-icon-2.png')}}" alt="" class="me-2">
                Auto Assignment Rules
            </h4>

            <div class="row g-4">

                <!-- Match By Category -->
                <div class="col-md-4 d-flex">
                    <div class="card-section w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="mb-0">Match By Category</label>
                            <div class="form-check form-switch">
                                <label class="switcher mx-auto">
                                    <input name="match_category" class="switcher_input" type="checkbox" value="1"
                                        {{ $dispatchSettings['match_category'] ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Assign based on expertise category (e.g., Legal, Medical)</small>
                    </div>
                </div>

                <!-- Match By Language -->
                <div class="col-md-4 d-flex">
                    <div class="card-section w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="mb-0">Match By Language</label>
                            <div class="form-check form-switch">
                                <label class="switcher mx-auto">
                                    <input name="match_language" class="switcher_input" type="checkbox" value="1"
                                        {{ $dispatchSettings['match_language'] ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Prefer the expert with matching language</small>
                    </div>
                </div>

                <!-- Prioritize Ratings -->
                <div class="col-md-4 d-flex">
                    <div class="card-section w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="mb-0">Prioritize Ratings</label>
                            <div class="form-check form-switch">
                                <label class="switcher mx-auto">
                                    <input name="prioritize_ratings" class="switcher_input" type="checkbox" value="1"
                                        {{ $dispatchSettings['prioritize_ratings'] ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">High rated experts get preference</small>
                    </div>
                </div>

                <!-- Avoid Pending Payouts -->
                <div class="col-md-4 d-flex">
                    <div class="card-section w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="mb-0">Avoid If Expert Has Pending Payouts</label>
                            <div class="form-check form-switch">
                                <label class="switcher mx-auto">
                                    <input name="avoid_pending_payouts" class="switcher_input" type="checkbox" value="1"
                                        {{ $dispatchSettings['avoid_pending_payouts'] ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Skip experts with payment issues</small>
                    </div>
                </div>

            </div>

            <!-- Manual Dispatch Settings -->
            <h4 class="fs-6 fw-bold mt-5 text--primary my-4">
                <img src="{{dynamicAsset('public/assets/back-end/img/dispatch-icon-2.png')}}" alt="" class="me-2">
                Manual Dispatch Settings
            </h4>

            <div class="row g-4">

                <!-- Admin Notification -->
                <div class="col-md-4 d-flex">
                    <div class="card-section w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="mb-0">Admin Notification</label>
                            <div class="form-check form-switch">
                                <label class="switcher mx-auto">
                                    <input name="admin_notification" class="switcher_input" type="checkbox" value="1"
                                        {{ $dispatchSettings['admin_notification'] ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Alert admin when manual dispatch is required.</small>
                    </div>
                </div>

                <!-- Max Pending Manual Assignments -->
                <div class="col-md-4 d-flex">
                    <div class="card-section w-100">
                        <div class="">
                            <label class="mb-0">Max Pending Manual Assignments</label>
                            <div>
                                <input name="max_pending_assignments" type="number" class="form-control w-100px"
                                    value="{{ $dispatchSettings['max_pending_assignments'] }}" min="1">
                                <small class="text-muted">e.g., 5 (limit number of questions waiting for manual assignment)</small>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Save Buttons -->
            <div class="d-flex justify-content-end gap-3 mt-5">
                <button type="reset" class="btn btn-secondary px-5">Reset</button>
                <button type="submit" class="btn btn--primary px-5">Save Changes</button>
            </div>

        </div>
    </form>
    <div class="card p-4 my-3">
        <h5 class="fs-16 ">Dispatch Logs Table</h5>
    </div>
    <div class="row ">
        <div class="col-lg-12 col-xl-12">
            <div class="card">
                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>SL</th>
                                <th>Questions iD</th>
                                <th>User</th>
                                <th>Dispatch Mode</th>
                                <th>Assigned To</th>
                                <th>Time</th>
                                <th>Staus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dispatchLogs as $key => $log)
                            <tr>
                                <td>
                                    {{ $dispatchLogs->firstItem() + $key }}
                                </td>

                                <td>
                                    #{{ $log->question_id }}
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $log->user?->f_name }} {{ $log->user?->l_name }}</strong><br>
                                        <small class="text-muted">{{ $log->user?->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-soft-info">
                                        {{ ucfirst($log->dispatch_mode) }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->chatSession?->expert)
                                    <strong>
                                        {{ $log->chatSession->expert->f_name }}
                                        {{ $log->chatSession->expert->l_name }}
                                    </strong>
                                    @else
                                    <span class="badge badge-soft-warning">
                                        Not Assigned
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    {{ $log->created_at->format('d M Y') }}<br>
                                    <small class="text-muted">
                                        {{ $log->created_at->format('h:i A') }}
                                    </small>
                                </td>
                                <td>

                                    @if($log->chatSession?->ended_at)
                                    <span class="badge badge-soft-danger">Ended</span>
                                    @elseif($log->chatSession?->expert_id)
                                    <span class="badge badge-soft-success">Active</span>
                                    @else
                                    <span class="badge badge-soft-warning">Waiting</span>
                                    @endif
                                </td>
                            </tr>

                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive mt-4">
                    <div class="px-4 d-flex justify-content-lg-end">
                        {!! $dispatchLogs->links() !!}
                    </div>
                </div>
                @if(count($dispatchLogs)==0)
                @include('layouts.back-end._empty-state',['text'=>'No_Logs_found'],['image'=>'default'])
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')

@endpush