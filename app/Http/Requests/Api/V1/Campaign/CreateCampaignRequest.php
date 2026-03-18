<?php

namespace App\Http\Requests\Api\V1\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class CreateCampaignRequest extends FormRequest
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
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper($this->code),
            ]);
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
            'code' => [
                'required',
                'string',
                'max:50',
                'unique:campaigns,code',
                'regex:/^[A-Z0-9_]+$/',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'status' => [
                'required',
                'in:draft,active,paused',
            ],
            'start_at' => [
                'nullable',
                'date',
            ],
            'end_at' => [
                'nullable',
                'date',
                'after:start_at',
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
            'code.unique' => 'Campaign code already exists.',
            'code.regex' => 'Campaign code must contain only uppercase letters, numbers, and underscores.',
            'end_at.after' => 'End date must be after start date.',
        ];
    }
}
