{{-- resources/views/admin/expert-category/edit.blade.php --}}
@extends('layouts.back-end.app')
@section('title', translate('edit_expert_category') . ' - ' . $category->name)

@section('content')
<div class="content container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
            <img src="{{ dynamicAsset('public/assets/back-end/img/expert-category.png') }}" alt="">
            {{ translate('edit_expert_category') }}
        </h2>
        <a href="{{ route('admin.expert-category.index') }}" class="btn btn--secondary">
            <i class="tio-back-ui"></i> {{ translate('back') }}
        </a>
    </div>

    <form action="{{ route('admin.expert-category.update', $category->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0 text-capitalize d-flex gap-1">
                    <i class="tio-category"></i>
                    {{ translate('category_details') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="title-color">{{ translate('name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}"
                                placeholder="{{ translate('enter_category_name') }}" required>
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="title-color">{{ translate('icon') }}</label>
                            <div class="d-flex align-items-center gap-3">
                                @if($category->icon_url)
                                <img src="{{$category->icon_url}}" width="60" class="border rounded" alt="icon">
                                @else
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                                    <i class="tio-image text-muted"></i>
                                </div>
                                @endif
                                <input type="file" name="icon" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="title-color">{{ translate('primary_specialty') }} <span class="text-danger">*</span></label>
                            <input type="text" name="primary_specialty" class="form-control"
                                value="{{ old('primary_specialty', $category->primary_specialty) }}"
                                placeholder="{{ translate('e.g._Cardiologist') }}" required>
                            @error('primary_specialty') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="title-color">{{ translate('description') }}</label>
                            <textarea name="description" class="form-control" rows="3"
                                placeholder="{{ translate('brief_description_about_this_expert_category') }}">{{ old('description', $category->description) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="title-color">{{ translate('free_follow_up_duration') }} ({{ translate('days') }})</label>
                            <input type="number" name="free_follow_up_duration" class="form-control"
                                value="{{ old('free_follow_up_duration', $category->free_follow_up_duration) }}" min="0"
                                placeholder="{{ translate('e.g._7') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="title-color">{{ translate('status') }}</label>
                            <select name="is_active" class="form-control">
                                <option value="1" {{ old('is_active', $category->is_active) ? 'selected' : '' }}>{{ translate('active') }}</option>
                                <option value="0" {{ old('is_active', $category->is_active) ? '' : 'selected' }}>{{ translate('inactive') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Sub Categories -->
                    <div class="col-12 mt-3">
                        <div class="form-group">
                            <label class="title-color">
                                {{ translate('sub_categories') }}
                                <small class="text-muted d-block">
                                    {{ translate('separate_with_comma') }}: e.g. Heart Checkup, Blood Pressure, ECG
                                </small>
                            </label>
                            <textarea name="sub_categorys" class="form-control" rows="3"
                                placeholder="{{ translate('e.g._Heart_Checkup,_Blood_Pressure,_ECG') }}">
                            {{ old('sub_categorys', is_array($category->sub_categorys) ? implode(', ', $category->sub_categorys) : '') }}
                            </textarea>
                            @error('sub_categorys') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Prices -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0 text-capitalize d-flex gap-1">
                    <i class="tio-money"></i>
                    {{ translate('pricing_details') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="title-color">{{ translate('monthly_subscription_fee') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="monthly_subscription_fee" class="form-control"
                                value="{{ old('monthly_subscription_fee', $category->monthly_subscription_fee) }}" required
                                placeholder="$99.99">
                            @error('monthly_subscription_fee') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="title-color">{{ translate('expert_fee') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="expert_fee" class="form-control"
                                value="{{ old('expert_fee', $category->expert_fee) }}" required
                                placeholder="$150.00">
                            @error('expert_fee') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="title-color">{{ translate('joining_fee') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="joining_fee" class="form-control"
                                value="{{ old('joining_fee', $category->joining_fee) }}" required
                                placeholder="$49.99">
                            @error('joining_fee') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="title-color">{{ translate('is_refundable') }}</label>
                            <select name="is_refundable" class="form-control" required>
                                <option value="1" {{ old('is_refundable', $category->is_refundable) ? 'selected' : '' }}>{{ translate('yes') }}</option>
                                <option value="0" {{ old('is_refundable', $category->is_refundable) ? '' : 'selected' }}>{{ translate('no') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: CMS Details -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0 text-capitalize d-flex gap-1">
                    <i class="tio-web"></i>
                    {{ translate('cms_details') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="title-color">{{ translate('cms_heading') }} <span class="text-danger">*</span></label>
                            <input type="text" name="cms_heading" class="form-control"
                                value="{{ old('cms_heading', $category->cms_heading) }}"
                                placeholder="{{ translate('e.g._Expert_Cardiology_Services') }}" required>
                            @error('cms_heading') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="title-color">{{ translate('cms_image') }}</label>
                            <div class="d-flex align-items-center gap-3">
                                @if($category->cms_image_url)
                                <img src="{{ $category->cms_image_url }}" width="120" class="border rounded" alt="cms image">
                                @else
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width:120px;height:60px;">
                                    <i class="tio-image text-muted"></i>
                                </div>
                                @endif
                                <input type="file" name="cms_image" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="title-color">{{ translate('cms_description') }}</label>
                            <textarea name="cms_description" class="form-control" rows="4"
                                placeholder="{{ translate('detailed_description_for_website_landing_page') }}">{{ old('cms_description', $category->cms_description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3">
           <div class="card-header">
                <h5 class="mb-0 text-capitalize d-flex gap-1">
                    <i class="tio-web"></i>
                    {{ translate('category_card_details') }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="title-color">{{ translate('card_title') }} <span class="text-danger">*</span></label>
                            <input type="text" name="card_description" class="form-control"
                                value="{{ old('card_description', $category->card_description) }}"
                                placeholder="{{ translate('e.g._Expert_Cardiology_Services') }}" required>
                            @error('card_description') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">    
                            <label class="title-color">{{ translate('card_image') }}</label>
                            <div class="d-flex align-items-center gap-3">
                                @if($category->card_image_url)
                                <img src="{{ $category->card_image_url }}" width="120" class="border rounded" alt="card image">
                                @else
                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width:120px;height:60px;">
                                    <i class="tio-image text-muted"></i>
                                </div>
                                @endif
                                <input type="file" name="card_image" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                  
                </div>
            </div>
        </div>
<!-- Expert Payout Section -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0 text-capitalize d-flex gap-1">
            <i class="tio-money-vs"></i>
            {{ translate('expert_payout') }}
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="title-color">
                        {{ translate('expert_basic_payout') }} 
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="0.01" name="expert_basic" class="form-control"
                        value="{{ old('expert_basic', $category->expert_basic) }}" required
                        placeholder="e.g. 70">
                    <small class="text-muted">{{ translate('amount_of_expert_fee_that_basic_expert_gets') }}</small>
                    @error('expert_basic') <span class="text-danger d-block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="title-color">
                        {{ translate('expert_premium_payout') }} 
                        <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="0.01" name="expert_premium" class="form-control"
                        value="{{ old('expert_premium', $category->expert_premium) }}" required
                        placeholder="e.g. 85">
                    <small class="text-muted">{{ translate('amount_of_expert_fee_that_premium_expert_gets') }}</small>
                    @error('expert_premium') <span class="text-danger d-block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>
</div>
        <div class="d-flex justify-content-end gap-3">
            <a href="{{ route('admin.expert-category.index') }}" class="btn btn--secondary">{{ translate('cancel') }}</a>
            <button type="submit" class="btn btn--primary px-4">{{ translate('update') }}</button>
        </div>
    </form>
</div>
@endsection