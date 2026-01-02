@extends('layouts.back-end.app')

@section('title', translate('Backup_And_Restore'))

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
                        {{translate('Backup & Restore')}}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="py-2 mt-3">
        <div class="row ">
            <div class="col-md-3 col-sm-6 ">
                <div class="card shadow-sm border-light rounded-4 p-5">
                    <p class="fs-5 mb-0">Total Backups</p>
                    <p class="fs-5 text-dark mb-0">{{ $totalBackups }}</p>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="col-md-3 col-sm-6 ">
                <div class="card shadow-sm border-light rounded-4 p-5">
                    <p class="fs-5 mb-0">Last Backup Date</p>
                    <p class="fs-5 text-dark mb-0">{{ $lastBackup }}</p>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="col-md-3 col-sm-6 ">
                <div class="card shadow-sm border-light rounded-4  p-5">
                    <p class="fs-5 mb-0">Auto Backup Status</p>
                    <p class="fs-5 text-dark mb-0">Enabled</p>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="col-md-3 col-sm-6 ">
                <div class="card shadow-sm border-light rounded-4 p-5">
                    <p class="fs-5 mb-0">Last Restore Performed</p>
                    <p class="fs-5 text-dark mb-0">—</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card p-4 mb-2">
        <h5 class="fs-16 ">Backup Controls Panel</h5>
    </div>
    <div class="card mb-2">
        <div class="card-body shadow-sm border border-2 border-light rounded-4 h-100">
            <div class="p-4 h-100">
                <form id="backupForm" action="{{ route('admin.backup.run') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Schedule Auto Backup</label>
                            <select class="form-select form-control select2-enable" name="schedule">
                                <option value="daily" selected>Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Time</label>
                            <input type="time" name="time" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Backup Type</label>
                            {{-- important: the values are used by controller --}}
                            <select class="form-select form-control select2-enable" name="type" id="backupTypeSelect">
                                <option value="full" selected>Full Backup</option>
                                <option value="db">Database Only</option>
                                <option value="files">Media Files</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Enable Encryption</label>
                            <select class="form-select form-control select2-enable" name="encryption" id="encryptionSelect">
                                <option value="disable" selected>Disabled</option>
                                <option value="enable">Enable</option>
                            </select>
                        </div>

                        <div class="col-md-12 d-flex justify-content-end align-items-end gap-2">
                            <button type="reset" class="btn btn-secondary me-3 px-5">Reset</button>

                            {{-- Post will include type & encryption now --}}
                            <button type="submit" class="btn btn--primary px-5">Backup Now</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
    <div class="card p-4 mb-2">
        <h5 class="fs-16 ">Backup History Table</h5>
    </div>

    <div class="row mt-3">
        <div class="col-lg-12 col-xl-12">
            <div class="card">
                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>SL</th>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Stored in</th>
                                <th>Encrypted</th>
                                <th>Sttaus</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>


                            @foreach($backups as $backup)
                            <tr>
                                <td>{{ $backup['id'] }}</td>
                                <td>{{ $backup['date'] }}</td>
                                <td>Full</td>
                                <td>{{ $backup['size'] }}</td>
                                <td>Local</td>
                                <td>No</td>
                                <td class="text-success">Success</td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-sm"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.backup.download',$backup['file']) }}">
                                                    <i class="fa-solid fa-download me-2"></i> Download
                                                </a>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.backup.delete',$backup['file']) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fa-solid fa-trash me-2"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>

                            </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>


                @if(count($backups)==0)
                @include('layouts.back-end._empty-state',['text'=>'No_Data_found'],['image'=>'default'])
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(el => {
            new bootstrap.Dropdown(el);
        });
    });
</script>

@endpush