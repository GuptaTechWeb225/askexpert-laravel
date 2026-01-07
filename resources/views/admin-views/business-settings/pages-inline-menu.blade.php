@php use App\Enums\ViewPaths\Admin\FeaturesSection;use App\Enums\ViewPaths\Admin\Pages; @endphp
<div class="inline-page-menu my-4">
    <ul class="list-unstyled">
        <li class="{{ Request::is('admin/business-settings/'.Pages::TERMS_CONDITION[URI]) ?'active':'' }}">
            <a href="{{route('admin.business-settings.terms-condition')}}">{{translate('terms_&_Conditions')}}</a>
        </li>
        <li class="{{ Request::is('admin/business-settings/'.Pages::PRIVACY_POLICY[URI]) ?'active':'' }}">
            <a href="{{route('admin.business-settings.privacy-policy')}}">{{translate('privacy_Policy')}}</a>
        </li>
          <li class="{{ Request::is('admin/business-settings/'.Pages::VIEW[URI].'/refund-policy') ?'active':'' }}">
            <a href="{{route('admin.business-settings.page',['refund-policy'])}}">{{translate('refund_Policy')}}</a>
        </li>
    </ul>
</div>
