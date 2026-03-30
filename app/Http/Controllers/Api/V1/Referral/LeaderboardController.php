<?php

namespace App\Http\Controllers\Api\V1\Referral;

use App\Domain\Referral\Services\ReferralAttributionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Referral\LeaderboardResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LeaderboardController extends Controller
{
    public function __construct(
        protected ReferralAttributionService $attributionService
    ) {
    }

    /**
     * Get referral leaderboard with captured and linked counts.
     */
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $limit = $request->query('limit', null);
        
        if ($limit !== null) {
            $limit = (int) $limit;
            if ($limit < 1 || $limit > 1000) {
                $limit = 100; // Default safe limit
            }
        }

        $leaderboard = $this->attributionService->getLeaderboard($limit);

        return LeaderboardResource::collection($leaderboard);
    }
}
