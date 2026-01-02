@php 
    $cats = app('App\Http\Controllers\Admin\Cms\HomeController')->getSectionData('expert_categories'); 
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-semibold mb-0"></h5>
    <a href="{{ route('admin.home-cms.add', 'expert_categories') }}" class="btn btn-primary btn-sm px-3">
        <i class="tio-add-circle me-1"></i> Add New
    </a>
</div>

<div class="table-responsive shadow-sm rounded">
    <table class="table align-middle table-hover mb-0">
        <thead class="table-light border-bottom">
            <tr class="text-nowrap">
                <th scope="col" width="5%">SL</th>
                <th scope="col" width="10%">Image</th>
                <th scope="col">Category Name</th>
                <th scope="col" width="15%">Experts Count</th>
                <th scope="col" width="20%">Link</th>
                <th scope="col" width="10%" class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cats as $i => $cat)
                <tr>
                    <td>{{ $i }}</td>
                    <td>
                        <img src="{{ asset($cat['image'] ?? 'dist/assets/img/default.png') }}" 
                             alt="Category Icon" width="50" height="50" 
                             class="rounded border object-fit-cover">
                    </td>
                    <td class="fw-semibold">{{ $cat['name'] ?? 'N/A' }}</td>
                    <td>{{ $cat['expert_count'] ?? 0 }}</td>
                    <td>
                        <a href="{{ $cat['link'] ?? '#' }}" target="_blank" class="text-decoration-none text-primary">
                            {{ $cat['link'] ?? 'N/A' }}
                        </a>
                    </td>
                   
                    <td class="text-center">
                        <button onclick="openModal('expert_categories', {{ $i }})" 
                                class="btn btn-outline-primary btn-sm rounded-circle me-1" 
                                title="Edit">
                            <i class="tio-edit"></i>
                        </button>
                        <form method="POST" 
                              action="{{ route('admin.home-cms.destroy', ['expert_categories', $i]) }}" 
                              class="d-inline m-0 p-0">
                            @csrf 
                            @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm rounded-circle" title="Delete">
                                <i class="tio-delete"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No categories found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
.table td, .table th {
    vertical-align: middle !important;
}
.table img {
    object-fit: cover;
}
</style>
