<?php

namespace App\Http\Requests\Api\V1\Referral;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrackReferralRequest extends FormRequest
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

        // Normalize referral_code to uppercase trimmed
        if ($this->has('referral_code')) {
            $data['referral_code'] = strtoupper(trim($this->input('referral_code')));
        }

        // Normalize prospect_email to lowercase trimmed
        if ($this->has('prospect_email') && $this->input('prospect_email')) {
            $data['prospect_email'] = strtolower(trim($this->input('prospect_email')));
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'referral_code' => ['required', 'string', 'max:32'],
            'prospect_external_ref' => ['nullable', 'string', 'max:150'],
            'prospect_phone' => ['nullable', 'string', 'max:30'],
            'prospect_email' => ['nullable', 'email', 'max:150'],
            'prospect_telegram_id' => ['nullable', 'string', 'max:100'],
            'click_id' => ['nullable', 'string', 'max:150'],
            'session_id' => ['nullable', 'string', 'max:150'],
            'device_fingerprint' => ['nullable', 'string', 'max:255'],
            'ip_address' => ['nullable', 'ip'],
            'user_agent' => ['nullable', 'string', 'max:500'],
            'attribution_source' => ['required', Rule::in(['link', 'manual_code', 'api', 'import'])],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check minimum identity requirement
            $identityFields = [
                'prospect_external_ref',
                'prospect_phone',
                'prospect_email',
                'prospect_telegram_id',
                'session_id',
            ];

            $hasIdentity = false;
            foreach ($identityFields as $field) {
                if (!empty($this->input($field))) {
                    $hasIdentity = true;
                    break;
                }
            }

            if (!$hasIdentity) {
                $validator->errors()->add('identity', 'At least one prospect identity field is required.');
            }
        });
    }
}
