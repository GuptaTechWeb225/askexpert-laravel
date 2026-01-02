@php 
    $items = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('difference'); 
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">{{ translate('What Makes Us Different') }}</h6>
</div>

<div class="table-responsive datatable-custom">
    <table 
        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};" 
        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">

        <tbody>
            {{-- Header Row (as <td>) --}}
            <tr class="thead-light text-capitalize fw-semibold">
                <td style="width: 60px;">{{ translate('SL') }}</td>
                <td>{{ translate('Icon') }}</td>
                <td>{{ translate('Title') }}</td>
                <td>{{ translate('Description') }}</td>
                <td class="text-center">{{ translate('Action') }}</td>
            </tr>

            {{-- Data Rows --}}
            @foreach($items as $id => $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    @if($item['icon'] ?? '')
                        <img src="{{ asset($item['icon']) }}" alt="Icon" width="40" class="rounded shadow-sm border">
                    @else
                        <span class="badge badge-soft-secondary">{{ translate('No Icon') }}</span>
                    @endif
                </td>
                <td><strong>{{ $item['title'] ?? '-' }}</strong></td>
                <td class="small text-muted">{{ $item['description'] ?? '-' }}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        <button onclick="openModal('difference', {{ $id }})" 
                            class="btn btn-outline-warning btn-sm square-btn">
                            <i class="tio-edit"></i>
                        </button>
                        <button onclick="deleteItem('difference', {{ $id }})" 
                            class="btn btn-outline-danger btn-sm square-btn">
                            <i class="tio-delete"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach

            @if(count($items) == 0)
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    {{ translate('No items added yet') }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
