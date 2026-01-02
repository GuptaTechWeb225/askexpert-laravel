@php 
    $hero = app('App\Http\Controllers\Admin\Cms\AboutController')::getSectionDataStatic('hero')[0] ?? []; 
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">{{ translate('Hero Section') }}</h6>
    <button onclick="openModal('hero', 0)" class="btn btn-sm btn-primary">
        <i class="tio-edit"></i> {{ translate('Edit') }}
    </button>
</div>

<div class="table-responsive datatable-custom">
    <table 
        style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};" 
        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
        
        <thead class="thead-light thead-50 text-capitalize">
            <tr>
                <th style="width: 60px;">{{ translate('SL') }}</th>
                <th>{{ translate('Field') }}</th>
                <th>{{ translate('Value') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><strong>{{ translate('Heading 1') }}</strong></td>
                <td>{{ $hero['heading1'] ?? '' }}</td>
            </tr>
            <tr>
                <td>2</td>
                <td><strong>{{ translate('Heading 2') }}</strong></td>
                <td>{{ $hero['heading2'] ?? '' }}</td>
            </tr>
            <tr>
                <td>3</td>
                <td><strong>{{ translate('Search Placeholder') }}</strong></td>
                <td>{{ $hero['search_placeholder'] ?? '' }}</td>
            </tr>
            <tr>
                <td>4</td>
                <td><strong>{{ translate('Paragraph') }}</strong></td>
                <td>{{ $hero['paragraph'] ?? '' }}</td>
            </tr>
            <tr>
                <td>5</td>
                <td><strong>{{ translate('Image') }}</strong></td>
                <td>
                    @if($hero['bg_image'] ?? '')
                        <img src="{{ asset($hero['bg_image']) }}" alt="Hero Background" width="120" class="rounded shadow-sm border">
                    @else
                        <span class="badge badge-soft-secondary">{{ translate('No Image Uploaded') }}</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>
