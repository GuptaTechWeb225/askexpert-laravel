@extends('layouts.front-end.app')

@section('title', translate('my_Questions'))

@section('content')

<div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
    <div class="row">
        @include('web-views.partials._profile-aside')
        <section class="col-lg-9 __customer-profile px-0">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-0 mb-md-3">
                        <h5 class="font-bold mb-0 fs-16">{{ translate('my_Questions') }}</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table __table __table-2 text-center">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-nowrap">SL</th>
                                    <th class="text-nowrap">Question Title</th>
                                    <th class="text-nowrap">Date Asked</th>
                                    <th class="text-nowrap">Category</th>
                                    <th class="text-nowrap">Expert Assigned</th>
                                    <th class="text-nowrap">Status</th>
                                    <th class="text-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($questions as $index => $question)
                                <tr>
                                    <td>{{ $questions->firstItem() + $index }}</td>
                                    <td>{{ Str::limit(optional($question->firstMessage)->message, 30) }}</td>
                                    <td>{{ $question->started_at->format('M d, Y') }}</td>
                                    <td>{{ $question->category?->name ?? '-' }}</td>
                                    <td>{{ optional($question->expert)->f_name . ' ' . optional($question->expert)->l_name ?? 'Not Assigned' }}</td>
                                    <td>
                                        <span class="badge 
                                    {{ $question->status == 'active' ? 'bg-success' : 
                                       ($question->status == 'pending' ? 'bg-warning' : 'bg-secondary') }}">
                                            {{ ucfirst($question->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('chat.view', $question->id) }}" class="btn btn-sm view-btn">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(count($questions)==0)
                    @include('layouts.back-end._empty-state',['text'=>'No_Questions_Found'],['image'=>'default'])
                    @endif
                    <div class="custom-pagination-wrapper d-flex justify-content-end mt-4">
                        {{ $questions->appends(request()->except('chat_experts_page'))->links() }}
                    </div>
                </div>
            </div>
            <input type="hidden" id="notification_paginated_page" value="{{request('page')}}">
        </section>
    </div>
</div>
@endsection