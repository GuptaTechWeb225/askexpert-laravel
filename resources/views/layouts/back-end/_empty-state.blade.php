@php
    $image = $image ?? 'default'; // if $image is not set, use 'default'
    $text = $text ?? 'nothing_found'; // if $text is not set, use 'nothing_found'
@endphp

<div class="text-center p-4">
    <img class="mb-3 w-160" src="{{ dynamicAsset(path: 'public/assets/back-end/img/empty-state-icon/' . $image . '.png') }}"
         alt="{{ translate('image_description') }}">
    <p class="mb-0">{{ translate($text) }}</p>
</div>
