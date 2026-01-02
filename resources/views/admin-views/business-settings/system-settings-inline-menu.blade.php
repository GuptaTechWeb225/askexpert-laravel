@php
    use App\Enums\ViewPaths\Admin\BusinessSettings;
    use App\Enums\ViewPaths\Admin\Currency;
    use App\Enums\ViewPaths\Admin\DatabaseSetting;
    use App\Enums\ViewPaths\Admin\EnvironmentSettings;
    use App\Enums\ViewPaths\Admin\SiteMap;
    use App\Enums\ViewPaths\Admin\SoftwareUpdate;
@endphp
<div class="inline-page-menu my-4">
    <ul class="list-unstyled">
        <li class="{{ Request::is('admin/business-settings/web-config/'.EnvironmentSettings::VIEW[URI]) ?'active':'' }}">
            <a href="{{route('admin.business-settings.web-config.environment-setup')}}">{{translate('Environment_Settings')}}</a>
        </li>

        <li class="{{ Request::is('admin/business-settings/web-config/'.BusinessSettings::APP_SETTINGS[URI]) ?'active':'' }}">
            <a href="{{route('admin.business-settings.web-config.app-settings')}}">{{translate('app_Settings')}}</a>
        </li>

    
        <li class="{{ Request::is('admin/business-settings/'.BusinessSettings::COOKIE_SETTINGS[URI]) ? 'active':'' }}">
            <a href="{{ route('admin.business-settings.cookie-settings') }}">{{translate('cookies')}}</a>
        </li>
      
    </ul>
</div>
