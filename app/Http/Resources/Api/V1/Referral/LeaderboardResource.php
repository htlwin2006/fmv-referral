<?php

namespace App\Http\Resources\Api\V1\Referral;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'referrer_user_id' => $this->referrer_user_id,
            'captured_count' => (int) $this->captured_count,
            'linked_count' => (int) $this->linked_count,
            'total_count' => (int) $this->total_count,
        ];
    }
}
