<p class="view-footer-text">
    {{$footerText}}
</p>
<p>{{translate('Thanks_&_Regards')}}, <br> {{getWebConfig('company_name')}}</p>
<div class="d-flex justify-content-center mb-3 ">
    <img width="76" class="mx-auto" id="view-mail-logo" src="{{$template->logo_full_url['path'] ?? getStorageImages(path: $companyLogo, type:'backend-logo')}}" alt="">
</div>
<div class="d-flex justify-content-center gap-2">
  
</div>
<div class="d-flex gap-4 justify-content-center align-items-center mb-3 fz-16 social-media-icon" id="selected-social-media">
    <div class="mx-auto">
    @foreach($socialMedia as $key=>$media)
        @if(!empty($template['social_media']))
            <a class="{{$media['name']}} {{in_array($media['name'],$template['social_media']) ? '' : 'd-none'}}" href="{{$media['link']}}" target="_blank">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/'.$media['name'].'.png') }}"
                     width="16" alt="">
            </a>
        @else
            <a class="{{$media['name']}}" href="{{$media['link']}}" target="_blank">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/'.$media['name'].'.png') }}"
                     width="16" alt="">
            </a>
        @endif
    @endforeach
    </div>
</div>
<p class="text-center view-copyright-text">
    {{$copyrightText}}
</p>
