@php
use App\Models\Expert;
use Illuminate\Support\Facades\Session;
use App\Utils\Notifications;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
$expert= Expert::find(auth('expert')->id());
$expertId = auth('expert')->id();
$notifications = Notifications::getExpertNotifications($expertId, [0]);

@endphp
@php($direction = Session::get('direction'))

<style>
    #notificationList .dropdown-item {
        white-space: normal !important;
        /* text wrap allow */
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
        /* parent width ke andar hi रहे */
    }
</style>
<div id="headerMain" class="d-none">
    <header id="header"
        class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered">
        <div class="navbar-nav-wrap">
            <div class="navbar-brand-wrapper d-none d-sm-block d-xl-none">
                @php($ecommerceLogo = getWebConfig('company_web_logo'))
                <a class="navbar-brand" href="{{route('expert.dashboard.index')}}" aria-label="">
                    <img class="navbar-brand-logo"
                        src="{{ getStorageImages($ecommerceLogo, type: 'backend-logo')}}" alt="{{ translate('logo') }}">
                    <img class="navbar-brand-logo-mini"
                        src="{{getStorageImages($ecommerceLogo, type: 'backend-logo')}}"
                        alt="{{ translate('logo') }}">
                </a>
            </div>
            <div class="navbar-nav-wrap-content-left">
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-sm-3 d-xl-none">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                        data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                        data-toggle="tooltip" data-placement="right" title="Expand"></i>
                </button>
                <div class="d-none">
                    <form class="position-relative">
                    </form>
                </div>
            </div>
            <div class="navbar-nav-wrap-content-right"
                style="{{$direction === "rtl" ? 'margin-left:unset; margin-right: auto' : 'margin-right:unset; margin-left: auto'}}">
                <ul class="navbar-nav align-items-center flex-row gap-xl-16px">
                      <li class="nav-item">
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle"
                                href="{{route('expert.questions.all')}}" title="{{translate('message')}}" data-toggle="tooltip" data-custom-class="header-icon-title">
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
                                        src="{{getStorageImages(path: auth('expert')->user()->image_full_url,type: 'backend-profile')}}"
                                        alt="{{translate('image_description')}}">
                                    <span class="d-none avatar-status avatar-sm-status avatar-status-success"></span>
                                </div>
                                <div class="d-none d-md-block media-body text-right">
                                    <h5 class="profile-name mb-0">{{auth('expert')->user()->f_name }}</h5>
                                    <span class="fz-12">Expert</span>
                                </div>
                            </a>
                            <div id="accountNavbarDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account __w-16rem">
                                <div class="dropdown-item-text">
                                    <div class="media align-items-center text-break">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img"
                                                src="{{getStorageImages(path:$expert->image_full_url,type:'backend-profile')}}"
                                                alt="{{translate('image_description')}}">
                                        </div>
                                        <div class="media-body">
                                            <span class="card-title h5">{{$expert->f_name}}</span>

                                            <span class="card-text text-nowrap">{{$expert->email}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>

                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:" data-toggle="modal"
                                    data-target="#sign-out-modal">
                                    <span class="text-truncate pr-2"
                                        title="{{translate('logout')}}">{{translate('logout')}}</span>
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