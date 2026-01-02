@php 
    $items = app('App\Http\Controllers\Admin\Cms\HomeController')->getSectionData('testimonials'); 
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Testimonials</h6>
        <a href="{{ route('admin.home-cms.add', 'testimonials') }}" class="btn btn-success btn-sm">
            <i class="tio-add"></i> Add New
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Author</th>
                        <th style="width: 120px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $i => $t)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $t['title'] ?? '—' }}</td>
                            <td>"{{ Str::limit($t['description'] ?? '', 100) }}"</td>
                            <td><em>{{ $t['name'] ?? 'Anonymous' }}</em></td>
                            <td class="text-center">
                                 <button onclick="openModal('testimonials', {{ $i }})" 
                                        class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="tio-edit"></i>
                                </button>

                                <form method="POST" 
                                      action="{{ route('admin.home-cms.destroy', ['testimonials', $i]) }}" 
                                      style="display:inline">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this testimonial?')">
                                        <i class="tio-delete"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                No testimonials found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
