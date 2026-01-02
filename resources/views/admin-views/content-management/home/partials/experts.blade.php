@php 
    $items = app('App\Http\Controllers\Admin\Cms\HomeController')->getSectionData('experts'); 
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Our Experts</h6>
        <a href="{{ route('admin.home-cms.add', 'experts') }}" class="btn btn-success btn-sm">
            <i class="tio-add"></i> Add New
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 80px;">Image</th>
                        <th>Name</th>
                        <th>Title</th>
                        <th style="width: 120px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $i => $e)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                @if(!empty($e['image']))
                                    <img src="{{ asset($e['image']) }}" width="60" height="60" class="rounded-circle border">
                                @else
                                    <span class="text-muted">No Image</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $e['name'] ?? '—' }}</td>
                            <td>{{ $e['title'] ?? '—' }}</td>
                            <td class="text-center">
                                  <button onclick="openModal('experts', {{ $i }})" 
                                        class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="tio-edit"></i>
                                </button>

                                <form method="POST" 
                                      action="{{ route('admin.home-cms.destroy', ['experts', $i + 1]) }}" 
                                      style="display:inline">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this expert?')">
                                        <i class="tio-delete"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                No experts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
