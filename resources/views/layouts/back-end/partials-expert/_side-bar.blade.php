@php
$eCommerceLogo = getWebConfig(name: 'company_web_logo');

@endphp
<div id="sidebarMain" class="d-none">
    <aside style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};"
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered  ">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between side-logo dashboard-navbar-side-logo-wrapper">
                <a class="navbar-brand" href="{{route('expert.dashboard.index')}}" aria-label="Front">
                    <img class="navbar-brand-logo-mini for-web-logo max-h-30" src="{{ getStorageImages(path:$eCommerceLogo, type: 'backend-logo') }}" alt="{{ translate('logo')}}">
                </a>
                <button type="button"
                    class="d-none js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                    <i class="tio-clear tio-lg"></i>
                </button>

                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-3">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                        data-template="<div class=&quot;tooltip d-none d-sm-block&quot; role=&quot;tooltip&quot;><div class=&quot;arrow&quot;></div><div class=&quot;tooltip-inner&quot;></div></div>"></i>
                </button>
            </div>
            <div class="navbar-vertical-footer-offset pb-0">
                <div class="navbar-vertical-content">
                    <div class="sidebar--search-form pb-3 pt-4 mx-3">
                        <div class="search--form-group">
                            <button type="button" class="btn"><i class="tio-search"></i></button>
                            <input type="text" class="js-form-search form-control form--control" id="search-bar-input"
                                placeholder="{{translate('search_menu').'...'}}">
                        </div>
                    </div>
                    <ul class="navbar-nav navbar-nav-lg nav-tabs">
                        <li class="navbar-vertical-aside-has-menu {{Request::is('expert/dashboard*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{route('expert.dashboard.index')}}" title="{{translate('dashboard')}}">
                                <i class="tio-home-vs-1-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('dashboard')}}
                                </span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{Request::is('expert/questions*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{route('expert.questions.all')}}" title="{{translate('my_Questions')}}">
                                <i class="tio-user nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('my_Questions')}}
                                </span>
                            </a>
                        </li>
                      
                        <li class="navbar-vertical-aside-has-menu {{ (Request::is('expert/earnings*')) ?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('expert.earnings') }}"
                                title="{{translate('ernings')}}">
                                <i class="tio-chart-bar-3 nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate text-capitalize">
                                    {{translate('ernings')}}
                                </span>
                            </a>
                        </li>


                        <li class="navbar-vertical-aside-has-menu {{ (Request::is('expert/settings/profile/update')) ?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{ route('expert.settings.profile.edit') }}" title="{{translate('profile')}}">
                                <i class="tio-hot nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate text-capitalize">
                                    {{translate('profile')}}
                                </span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{ (Request::is('expert/massages/all')) ?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                href="{{route('expert.massages.allmassages')}}" title="{{translate('messages')}}">
                                <i class="tio-notifications nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate text-capitalize">
                                    {{translate('messages')}}
                                </span>
                            </a>
                        </li>
                          <li class="navbar-vertical-aside-has-menu {{Request::is('expert/notifications/list')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                                title="{{translate('notifications')}}" href="{{route('expert.notifications.list')}}">
                                <span class="nav-icon">
                                    <svg width="15" height="15" viewBox="0 0 20 20" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_5926_1152)">
                                            <path
                                                d="M10 20.5C11.1046 20.5 12 19.6046 12 18.5H8C8 19.6046 8.89543 20.5 10 20.5ZM16 14.5V9.5C16 6.57436 14.3682 4.15379 11.75 3.53235V3C11.75 2.30964 11.1904 1.75 10.5 1.75C9.80964 1.75 9.25 2.30964 9.25 3V3.53235C6.63184 4.15379 5 6.57436 5 9.5V14.5L3 16.5V17.5H17V16.5L16 14.5Z"
                                                fill="#fff"></path>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_5926_1152">
                                                <rect width="20" height="20" fill="white" transform="translate(0 0.5)">
                                                </rect>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </span>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    {{translate('notifications')}}
                                </span>
                            </a>
                        </li>

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
                                style="display: {{Request::is('expert/settings/availability') || Request::is('expert/settings/communication-modes') || Request::is('expert/settings/notifications') ? 'block' : 'none'}}">
                                <li class="nav-item {{Request::is('expert/settings/availability')?'active':''}}">
                                    <a class="nav-link" href="{{ route('expert.settings.availability') }}" title="{{translate('Availability Setting')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('Availability Setting')}}
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('expert/settings/communication-modes')?'active':''}}">
                                    <a class="nav-link" href="{{ route('expert.settings.communication') }}" title="{{translate('Communication Preferences')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('Communication Preferences')}}
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('expert/settings/notifications')?'active':''}}">
                                    <a class="nav-link" href="{{ route('expert.settings.notifications') }}" title="{{translate('Notification Settings')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('Notification Settings')}}
                                        </span>
                                    </a>
                                </li>
                                <!-- <li class="nav-item {{Request::is('admin/content-management/about')?'active':''}}">
                                    <a class="nav-link" href="" title="{{translate('language_&_Timezone_Settings')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('language_&_Timezone_Settings')}}
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('admin/content-management/help')?'active':''}}">
                                    <a class="nav-link" href="" title="{{translate('Security_Options')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{translate('Security_Options')}}
                                        </span>
                                    </a>
                                </li> -->
                            </ul>
                        </li>
                        <li class="nav-item pt-5">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </aside>
</div>