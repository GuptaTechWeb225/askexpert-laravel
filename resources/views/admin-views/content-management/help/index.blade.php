{{-- resources/views/admin-views/content-management/help/index.blade.php --}}
@extends('layouts.back-end.app')
@section('title', 'Help / FAQ CMS')


@section('content')
<div class="content container-fluid">
    <div class="inline-page-menu my-4">
        <ul class="list-unstyled d-inline-flex gap-2 mb-2">
            @foreach($typeList as $key => $label)
            <li class="{{ $currentType == $key ? 'active' : 'text-dark' }}">
                <a href="{{ route('admin.content-management.help', ['section' => $key]) }}"
                    class="nav-link {{ $currentType == $key ? 'active' : 'text-dark' }}">
                    {{ $label }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5>{{ $typeList[$currentType] }}</h5>
        </div>
        <div class="card-body">
            @include("admin-views.content-management.help.partials.{$currentType}")
        </div>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf @method('POST')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Edit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="modalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script src="{{ asset('assets/back-end/js/sweet_alert.js') }}"></script>
<script>
    const modal = new bootstrap.Modal(document.getElementById('editModal'));

    window.openModal = function(section, itemId = 0, isAdd = false) {
        $('#modalTitle').text(isAdd ? 'Add New' : 'Edit');
        $('#editForm').attr('action', `{{ url('admin/help-cms') }}/${section}/${itemId}`);
        $.get(`{{ url('admin/help-cms/edit-data') }}/${section}/${itemId}`, function(html) {
            $('#modalBody').html(html);
            modal.show();
        });
    };

    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: () => {
                toastr.success('Saved!');
                modal.hide();
                location.reload();
            },
            error: () => toastr.error('Failed')
        });
    });
  window.deleteItem = function(section, itemId) {

    Swal.fire({
        title: 'Delete?',
        text: 'This action cannot be undone!',
        type: 'warning',              
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.value) {            
            let url = `{{ url('admin/help-cms/destroy') }}/${section}/${itemId}`;

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    _method: 'DELETE'
                },
                success: function() {
                    toastr.success('Deleted!');
                    location.reload();
                },
                error: function(xhr) {
                    toastr.error('Delete failed');
                }
            });
        } else {
        }
    });
};
</script>
@endpush