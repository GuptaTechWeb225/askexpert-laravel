@php
    use App\Enums\ViewPaths\Admin\ErrorLogs;
    use App\Enums\ViewPaths\Admin\RobotsMetaContent;
    use App\Enums\ViewPaths\Admin\SEOSettings;
    use App\Enums\ViewPaths\Admin\SiteMap;
@endphp
<div class="inline-page-menu my-4">
    <ul class="list-unstyled">
        <li class="{{ Request::is('admin/seo-settings/robots-meta-content*') ? 'active' : '' }}">
            <a href="{{ route('admin.seo-settings.robots-meta-content.index') }}">
                {{ translate('Robots_Meta_Content') }}
            </a>
        </li>
        <li class="{{ Request::is('admin/error-logs/'.ErrorLogs::INDEX[URI]) ? 'active' : '' }}">
            <a href="{{ route('admin.error-logs.index') }}">
                {{ translate('404_Logs') }}
            </a>
        </li>
    </ul>
</div>
