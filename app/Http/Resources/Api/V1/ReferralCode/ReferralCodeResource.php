<?php

namespace App\Http\Resources\Api\V1\ReferralCode;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralCodeResource extends JsonResource
{
    /**
     * Custom message for the response
     */
    protected string $message = 'Referral code created successfully.';

    /**
     * Set custom message
     */
    public function withMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'referrer_user_id' => $this->referrer_user_id,
            'referrer_account_id' => $this->referrer_account_id,
            'referral_code' => $this->referral_code,
            'code_type' => $this->code_type,
            'status' => $this->status,
            'used_count' => $this->used_count,
            'max_usage_count' => $this->max_usage_count,
            'campaign' => [
                'uuid' => $this->campaign->uuid,
                'code' => $this->campaign->code,
                'name' => $this->campaign->name,
                'status' => $this->campaign->status,
            ],
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'message' => $this->message,
        ];
    }
}
