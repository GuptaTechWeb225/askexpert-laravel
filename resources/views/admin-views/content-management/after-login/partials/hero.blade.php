@php $hero = app('App\Http\Controllers\Admin\Cms\AfterLoginCmsController')->getSectionData('hero')->first(); @endphp


<div class="table-responsive shadow-sm rounded">
    <table class="table table-borderless mb-0 text-wrap align-middle">
        <thead class="border-bottom">
            <tr>
                <th scope="col">Heading</th>
                <th scope="col">Paragraph</th>
                <th scope="col">Placeholder</th>
                <th scope="col">Chat Link</th>
                <th scope="col">BG Image</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{!! $hero['heading'] ?? 'N/A' !!}</td>
                <td>{!! $hero['paragraph'] ?? 'N/A' !!}</td>
                <td>{{ $hero['search_placeholder'] ?? 'N/A' }}</td>
                <td>{{ $hero['start_chat_link'] ?? 'N/A' }}</td>
                <td>
                    @if($hero['bg_image'] ?? '')
                    <img src="{{ asset($hero['bg_image']) }}" alt="Hero Background" width="120" class="rounded shadow-sm border">
                    @else
                    <span class="badge badge-soft-secondary">{{ translate('No Image Uploaded') }}</span>
                    @endif
                </td>
                <td class="text-nowrap">
                    <button onclick="openModal('hero', 0)" class="btn btn-sm btn-primary"> <i class="tio-edit"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>