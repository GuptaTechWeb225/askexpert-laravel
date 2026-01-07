@extends('layouts.back-end.app')
@section('title', translate('home_page'))

@section('content')
<section>
    <div class="content container-fluid">
        <!-- Tabs -->
        <div class="inline-page-menu my-4">
            <ul class="list-unstyled">
                @foreach($typeList as $key => $label)
                <li class="{{ $currentType == $key ? 'active' : 'text-dark' }}">
                    <a href="{{ route('admin.content-management.home', ['section' => $key]) }}"
                        class="nav-link {{ $currentType == $key ? 'active' : 'text-dark' }}">
                        {{ $label }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-capitalize">{{ $typeList[$currentType] ?? $currentType }}</h5>

            </div>

            <div class="card-body p-3">
                @include("admin-views.content-management.home.partials.{$currentType}")
            </div>
        </div>
    </div>
</section>

<!-- MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
            </div>
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf @method('POST')
                <div class="modal-body" id="modalBody">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="{{ asset('assets/back-end/js/sweet_alert.js') }}"></script>
<script>
    const modal = new bootstrap.Modal(document.getElementById('editModal'));

    // Open Modal
    window.openModal = function(section, itemId = 0, isAdd = false) {
        const title = isAdd ? 'Add New ' + section.replace(/_/g, ' ') : 'Edit Item';
        $('#modalTitle').text(title);
        $('#editForm').attr('action', `{{ url('admin/home-cms') }}/${section}/${itemId}`);

        $.get(`{{ url('admin/home-cms/edit-data') }}/${section}/${itemId}`, function(html) {
            $('#modalBody').html(html);
            modal.show();
        });
    };

    // Submit Form
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                toastr.success('Saved!');
                modal.hide();
                location.reload(); // Refresh list
            },
            error: function() {
                toastr.error('Failed');
            }
        });
    });
</script>

<script>
    @if(session('open_modal'))
        document.addEventListener('DOMContentLoaded', function() {
            openModal(
                '{{ session('open_modal')['section'] }}',
                {{ session('open_modal')['item_id'] ?? 0 }},
                true
            );
        });
    @endif
</script>
@endpush