<?php

namespace App\Http\Requests;

use App\Traits\CalculatorTrait;
use App\Traits\ResponseHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class PlanRequest extends Request
{
    use CalculatorTrait, ResponseHandler;

    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|min:6|unique:plans,code,' . $this->plan?->id,
            'plan_type' => 'required|string|in:Free,Paid',
            'price' => 'required|numeric|min:0',
            'boosts' => 'required|string',
            'push_notification_count' => 'required|string',
            'duration' => 'required|integer|min:1',
            'mail_campaign_count' => 'required|integer|min:0',
            'plan_expiry_reminder' => 'required|integer|min:0',
            'addons' => 'nullable|array',
            'addons.*' => 'string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ];
    }

  
    public function messages(): array
    {
        return [
            'name.required' => 'Plan Name is required.',
            'code.required' => 'Plan code is required.',
            'code.unique' => 'Plan code must be unique.',
            'price.required' => 'Price is required.',
            'duration.required' => 'Plan duration is required.',
            'boosts.required' => 'Boosts duration is required.',
            'mail_campaign_count.required' => 'Mail campaign count is required.',
            'plan_expiry_reminder.required' => 'Plan expiry reminder is required.',
            'image.image' => 'Uploaded file must be an image.',
            'image.max' => 'Image size must not exceed 2MB.',
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $this->errorProcessor($validator)]));
    }
}
