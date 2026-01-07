@php

use App\Enums\ViewPaths\Admin\Customer;
use App\Enums\ViewPaths\Admin\Dashboard;
use App\Enums\ViewPaths\Admin\Pages;
use App\Enums\ViewPaths\Admin\SocialMedia;
use App\Enums\ViewPaths\Admin\SEOSettings;
use App\Enums\ViewPaths\Admin\SiteMap;
use App\Enums\ViewPaths\Admin\Expert;
use App\Utils\Helpers;
$eCommerceLogo = getWebConfig(name: 'company_web_logo');
@endphp
<div id="sidebarMain" class="d-none">
    <aside class="bg-white js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered text-start">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between side-logo dashboard-navbar-side-logo-wrapper">
                <a class="navbar-brand" href="{{route('admin.dashboard.index')}}" aria-label="Front">
                    <img class="navbar-brand-logo-mini for-web-logo max-h-30" src="{{ getStorageImages(path:$eCommerceLogo, type: 'backend-logo') }}" alt="{{ translate('logo')}}">
                </a>
                <button type="button" class="d-none js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                    <i class="tio-clear tio-lg"></i>
                </button>

                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align" data-template="<div class=&quot;tooltip d-none d-sm-block&quot; role=&quot;tooltip&quot;><div class=&quot;arrow&quot;></div><div class=&quot;tooltip-inner&quot;></div></div>"></i>
                </button>
            </div>
            <div class="navbar-vertical-footer-offset pb-0">
                <div class="navbar-vertical-content">
                    <div class="sidebar--search-form pb-3 pt-4 mx-3">
                        <div class="search--form-group">
                            <button type="button" class="btn"><i class="tio-search"></i></button>
                            <input type="text" class="js-form-search form-control form--control" id="search-bar-input" placeholder="{{translate('search_menu').'...'}}">
                        </div>
                    </div>

                    <ul class="navbar-nav navbar-nav-lg nav-tabs">

                        <!-- Dashboard -->
                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/dashboard'.Dashboard::VIEW[URI])?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" title="Dashboard" href="{{route('admin.dashboard.index')}}">
                                <i class="tio-dashboard-vs-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Dashboard</span>
                            </a>
                        </li>

                        @if(Helpers::module_permission_check('user_management'))
                        <li class="nav-item">
                            <small class="nav-subtitle" title="">User Management</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/customer/'.Customer::LIST[URI]) || Request::is('admin/customer/'.Customer::VIEW[URI].'*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" title="Customer Management" href="{{ route('admin.customer.list') }}">
                                <i class="tio-users-switch nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Customer Management</span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/expert/questions*') ?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" title="Manage Questions" href="{{ route('admin.expert.questions') }}">
                                <i class="tio-help nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Manage Questions</span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/refunds')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" title="Refund Requests" href="{{ route('admin.refunds.index') }}">
                                <i class="tio-money nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Refund Requests</span>
                            </a>
                        </li>
                        @endif

                        <li class="nav-item">
                            <small class="nav-subtitle" title="">Expert Management</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/expert/'.Expert::EXPERT_REQUEST[URI])?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" title="Expert Applications" href="{{ route('admin.expert.request') }}">
                                <i class="tio-file-text nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Expert Applications</span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/expert-payouts*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" title="Revenue & Payouts" href="{{ route('admin.expert-payouts.index') }}">
                                <i class="tio-money-vs nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Revenue & Payouts</span>
                            </a>
                        </li>

                        @if(Helpers::module_permission_check('plan_management'))
                        <li class="nav-item">
                            <small class="nav-subtitle" title="">Expert & Categories</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{(Request::is('admin/expert/'.Expert::EXPERTS[URI]) || Request::is('admin/expert/'.Expert::EXPERT_VIEW[URI]))?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" title="Expert Management" href="{{ route('admin.expert.index') }}">
                                <i class="tio-user nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Expert Management</span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{(Request::is('admin/expert-category*')) ||(Request::is('admin/expert-category/index')) || (Request::is('admin/expert-category/create'))?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" title="Categories & Pricing" href="{{ route('admin.expert-category.index') }}">
                                <i class="tio-label nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Categories & Pricing</span>
                            </a>
                        </li>
                        @endif

                        <!-- Internal Messages -->
                        @if(Helpers::module_permission_check('report'))
                        <li class="nav-item">
                            <small class="nav-subtitle" title="">Communication</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{(Request::is('admin/expert-chat/all'))?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" title="Internal Messages" href="{{ route('admin.expert-chat.index') }}">
                                <i class="tio-chat nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Internal Messages</span>
                            </a>
                        </li>
                        <li class="navbar-vertical-aside-has-menu {{(Request::is('admin/contact/list'))?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" title="Internal Messages" href="{{ route('admin.contact.list') }}">
                                <i class="tio-chat nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Web Inquiries</span>
                            </a>
                        </li>
                        @endif

                        <!-- Backup & Restore + Dispatch -->
                        @if(Helpers::module_permission_check('support_section'))
                        <li class="nav-item">
                            <small class="nav-subtitle" title="">System Tools</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/backup/index')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" title="Backup & Restore" href="{{ route('admin.backup.index') }}">
                                <i class="tio-restore nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Backup & Restore</span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/business-settings/dispatch-settings')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" title="Dispatch Settings" href="{{ route('admin.business-settings.dispatch-view') }}">
                                <i class="tio-tune-horizontal nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Dispatch Settings</span>
                            </a>
                        </li>
                        @endif

                        <!-- Settings Dropdown -->
                        @if(Helpers::module_permission_check('system_settings'))
                        <li class="nav-item">
                            <small class="nav-subtitle" title="">Settings</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="Setting">
                                <i class="tio-settings nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Setting</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{Request::is('admin/content-management/home') || Request::is('admin/pricing-cms') || Request::is('admin/content-management/about') || Request::is('admin/content-management/expert-cms') || Request::is('admin/content-management/help') || Request::is('admin/content-management/web-config*') || Request::is('admin/business-settings/social-media') ? 'block' : 'none'}}">
                                <li class="nav-item {{Request::is('admin/content-management/home')?'active':''}}">
                                    <a class="nav-link" href="{{ route('admin.content-management.home') }}" title="{{translate('Home')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('Home')}}
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('admin/pricing-cms*')?'active':''}}">
                                    <a class="nav-link" href="{{ route('admin.content-management.pricing') }}" title="{{translate('Pricing')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('Pricing')}}
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('admin/content-management/expert-cms')?'active':''}}">
                                    <a class="nav-link" href="{{ route('admin.content-management.expert') }}" title="{{translate('Become An Expert')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('Become An Expert')}}
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('admin/content-management/about')?'active':''}}">
                                    <a class="nav-link" href="{{ route('admin.content-management.about') }}" title="{{translate('About us')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('About us')}}
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('admin/content-management/help')?'active':''}}">
                                    <a class="nav-link" href="{{ route('admin.content-management.help') }}" title="{{translate('Help')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('Help')}}
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('admin/content-management/after-login')?'active':''}}">
                                    <a class="nav-link" href="{{ route('admin.content-management.after-login') }}" title="{{translate('After_Login')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('After_Login')}}
                                        </span>
                                    </a>
                                </li>
                                  <li class="nav-item {{ (
                                    Request::is('admin/business-settings/'.Pages::TERMS_CONDITION[URI]) ||
                                    Request::is('admin/business-settings/'.Pages::VIEW[URI].'*') ||
                                    Request::is('admin/business-settings/'.Pages::PRIVACY_POLICY[URI]) ||
                                    Request::is('admin/business-settings/'.Pages::ABOUT_US[URI])) ? 'active' : '' }}">
                                    <a class="nav-link" href="{{route('admin.business-settings.terms-condition')}}"
                                        title="{{translate('business_Pages')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('business_Pages')}}</span>
                                    </a>
                                </li>

                            
                                <li
                                    class="nav-item {{ Request::is('admin/business-settings/'.SocialMedia::VIEW[URI]) ? 'active' : '' }}">
                                    <a class="nav-link"
                                        href="{{route('admin.business-settings.social-media')}}"
                                        title="{{translate('social_Media_Links')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span
                                            class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('social_Media_Links')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="Setting">
                                <i class="tio-settings nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Configurations</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{Request::is('admin/business-settings/payment-method') || Request::is('admin/business-settings/'.Pages::TERMS_CONDITION[URI]) || Request::is('admin/business-settings/'.Pages::ABOUT_US[URI]) ? 'block' : 'none'}}">
                                <li class="nav-item {{Request::is('admin/business-settings/payment-method')?'active':''}}">
                                    <a class="nav-link" href="{{ route('admin.business-settings.payment-method.index') }}" title="{{translate('Home')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('payment methods')}}
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('admin/social-login/view') || Request::is('admin/business-settings/mail') || Request::is('admin/firebase-otp-verification/index')?'active':''}}">
                                    <a class="nav-link" href="{{ route('admin.social-login.view') }}" title="{{translate('Pricing')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('other_Configurations')}}
                                        </span>
                                    </a>
                                </li>

                            </ul>
                        </li>
                         <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                                title="{{translate('business_Setup')}}">
                                <i class="tio-pages-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('business_Setup')}}
                                </span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display: {{(
                                        Request::is('admin/business-settings/web-config') ||
                                        Request::is('admin/business-settings/delivery-restriction'))?'block':'none'}}">
                                <li
                                    class="nav-item {{(
                                            Request::is('admin/business-settings/web-config') ||
                                         
                                            Request::is('admin/business-settings/delivery-restriction'))?'active':''}}">
                                    <a class="nav-link" href="{{route('admin.business-settings.web-config.index')}}"
                                        title="{{translate('business_Settings')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{translate('business_Settings')}}
                                        </span>
                                    </a>
                                </li>
                               
                                <li class="nav-item {{
                                        (Request::is('admin/seo-settings/'.SEOSettings::WEB_MASTER_TOOL[URI]) ||
                                        Request::is('admin/seo-settings/'.SEOSettings::ROBOT_TXT[URI]) ||
                                        Request::is('admin/seo-settings/'.SiteMap::SITEMAP[URI]) ||
                                        Request::is('admin/seo-settings/robots-meta-content/*')) ? 'active' : ''
                                    }}">
                                    <a class="nav-link" href="{{ route('admin.seo-settings.robots-meta-content.index') }}"
                                        title="{{ translate('SEO_Settings') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">
                                            {{ translate('SEO_Settings') }}
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif


                        <li class="nav-item pt-5"></li>
                    </ul>
                </div>
            </div>
        </div>
    </aside>
</div>