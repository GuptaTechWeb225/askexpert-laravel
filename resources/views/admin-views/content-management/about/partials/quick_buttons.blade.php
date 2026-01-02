@php 
    $buttons = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('quick_buttons'); 
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">{{ translate('Quick Buttons') }}</h6>
    <button onclick="openModal('quick_buttons', 0, true)" class="btn btn-sm btn-success">
        <i class="tio-add"></i> {{ translate('Add New') }}
    </button>
</div>

<div class="table-responsive datatable-custom">
    <table 
        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};" 
        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">

        <tbody>
            {{-- Header Row (as <td>) --}}
            <tr class="thead-light text-capitalize fw-semibold">
                <td style="width: 60px;">{{ translate('SL') }}</td>
                <td>{{ translate('Button Text') }}</td>
                <td>{{ translate('Link') }}</td>
                <td class="text-center">{{ translate('Action') }}</td>
            </tr>

            {{-- Data Rows --}}
            @foreach($buttons as $id => $btn)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><strong>{{ $btn['text'] ?? '-' }}</strong></td>
                <td>
                    @if($btn['link'] ?? '')
                        <a href="{{ $btn['link'] }}" target="_blank" class="text-info text-decoration-underline">
                            {{ $btn['link'] }}
                        </a>
                    @else
                        <span class="text-muted">{{ translate('No link added') }}</span>
                    @endif
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        <button onclick="openModal('quick_buttons', {{ $id }})" 
                            class="btn btn-outline-warning btn-sm square-btn">
                            <i class="tio-edit"></i>
                        </button>
                        <button onclick="deleteItem('quick_buttons', {{ $id }})" 
                            class="btn btn-outline-danger btn-sm square-btn">
                            <i class="tio-delete"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach

            @if(count($buttons) == 0)
            <tr>
                <td colspan="4" class="text-center text-muted py-4">
                    {{ translate('No quick buttons added yet') }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
