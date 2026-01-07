@extends('layouts.back-end.app')
@section('title', 'About Us CMS')

@section('content')
<div class="content container-fluid">
    <div class="inline-page-menu my-4">
        <ul class="list-unstyled d-inline-flex gap-2 mb-2">
            @foreach($typeList as $key => $label)
            <li class="{{ $currentType == $key ? 'active' : 'text-dark' }}"><a href="{{ route('admin.content-management.about', ['section' => $key]) }}"
                   class="nav-link ">{{ $label }}</a></li>
            @endforeach
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5>{{ $typeList[$currentType] }}</h5>
           
        </div>
        <div class="card-body">
            @include("admin-views.content-management.about.partials.{$currentType}")
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf @method('POST')
            <div class="modal-content">
                <div class="modal-header text-white">
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
<script>
    const modal = new bootstrap.Modal(document.getElementById('editModal'));

    window.openModal = function(section, itemId = 0, isAdd = false) {
        $('#modalTitle').text(isAdd ? 'Add New' : 'Edit');
        $('#editForm').attr('action', `{{ url('admin/about-cms') }}/${section}/${itemId}`);
        $.get(`{{ url('admin/about-cms/edit-data') }}/${section}/${itemId}`, function(html) {
            $('#modalBody').html(html);
            modal.show();
        });
    };

    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        $.ajax({
            url: $(form).attr('action'),
            method: 'POST',
            data: new FormData(form),
            processData: false,
            contentType: false,
            success: () => { toastr.success('Saved!'); modal.hide(); location.reload(); },
            error: () => toastr.error('Failed')
        });
    });

    $(document).on('change', '.section-toggle', function() {
        $.post("{{ route('admin.about-cms.toggle-status') }}", {
            _token: "{{ csrf_token() }}",
            type: $(this).data('type'),
            status: $(this).is(':checked') ? 1 : 0
        });
    });
</script>
@endpush