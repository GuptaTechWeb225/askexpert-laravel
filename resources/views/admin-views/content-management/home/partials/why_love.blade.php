@php 
    $items = app('App\Http\Controllers\Admin\Cms\HomeController')->getSectionData('why_love'); 
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Why Love AskExpert</h6>
        <a href="{{ route('admin.home-cms.add', 'why_love') }}" class="btn btn-success btn-sm">
            <i class="tio-add"></i> Add New
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">SL</th>
                        <th style="width: 80px;">Image</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th style="width: 120px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                @if(!empty($item['icon']))
                                    <img src="{{ asset($item['icon']) }}" class="img-thumbnail bg-c1" width="60" height="60" style="object-fit: cover;">
                                @else
                                    <span class="text-muted small">No Image</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $item['title'] ?? '—' }}</td>
                            <td>{{ Str::limit($item['description'] ?? '', 100) }}</td>
                            <td class="text-center">

                                 <button onclick="openModal('why_love', {{ $i }})" 
                                        class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="tio-edit"></i>
                                </button>

                                <form method="POST" 
                                      action="{{ route('admin.home-cms.destroy', ['why_love', $i + 1]) }}" 
                                      style="display:inline">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this item?')">
                                        <i class="tio-delete"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                No records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
