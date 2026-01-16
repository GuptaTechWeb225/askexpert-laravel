@extends('layouts.back-end.app-expert')

@section('title', translate('Communication Modes'))

@section('content')
<div class="content container-fluid">
     <div class="my-4">
            <div class="banner-container d-flex justify-content-between position-relative" style="background-image: url('{{ asset('assets/back-end/img/home-banner-bg-1.jpg') }}');">
                <div class="banner-content">
                    <div class="banner-text">
                        <h3>Welcome Back, {{ auth('expert')->user()->f_name }} {{ auth('expert')->user()->l_name }}</h3>
                        <p>Have a good day.</p>
                    </div>
                </div>

                <div class="position-relative" id="status-container">
                    @if(auth('expert')->user()->is_online)
                    <button class="btn btn-success px-3 toggle-status-btn d-flex align-items-center gap-2" data-status="1">
                        <span class="spinner-border" role="status"></span> Go Offline
                    </button>
                    @else
                    <button class="btn btn--primary px-3 toggle-status-btn " data-status="0">
                        <i class="fa-solid fa- tower-broadcast"></i> Go Live
                    </button>
                    @endif
                    <span class="live-indicator" style="{{ auth('expert')->user()->is_online ? '' : 'display:none;' }}"></span>
                </div>
            </div>
        </div>

    <form action="{{ route('expert.settings.communication.update') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ translate('Mode') }}</th>
                                <th class="text-center">{{ translate('Available') }}</th>
                                <th class="text-center">{{ translate('On Break') }}</th>
                                <th class="text-center">{{ translate('Vacation Mode') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modes as $mode)
                            @php
                            $modeLabel = str_replace('_', ' ', ucwords($mode->mode, '_'));
                            @endphp
                            <tr>
                                <td class="text-capitalize font-weight-bold">{{ translate($modeLabel) }}</td>
                                <td class="text-center">
                                    <label class="switcher mx-auto">

                                        <input type="checkbox" class="switcher_input"
                                            name="modes[{{ $mode->mode }}][available]"
                                            {{ $mode->available ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="switcher mx-auto">

                                        <input type="checkbox" class="switcher_input"
                                            name="modes[{{ $mode->mode }}][on_break]"
                                            {{ $mode->on_break ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="switcher mx-auto">

                                        <input type="checkbox" class="switcher_input"
                                            name="modes[{{ $mode->mode }}][vacation_mode]"
                                            {{ $mode->vacation_mode ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <button type="button" class="btn btn-secondary me-2" onclick="location.reload()">
                        {{ translate('Reset') }}
                    </button>
                    <button type="submit" class="btn btn--primary">
                        {{ translate('Save Changes') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection


@push('script_2')
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/apexcharts.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/dashboard.js')}}"></script>
<script>
    $(document).on('click', '.toggle-status-btn', function() {
        let currentStatus = $(this).data('status'); // 1 means Online, 0 means Offline
        let titleText = currentStatus == 1 ? "Want to go Offline?" : "Want to go Live?";
        let confirmText = currentStatus == 1 ? "Yes, Go Offline" : "Yes, Go Live";
        let btnColor = currentStatus == 1 ? "#d33" : "#377dff";

        Swal.fire({
            title: titleText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: btnColor,
            cancelButtonColor: '#secondary',
            confirmButtonText: confirmText
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('expert.dashboard.update-status') }}",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message); 

                            let btnHtml = '';
                            if (response.new_status == 1) {
                                btnHtml = `<button class="btn btn-success px-3 toggle-status-btn d-flex align-items-center gap-2" data-status="1">
                     <span class="spinner-border" role="status"></span> Go Offline
                    </button>`;
                                $('.live-indicator').show();
                            } else {
                                btnHtml = `<button class="btn btn--primary px-3 toggle-status-btn" data-status="0">
                                              <i class="fa-solid fa-tower-broadcast"></i> Go Live
                                           </button>`;
                                $('.live-indicator').hide();
                            }

                            // Update the container
                            $('.toggle-status-btn').parent().html(btnHtml + `<span class="live-indicator" style="${response.new_status == 1 ? '' : 'display:none;'}"></span>`);
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Action failed. Try again.', 'error');
                    }
                });
            }
        })
    });
</script>

<script>
$(document).ready(function() {

    // Har row ke saare checkboxes ko pakadte hain
    $('.table tbody tr').each(function() {

        const $row = $(this);

        // Is row ke 3 checkboxes
        const $available   = $row.find('input[name*="\\[available\\]"]');
        const $onBreak     = $row.find('input[name*="\\[on_break\\]"]');
        const $vacation    = $row.find('input[name*="\\[vacation_mode\\]"]');

        // Jab bhi koi checkbox change ho
        $row.find('.switcher_input').on('change', function() {

            // Agar ye wala check ho raha hai
            if (this.checked) {

                // Baaki dono ko forcefully off kar do
                if (this === $available[0]) {
                    $onBreak.prop('checked', false);
                    $vacation.prop('checked', false);
                }
                else if (this === $onBreak[0]) {
                    $available.prop('checked', false);
                    $vacation.prop('checked', false);
                }
                else if (this === $vacation[0]) {
                    $available.prop('checked', false);
                    $onBreak.prop('checked', false);
                }
            }
        });
    });

    // Optional: Form submit se pehle last check (safety)
    $('form').on('submit', function(e) {
        $('.table tbody tr').each(function() {
            const checkedCount = $(this).find('.switcher_input:checked').length;
            if (checkedCount > 1) {
                alert("Ek row mein sirf ek option select kar sakte ho bhai!");
                e.preventDefault();
                return false;
            }
        });
    });
});
</script>
@endpush