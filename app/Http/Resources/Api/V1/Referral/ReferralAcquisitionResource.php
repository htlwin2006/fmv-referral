<?php

namespace App\Http\Resources\Api\V1\Referral;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReferralAcquisitionResource extends JsonResource
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
                'referrer_user_id' => $this->referrer_user_id,
                'acquired_user_id' => $this->acquired_user_id,
                'acquired_account_id' => $this->acquired_account_id,
                'acquired_customer_id' => $this->acquired_customer_id,
                'acquisition_status' => $this->acquisition_status,
                'account_opened_at' => $this->account_opened_at?->toIso8601String(),
                'linked_at' => $this->linked_at?->toIso8601String(),
                'metadata' => $this->metadata,
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ],
            'message' => $this->message ?? 'Referral acquisition linked successfully.',
        ];
    }
}
