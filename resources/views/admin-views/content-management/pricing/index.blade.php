{{-- resources/views/admin-views/content-management/pricing/index.blade.php --}}
@extends('layouts.back-end.app')
@section('title', 'Pricing CMS')

@section('content')
<div class="content container-fluid">
    <div class="inline-page-menu my-4">
        <ul class="list-unstyled d-inline-flex gap-2 mb-2">
            @foreach($typeList as $key => $label)
            <li class="{{ $currentType == $key ? 'active' : 'text-dark' }}"><a href="{{ route('admin.pricing-cms.index', ['section' => $key]) }}"
                    class="nav-link ">{{ $label }}</a></li>
            @endforeach
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5>{{ $typeList[$currentType] }}</h5>
        </div>
        <div class="card-body">
            @include("admin-views.content-management.pricing.partials.{$currentType}")
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div class="modal-content">
                <div class="modal-header text-white">
                    <h5 class="modal-title" id="modalTitle">Edit</h5>
                    <button type="button" class="btn-close btn" data-bs-dismiss="modal">X</button>
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
<script>
    const modal = new bootstrap.Modal(document.getElementById('editModal'));

    window.openModal = function(section, itemId = 0, isAdd = false) {
        $('#modalTitle').text(isAdd ? 'Add New' : 'Edit');
        $('#editForm').attr('action', `{{ url('admin/pricing-cms') }}/${section}/${itemId}`);
        $.get(`{{ url('admin/pricing-cms/edit-data') }}/${section}/${itemId}`, function(html) {
            $('#modalBody').html(html);
            modal.show();
        });
    };

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
@endpush