@php 
    $buttons = app('App\Http\Controllers\Admin\Cms\HomeController')->getSectionData('quick_buttons'); 
@endphp

<div class="card shadow-sm border-0">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Quick Question Buttons</h6>
        <a href="{{ route('admin.home-cms.add', 'quick_buttons') }}" class="btn btn-success btn-sm">
            <i class="tio-add"></i> Add New
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">SL</th>
                        <th>Button Text</th>
                        <th>Link</th>
                        <th style="width: 120px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($buttons as $i => $btn)
                        <tr>
                            <td>{{ $i}}</td>
                            <td class="fw-semibold">{{ $btn['text'] ?? '—' }}</td>
                            <td>
                                @if(!empty($btn['link']))
                                    <a href="{{ $btn['link'] }}" target="_blank" class="text-primary text-decoration-underline">
                                        {{ $btn['link'] }}
                                    </a>
                                @else
                                    <span class="text-muted">No Link</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button onclick="openModal('quick_buttons', {{ $i }})" 
                                        class="btn btn-sm btn-primary me-1" title="Edit">
                                    <i class="tio-edit"></i>
                                </button>

                                <form method="POST" 
                                      action="{{ route('admin.home-cms.destroy', ['quick_buttons', $i]) }}" 
                                      style="display:inline">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this button?')">
                                        <i class="tio-delete"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                No quick buttons found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
