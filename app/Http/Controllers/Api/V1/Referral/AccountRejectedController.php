<?php

namespace App\Http\Controllers\Api\V1\Referral;

use App\Domain\Referral\Services\ReferralAcquisitionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Referral\AccountRejectedRequest;
use App\Http\Resources\Api\V1\Referral\ReferralAttributionResource;
use Illuminate\Http\JsonResponse;

class AccountRejectedController extends Controller
{
    public function __construct(
        protected ReferralAcquisitionService $acquisitionService
    ) {
    }

    /**
     * Reject an account and mark the attribution as rejected.
     */
    public function __invoke(AccountRejectedRequest $request): JsonResponse
    {
        try {
            $result = $this->acquisitionService->rejectAccount($request->validated());

            $message = $result['was_rejected']
                ? 'Referral attribution rejected successfully.'
                : 'Attribution already rejected.';

            $resource = (new ReferralAttributionResource($result['attribution']))
                ->withMessage($message);

            $statusCode = $result['was_rejected'] ? 200 : 200;

            return $resource->toResponse($request)
                ->setStatusCode($statusCode);
        } catch (\Exception $e) {
            // If exception doesn't have render method, return generic error
            if (method_exists($e, 'render')) {
                throw $e;
            }

            return response()->json([
                'message' => 'Unable to reject referral attribution.',
            ], 500);
        }
    }
}
