@extends('layouts.front-end.app')

@section('title', translate('my_experts'))

@section('content')

<div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
    <div class="row">
        @include('web-views.partials._profile-aside')
        <section class="col-lg-9 __customer-profile px-0">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-0 mb-md-3">
                        <h5 class="font-bold mb-0 fs-16">{{ translate('my_Experts') }}</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table __table __table-2 text-center">
                            <thead class="thead-light">
                                <tr>
                                    <th>SL</th>
                                    <th>Expert Assigned</th>
                                    <th>Date Asked</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Favorites</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chatExperts as $index => $chat)
                                <tr>
                                    <td>{{ $chatExperts->firstItem() + $index }}</td>
                                    <td>{{ $chat->expert->f_name ?? '-' }} {{ $chat->expert->l_name ?? '' }}</td>
                                    <td>{{ $chat->started_at?->format('M d, Y') ?? '-' }}</td>
                                    <td>{{ $chat->category->name ?? 'General' }}</td>
                                    <td>
                                        <span class="badge {{ $chat->status == 'active' ? 'bg-success' : ($chat->status == 'pending' ? 'bg-warning' : 'bg-secondary') }}">
                                            {{ ucfirst($chat->status) }}
                                        </span>
                                    </td>
                                    <td class="text-danger"><i class="fa-solid fa-heart"></i></td>
                                    <td>
                                        <a href="{{ route('chat.view', $chat->id) }}" class="btn btn-sm view-btn btn-outline-accent">
                                            <i class="fa-solid fa-message"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
  @if(count($chatExperts)==0)
                    @include('layouts.back-end._empty-state',['text'=>'No_Expert_Found'],['image'=>'default'])
                    @endif
                    <div class="custom-pagination-wrapper d-flex justify-content-end mt-4">
                        {{ $chatExperts->appends(request()->except('chat_experts_page'))->links() }}
                    </div>
                </div>
            </div>
            <input type="hidden" id="notification_paginated_page" value="{{request('page')}}">
        </section>
    </div>
</div>
@endsection