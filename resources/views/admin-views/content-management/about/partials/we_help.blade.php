@php 
    $items = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('we_help'); 
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">{{ translate('Who We Help') }}</h6>
    <button onclick="openModal('we_help', 0, true)" class="btn btn-sm btn-success">
        <i class="tio-add"></i> {{ translate('Add New') }}
    </button>
</div>

<div class="table-responsive datatable-custom">
    <table 
        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};" 
        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">

        <tbody>
            <tr class="thead-light text-capitalize fw-semibold">
                <td style="width: 60px;">{{ translate('SL') }}</td>
                <td>{{ translate('Title') }}</td>
                <td>{{ translate('Description') }}</td>
                <td class="text-center">{{ translate('Action') }}</td>
            </tr>
            @foreach($items as $id => $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><strong>{{ $item['title'] ?? '-' }}</strong></td>
                <td class="text-muted small">{{ $item['description'] ?? '-' }}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        <button onclick="openModal('we_help', {{ $id }})" 
                            class="btn btn-outline-warning btn-sm square-btn" title="{{ translate('Edit') }}">
                            <i class="tio-edit"></i>
                        </button>
                        <button onclick="deleteItem('we_help', {{ $id }})" 
                            class="btn btn-outline-danger btn-sm square-btn" title="{{ translate('Delete') }}">
                            <i class="tio-delete"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach

            @if(count($items) == 0)
            <tr>
                <td colspan="4" class="text-center text-muted py-4">
                    {{ translate('No items added yet') }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
