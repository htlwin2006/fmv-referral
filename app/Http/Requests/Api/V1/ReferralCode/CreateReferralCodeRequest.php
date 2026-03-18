<?php

namespace App\Http\Requests\Api\V1\ReferralCode;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateReferralCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        // Normalize campaign_code to uppercase
        if ($this->has('campaign_code')) {
            $data['campaign_code'] = strtoupper(trim($this->campaign_code));
        }

        // Normalize custom_code to uppercase
        if ($this->has('custom_code') && $this->custom_code !== null) {
            $data['custom_code'] = strtoupper(trim($this->custom_code));
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'referrer_user_id' => [
                'required',
                'string',
                'max:100',
            ],
            'referrer_account_id' => [
                'nullable',
                'string',
                'max:100',
            ],
            'campaign_code' => [
                'required',
                'string',
                'max:50',
            ],
            'code_type' => [
                'required',
                Rule::in(['system', 'custom']),
            ],
            'custom_code' => [
                'nullable',
                'string',
                'min:4',
                'max:32',
                'regex:/^[A-Z0-9_]+$/',
                Rule::requiredIf(fn () => $this->input('code_type') === 'custom'),
                Rule::prohibitedIf(fn () => $this->input('code_type') === 'system'),
            ],
            'metadata' => [
                'nullable',
                'array',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'custom_code.required' => 'Custom code is required when code type is custom.',
            'custom_code.regex' => 'Custom code must contain only uppercase letters, numbers, and underscores.',
            'custom_code.prohibited' => 'Custom code must not be provided when code type is system.',
        ];
    }
}
