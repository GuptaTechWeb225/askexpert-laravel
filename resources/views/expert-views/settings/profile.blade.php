@extends('layouts.back-end.app-expert') <!-- Ya jo bhi wholesale ke liye layout ho -->

@section('title', translate('Update Expert Profile'))
@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/css/intlTelInput.css') }}">
@endpush
@section('content')
<div class="content container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h3 class="page-title">{{ translate('Update Profile') }}</h3>

    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('expert.settings.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                 <div class="profile-cover">
                            @php($banner = dynamicAsset(path: 'public/assets/back-end/img/media/admin-profile-bg.png'))
                            <div class="profile-cover-img-wrapper profile-bg" style="background-image: url({{ $banner }})"></div>
                        </div>
                                <div class="avatar avatar-xxl avatar-circle avatar-border-lg avatar-uploader profile-cover-avatar">
    <img id="viewer" class="avatar-img"
         src="{{getStorageImages(path:auth('expert')->user()->image_full_url, type:'backend-profile')}}"
         alt="{{translate('image')}}">
         
    <!-- Hidden Input -->
    <input type="file" name="image" id="custom-file-upload" accept="image/*" capture="environment" class="d-none">
    
    <!-- Label triggers file input -->
    <label class="change-profile-image-icon" for="custom-file-upload">
        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-photo.png') }}" alt="">
    </label>
</div>

                                <h5 class="mt-3 mb-1">{{ auth('expert')->user()->f_name }} {{ auth('expert')->user()->l_name }}</h5>
                                <small class="text-muted">{{ auth('expert')->user()->email }}</small>
                            </div>

                            <!-- Form Fields -->
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <!-- First Name -->
                                    <div class="col-md-6">
                                        <label class="form-label">{{ translate('First Name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="f_name" class="form-control"
                                            value="{{ old('f_name', auth('expert')->user()->f_name) }}" required>
                                    </div>

                                    <!-- Last Name -->
                                    <div class="col-md-6">
                                        <label class="form-label">{{ translate('Last Name') }}</label>
                                        <input type="text" name="l_name" class="form-control"
                                            value="{{ old('l_name', auth('expert')->user()->l_name) }}">
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6">
                                        <label class="form-label">{{ translate('Email') }} <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ old('email', auth('expert')->user()->email) }}" readonly>
                                    </div>

                                    <!-- Phone -->
                                    <div class="col-md-6">
                                        <label class="form-label">{{ translate('Phone') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" class="form-control"
                                            value="{{ old('phone', auth('expert')->user()->phone) }}" required>
                                    </div>

                                    <!-- Country -->
                                    <div class="col-md-6">
                                        <label class="form-label">{{ translate('Country') }}</label>
                                        <input type="text" name="country" class="form-control"
                                            value="{{ old('country', auth('expert')->user()->country) }}">
                                    </div>

                                    <!-- State -->
                                    <div class="col-md-6">
                                        <label class="form-label">{{ translate('State') }}</label>
                                        <input type="text" name="state" class="form-control"
                                            value="{{ old('state', auth('expert')->user()->state) }}">
                                    </div>

                                    <!-- Category -->
                                    <div class="col-md-6">
                                        <label class="form-label">{{ translate('Category') }}</label>
                                        <select name="category_id" class="form-control" disabled>
                                            <option value="">{{ translate('Select Category') }}</option>
                                            @foreach(\App\Models\ExpertCategory::active()->get() as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('category_id', auth('expert')->user()->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Primary Specialty -->
                                    <div class="col-md-6">
                                        <label class="form-label">{{ translate('Primary Specialty') }}</label>
                                        <input type="text" name="primary_specialty" class="form-control"
                                            value="{{ old('primary_specialty', auth('expert')->user()->primary_specialty) }}">
                                    </div>

                                    <!-- Secondary Specialty -->
                                    <div class="col-md-6">
                                        <label class="form-label">{{ translate('Secondary Specialty') }}</label>
                                        <input type="text" name="secondary_specialty" class="form-control"
                                            value="{{ old('secondary_specialty', auth('expert')->user()->secondary_specialty) }}">
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="text-end mt-5">
                                    <button type="button" class="btn btn-secondary me-3" onclick="history.back()">
                                        {{ translate('Cancel') }}
                                    </button>
                                    <button type="submit" class="btn btn--primary px-5">
                                        {{ translate('Update Profile') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
$("#custom-file-upload").change(function() {
    readURL(this);
});

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#viewer').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

</script>
@endpush

@push('css')
<style>
    .profile-img-wrapper {
        position: relative;
        display: inline-block;
    }

    .change-photo-btn {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.6);
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.3s;
    }

    .change-photo-btn:hover {
        background: rgba(0, 0, 0, 0.8);
    }
</style>
@endpush