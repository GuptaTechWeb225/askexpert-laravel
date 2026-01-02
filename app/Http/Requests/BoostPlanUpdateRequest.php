<?php

namespace App\Http\Requests;

use App\Traits\CalculatorTrait;
use App\Traits\ResponseHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;


class BoostPlanUpdateRequest extends Request
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

        $rules = [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'regex:/^[a-zA-Z0-9]+$/',
                'min:6',
                'max:20',
                Rule::unique('plans', 'code')->ignore($this->id, 'id'),
            ],
            'boost_type' => 'required|in:Boost,Email,Notification',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ];

        if ($this->boost_type === 'Notification') {
            $rules['notification_count'] = 'required|integer|min:1';
        } elseif ($this->boost_type === 'Boost') {
            $rules['boost_count'] = 'required|integer|min:1';
        } elseif ($this->boost_type === 'Email') {
            $rules['email_count'] = 'required|integer|min:1';
        }

        return $rules;
    }

    /**
     * Customize error messages if needed
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Plan Name is required.',
            'code.required' => 'Plan code is required.',
            'code.unique' => 'Plan code must be unique.',
            'price.required' => 'Price is required.',
            'duration.required' => 'Plan duration is required.',
            'count.required' => 'Count is required.',
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
