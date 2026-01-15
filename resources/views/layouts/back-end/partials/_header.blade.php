@php
use Illuminate\Support\Facades\Session;
use App\Utils\Notifications;
$adminId = auth('admin')->id();
$notifications = Notifications::getNotifications($adminId, [0]);
@endphp
@php($direction = Session::get('direction'))
<div id="headerMain" class="d-none">
    <header id="header" class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container shadow">
        <div class="navbar-nav-wrap">
            <div class="navbar-brand-wrapper d-none d-sm-block d-xl-none">
                @php($ecommerceLogo = getWebConfig('company_web_logo'))
                <a class="navbar-brand" href="{{route('admin.dashboard.index')}}" aria-label="">
                    <img class="navbar-brand-logo"
                        src="{{ getStorageImages($ecommerceLogo, type: 'backend-logo')}}" alt="{{ translate('logo') }}">
                    <img class="navbar-brand-logo-mini"
                        src="{{getStorageImages($ecommerceLogo, type: 'backend-logo')}}"
                        alt="{{ translate('logo') }}">
                </a>
            </div>
            <div class="navbar-nav-wrap-content-left">
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-3 d-xl-none">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                        data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                        data-toggle="tooltip" data-placement="right" title="Expand"></i>
                </button>
            </div>
            <div class="navbar-nav-wrap-content-right"
                style="{{$direction == "rtl" ? 'margin-left:unset; margin-right: auto' : 'margin-right:unset; margin-left: auto'}}">
                <ul class="navbar-nav align-items-center flex-row gap-xl-16px">
                    <li class="nav-item">
                        <div class="hs-unfold">
                            <a title="Website home"
                                class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle"
                                href="{{route('home')}}" target="_blank" data-toggle="tooltip" data-custom-class="header-icon-title">
                                <svg width="25" height="26" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13.3663 6.75C12.7069 3.6875 11.3007 1.75 10 1.75C8.69941 1.75 7.29316 3.6875 6.63379 6.75H13.3663Z" fill="#073B74" />
                                    <path d="M6.25 10.5C6.24985 11.3361 6.3056 12.1713 6.41688 13H13.5831C13.6944 12.1713 13.7502 11.3361 13.75 10.5C13.7502 9.66388 13.6944 8.82868 13.5831 8H6.41688C6.3056 8.82868 6.24985 9.66388 6.25 10.5Z" fill="#073B74" />
                                    <path d="M6.63379 14.25C7.29316 17.3125 8.69941 19.25 10 19.25C11.3007 19.25 12.7069 17.3125 13.3663 14.25H6.63379Z" fill="#073B74" />
                                    <path d="M14.6462 6.74965H18.5837C17.9921 5.40325 17.0932 4.21424 15.9591 3.27798C14.8249 2.34173 13.4872 1.68429 12.0531 1.3584C13.2387 2.40152 14.1687 4.33027 14.6462 6.74965Z" fill="#073B74" />
                                    <path d="M19.0331 8H14.8456C14.9487 8.82934 15.0003 9.66428 15 10.5C15.0001 11.3357 14.9483 12.1707 14.845 13H19.0325C19.4883 11.3645 19.4889 9.6355 19.0331 8Z" fill="#073B74" />
                                    <path d="M12.0531 19.6412C13.4874 19.3155 14.8254 18.6582 15.9598 17.7219C17.0941 16.7856 17.9932 15.5965 18.585 14.25H14.6475C14.1687 16.6694 13.2387 18.5981 12.0531 19.6412Z" fill="#073B74" />
                                    <path d="M5.35376 14.25H1.41626C2.008 15.5965 2.90712 16.7856 4.04147 17.7219C5.17582 18.6582 6.51382 19.3155 7.94813 19.6412C6.76126 18.5981 5.83126 16.6694 5.35376 14.25Z" fill="#073B74" />
                                    <path d="M7.94691 1.3584C6.5126 1.68411 5.1746 2.34147 4.04025 3.27774C2.9059 4.214 2.00678 5.40311 1.41504 6.74965H5.35254C5.83129 4.33027 6.76129 2.40152 7.94691 1.3584Z" fill="#073B74" />
                                    <path d="M4.99996 10.5C4.99987 9.66426 5.05164 8.82933 5.15495 8H0.967455C0.511662 9.6355 0.511662 11.3645 0.967455 13H5.15495C5.05164 12.1707 4.99987 11.3357 4.99996 10.5Z" fill="#073B74" />
                                </svg>
                            </a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle"
                                href="{{route('admin.expert.questions')}}" title="{{translate('message')}}" data-toggle="tooltip" data-custom-class="header-icon-title">
                                <svg width="25" height="26" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_5926_1152)">
                                        <path d="M16.6666 2.16699H3.33329C2.41663 2.16699 1.67496 2.91699 1.67496 3.83366L1.66663 18.8337L4.99996 15.5003H16.6666C17.5833 15.5003 18.3333 14.7503 18.3333 13.8337V3.83366C18.3333 2.91699 17.5833 2.16699 16.6666 2.16699ZM4.99996 8.00033H15V9.66699H4.99996V8.00033ZM11.6666 12.167H4.99996V10.5003H11.6666V12.167ZM15 7.16699H4.99996V5.50033H15V7.16699Z" fill="#073B74" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_5926_1152">
                                            <rect width="20" height="20" fill="white" transform="translate(0 0.5)" />
                                        </clipPath>
                                    </defs>
                                </svg>
                                @php($message=\App\Models\Contact::where('seen',0)->count())
                                @if($message!=0)
                                <span class="btn-status btn-sm-status btn-status-danger">{{ $message }}</span>
                                @endif
                            </a>
                        </div>
                    </li>


                    <li class="nav-item">
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle dropdown-toggle dropdown-toggle-left-arrow" href="javascript:" data-hs-unfold-options='{
                                     "target": "#notificationDropdown",
                                     "type": "css-animation"
                                   }'>
                                <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_5926_1152)">
                                        <path d="M10 20.5C11.1046 20.5 12 19.6046 12 18.5H8C8 19.6046 8.89543 20.5 10 20.5ZM16 14.5V9.5C16 6.57436 14.3682 4.15379 11.75 3.53235V3C11.75 2.30964 11.1904 1.75 10.5 1.75C9.80964 1.75 9.25 2.30964 9.25 3V3.53235C6.63184 4.15379 5 6.57436 5 9.5V14.5L3 16.5V17.5H17V16.5L16 14.5Z" fill="#073B74" />
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_5926_1152">
                                            <rect width="20" height="20" fill="white" transform="translate(0 0.5)" />
                                        </clipPath>
                                    </defs>
                                </svg>
                                @if($notifications->count() != 0)
                                <span class="btn-status btn-sm-status btn-status-danger">{{ $notifications->count() }}</span>
                                @endif
                            </a>

                            <div id="notificationDropdown" class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu py-2" style="border: 1px solid #ccc;">

                                @forelse($notifications as $notification)
                                <a class="dropdown-item" href="">
                                    <strong>{{ $notification->title }}</strong><br>
                                    <small class="text-wrap">{{ \Illuminate\Support\Str::limit($notification->message, 80) }}</small>
                                </a>
                                @empty
                                <p class="dropdown-item">No notifications yet</p>
                                @endforelse

                                <div class="dropdown-divider"></div>

                            </div>
                        </div>
                    </li>


                    <li class="nav-item">
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker media align-items-center gap-3 navbar-dropdown-account-wrapper dropdown-toggle dropdown-toggle-left-arrow border-color-primary-light px-3"
                                href="javascript:"
                                data-hs-unfold-options='{
                                     "target": "#accountNavbarDropdown",
                                     "type": "css-animation"
                                   }'>

                                <div class="avatar border avatar-circle">
                                    <img class="avatar-img"
                                        src="{{getStorageImages(path: auth('admin')->user()->image_full_url,type: 'backend-profile')}}"
                                        alt="{{translate('image_description')}}">
                                    <span class="d-none avatar-status avatar-sm-status avatar-status-success"></span>
                                </div>
                                <div class="d-none d-md-block media-body text-right">
                                    <h5 class="profile-name mb-0">{{auth('admin')->user()->name}}</h5>
                                    <span class="fz-12">{{ auth('admin')->user()->role->name ?? '' }}</span>
                                </div>
                            </a>
                            <div id="accountNavbarDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account">
                                <div class="dropdown-item-text">
                                    <div class="media align-items-center text-break">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img" src="{{getStorageImages(path: auth('admin')->user()->image_full_url,type: 'backend-profile')}}"
                                                alt="{{translate('image_description')}}">
                                        </div>
                                        <div class="media-body">
                                            <span class="card-title h5">{{auth('admin')->user()->name}}</span>
                                            <span class="card-text">{{auth('admin')->user()->email}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item"
                                    href="{{route('admin.profile.update',auth('admin')->user()->id)}}">
                                    <span class="text-truncate pr-2" title="Settings">{{ translate('settings')}}</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:" data-toggle="modal" data-target="#sign-out-modal">
                                    <span class="text-truncate pr-2" title="{{translate('logout')}}">{{translate('logout')}}</span>
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>
</div>
<div id="headerFluid" class="d-none"></div>
<div id="headerDouble" class="d-none"></div>