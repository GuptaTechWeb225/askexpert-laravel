@php 
    $main = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('our_mission')[0] ?? [];
    $images = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('our_mission');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">{{ translate('Our Mission') }}</h6>
    <div class="d-flex gap-2">
        <button onclick="openModal('our_mission', 0)" class="btn btn-sm btn-primary">
            <i class="tio-edit"></i> {{ translate('Edit Text') }}
        </button>
    </div>
</div>

{{-- Mission Text --}}
<div class="mb-3">
    <p class="fw-bold mb-1">{{ $main['title'] ?? '' }}</p>
    <p class="mb-1 text-muted">{{ $main['paragraph1'] ?? '' }}</p>
    <p class="mb-0 text-muted">{{ $main['paragraph2'] ?? '' }}</p>
</div>

{{-- Mission Images Table --}}
<div class="table-responsive datatable-custom">
    <table 
        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};" 
        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">

        <tbody>
            {{-- Header Row (as <td>) --}}
            <tr class="thead-light text-capitalize fw-semibold">
                <td style="width: 60px;">{{ translate('SL') }}</td>
                <td>{{ translate('Image') }}</td>
                <td class="text-center">{{ translate('Action') }}</td>
            </tr>

            {{-- Data Rows --}}
            @foreach($images as $id => $img)
                @if($id > 0)
                <tr>
                    <td>{{ $loop->iteration - 1 }}</td>
                    <td>
                        @if($img['image'] ?? '')
                            <img src="{{ asset($img['image']) }}" alt="Mission Image" width="100" class="rounded shadow-sm border">
                        @else
                            <span class="badge badge-soft-secondary">{{ translate('No Image') }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <button onclick="openModal('our_mission', {{ $id }})" 
                                class="btn btn-outline-warning btn-sm square-btn">
                                <i class="tio-edit"></i>
                            </button>
                          
                        </div>
                    </td>
                </tr>
                @endif
            @endforeach

            @if(count($images) <= 1)
            <tr>
                <td colspan="3" class="text-center text-muted py-4">
                    {{ translate('No images added yet') }}
                </td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
