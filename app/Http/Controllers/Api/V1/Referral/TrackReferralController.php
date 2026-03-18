<?php

namespace App\Http\Controllers\Api\V1\Referral;

use App\Domain\Referral\Services\ReferralAttributionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Referral\TrackReferralRequest;
use App\Http\Resources\Api\V1\Referral\ReferralAttributionResource;
use Illuminate\Http\JsonResponse;

class TrackReferralController extends Controller
{
    public function __construct(
        protected ReferralAttributionService $attributionService
    ) {
    }

    /**
     * Capture referral attribution before account creation.
     */
    public function __invoke(TrackReferralRequest $request): JsonResponse
    {
        try {
            $result = $this->attributionService->createOrReuseAttribution($request->validated());

            $message = $result['was_created']
                ? 'Referral attribution captured successfully.'
                : 'Existing referral attribution returned.';

            $resource = (new ReferralAttributionResource($result['attribution']))
                ->withMessage($message);

            $statusCode = $result['was_created'] ? 201 : 200;

            return $resource->toResponse($request)
                ->setStatusCode($statusCode);
        } catch (\Exception $e) {
            // If exception doesn't have render method, return generic error
            if (method_exists($e, 'render')) {
                throw $e;
            }

            return response()->json([
                'message' => 'Unable to capture referral attribution.',
            ], 500);
        }
    }
}
