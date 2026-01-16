@php
$items = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('achievements');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0"></h6>
</div>

<div class="table-responsive datatable-custom">
    <table
        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};"
        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
        <thead class="thead-light thead-50 text-capitalize">
            <tr class="">
                <td style="width: 60px;">{{ translate('SL') }}</td>
                <td>{{ translate('Icon') }}</td>
                <td>{{ translate('Number') }}</td>
                <td>{{ translate('Text') }}</td>
                <td class="text-center">{{ translate('Action') }}</td>
            </tr>
        </thead>
        <tbody>
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
                <td><span class="fw-semibold">{{ $item['number'] ?? '-' }}</span></td>
                <td>{{ $item['text'] ?? '-' }}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        <button onclick="openModal('achievements', {{ $id }})"
                            class="btn btn-outline-warning btn-sm square-btn">
                            <i class="tio-edit"></i>
                        </button>
                        <!-- <button onclick="deleteItem('achievements', {{ $id }})"
                            class="btn btn-outline-danger btn-sm square-btn">
                            <i class="tio-delete"></i>
                        </button> -->
                    </div>
                </td>
            </tr>
            @endforeach

            @if(count($items) == 0)
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    {{ translate('No achievements added yet') }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>