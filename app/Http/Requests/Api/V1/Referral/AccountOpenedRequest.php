<?php

namespace App\Http\Requests\Api\V1\Referral;

use Illuminate\Foundation\Http\FormRequest;

class AccountOpenedRequest extends FormRequest
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

        // Normalize referral_code to uppercase trimmed if present
        if ($this->has('referral_code') && $this->input('referral_code')) {
            $data['referral_code'] = strtoupper(trim($this->input('referral_code')));
        }

        // Normalize prospect_email to lowercase trimmed if present
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
            'acquired_user_id' => ['required', 'string', 'max:100'],
            'acquired_account_id' => ['nullable', 'string', 'max:100'],
            'acquired_customer_id' => ['nullable', 'string', 'max:100'],
            'account_opened_at' => ['required', 'date'],
            'attribution_uuid' => ['nullable', 'uuid'],
            'referral_code' => ['nullable', 'string', 'max:32'],
            'prospect_phone' => ['nullable', 'string', 'max:30'],
            'prospect_email' => ['nullable', 'email', 'max:150'],
            'prospect_telegram_id' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check that at least one method to resolve attribution exists
            $hasAttributionUuid = !empty($this->input('attribution_uuid'));
            $hasReferralCode = !empty($this->input('referral_code'));
            $hasProspectIdentity = !empty($this->input('prospect_phone')) 
                || !empty($this->input('prospect_email'))
                || !empty($this->input('prospect_telegram_id'));

            if (!$hasAttributionUuid && !($hasReferralCode && $hasProspectIdentity)) {
                $validator->errors()->add(
                    'attribution_resolution',
                    'Either attribution_uuid or (referral_code + prospect identity) must be provided.'
                );
            }
        });
    }
}
