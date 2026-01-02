@php $hero = app('App\Http\Controllers\Admin\Cms\HomeController')->getSectionData('hero')->first(); @endphp


<div class="table-responsive shadow-sm rounded">
    <table class="table table-borderless mb-0 text-wrap align-middle">
        <thead class="border-bottom">
            <tr>
                <th scope="col">Heading</th>
                <th scope="col">Paragraph</th>
                <th scope="col">Placeholder</th>
                <th scope="col">Chat Link</th>
                <th scope="col">BG Video</th>
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
                    @if(!empty($hero['bg_video']))
                    <video width="90" height="90" controls muted class="rounded">
                        <source src="{{ asset($hero['bg_video']) }}" type="video/mp4">
                    </video>
                    @else
                    <span class="text-muted">No video</span>
                    @endif
                </td>
                <td class="text-nowrap">
                    <button onclick="openModal('hero', 0)" class="btn btn-sm btn-primary">                                    <i class="tio-edit"></i>
</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>