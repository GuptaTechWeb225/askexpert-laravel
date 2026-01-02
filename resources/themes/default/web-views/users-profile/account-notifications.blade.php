@extends('layouts.front-end.app')

@section('title', translate('my_notifications'))

@section('content')

<div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
    <div class="row">
        @include('web-views.partials._profile-aside')
        <section class="col-lg-9 __customer-profile px-0">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-0 mb-md-3">
                        <h5 class="font-bold mb-0 fs-16">{{ translate('notifications') }}</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table __table __table-2 text-center">
                            <thead class="thead-light">
                                <tr>
                                    <td class="tdBorder">{{translate('SL')}}</td>
                                    <td class="tdBorder">{{translate('notification_title')}}</td>
                                    <td class="tdBorder">{{translate('notification_message')}}</td>
                                    <td class="tdBorder">{{translate('notification_receiveds')}}</td>
                                    <td class="tdBorder">{{translate('action')}}</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                use Carbon\Carbon;

                                ($notifications = \App\Utils\Notifications::getUserNotifications(auth('customer')->id(), 10));
                                ?>
                                @php $i = 1; @endphp
                                @foreach($notifications as $notification)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ Str::limit($notification->title, 40, '...') }}</td>
                                    <td>{{ Str::limit($notification->message, 40, '...') }}</td>
                                    <td>
                                        <?php
                                        if (!empty($notification->created_at)) {
                                            $createdAt = Carbon::parse($notification->created_at);
                                            echo ($createdAt->diffInDays(Carbon::now()) < 7)
                                                ? $createdAt->format('D h:i A')
                                                : $createdAt->format('d M Y h:i A');
                                        } else {
                                            echo "N/A";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="__btn-grp-sm flex-nowrap">

                                            <a href="{{route('notification.view',$notification->id)}}" title="View notification" class="__action-btn btn btn-outline-accent btn-shadow btn-sm rounded-full">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(count($notifications)==0)
                    @include('layouts.back-end._empty-state',['text'=>'No_Notification_Found'],['image'=>'default'])
                    @endif
                </div>
            </div>
            <input type="hidden" id="notification_paginated_page" value="{{request('page')}}">
        </section>
    </div>
</div>
@endsection