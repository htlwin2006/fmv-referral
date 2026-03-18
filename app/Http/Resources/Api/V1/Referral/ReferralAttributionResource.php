<?php

namespace App\Http\Resources\Api\V1\Referral;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralAttributionResource extends JsonResource
{
    /**
     * The message to include in the response.
     *
     * @var string
     */
    protected string $message;

    /**
     * Set the message for the response.
     *
     * @param string $message
     * @return $this
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
            'data' => [
                'uuid' => $this->uuid,
                'referral_code' => $this->referralCode->referral_code,
                'referrer_user_id' => $this->referrer_user_id,
                'campaign' => [
                    'uuid' => $this->campaign->uuid,
                    'code' => $this->campaign->code,
                    'name' => $this->campaign->name,
                    'status' => $this->campaign->status,
                ],
                'prospect' => [
                    'external_ref' => $this->prospect_external_ref,
                    'phone' => $this->prospect_phone,
                    'email' => $this->prospect_email,
                    'telegram_id' => $this->prospect_telegram_id,
                ],
                'tracking' => [
                    'click_id' => $this->click_id,
                    'session_id' => $this->session_id,
                    'device_fingerprint' => $this->device_fingerprint,
                    'ip_address' => $this->ip_address,
                    'user_agent' => $this->user_agent,
                    'attribution_source' => $this->attribution_source,
                ],
                'attribution_status' => $this->attribution_status,
                'attributed_at' => $this->attributed_at?->toIso8601String(),
                'expires_at' => $this->expires_at?->toIso8601String(),
                'metadata' => $this->metadata,
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ],
            'message' => $this->message ?? 'Referral attribution captured successfully.',
        ];
    }
}
