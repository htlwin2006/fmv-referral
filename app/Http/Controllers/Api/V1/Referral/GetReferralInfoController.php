<?php

namespace App\Http\Controllers\Api\V1\Referral;

use App\Domain\Referral\Services\ReferralAttributionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Referral\ReferralAttributionResource;
use Illuminate\Http\JsonResponse;

class GetReferralInfoController extends Controller
{
    public function __construct(
        protected ReferralAttributionService $attributionService
    ) {
    }

    /**
     * Get referral information by prospect Telegram ID.
     */
    public function __invoke(string $telegramId): JsonResponse
    {
        $attribution = $this->attributionService->getReferralByTelegramId($telegramId);

        if (!$attribution) {
            return response()->json([
                'message' => 'Referral attribution not found.',
            ], 404);
        }

        return (new ReferralAttributionResource($attribution))
            ->toResponse(request());
    }
}
