<?php

namespace App\Http\Controllers\Api\V1\Referral;

use App\Domain\Referral\Services\ReferralAcquisitionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Referral\AccountOpenedRequest;
use App\Http\Resources\Api\V1\Referral\ReferralAcquisitionResource;
use Illuminate\Http\JsonResponse;

class AccountOpenedController extends Controller
{
    public function __construct(
        protected ReferralAcquisitionService $acquisitionService
    ) {
    }

    /**
     * Link a created user account to a referral attribution.
     */
    public function __invoke(AccountOpenedRequest $request): JsonResponse
    {
        try {
            $result = $this->acquisitionService->linkAccount($request->validated());

            $message = $result['was_created']
                ? 'Referral acquisition linked successfully.'
                : 'Existing referral acquisition returned.';

            $resource = (new ReferralAcquisitionResource($result['acquisition']))
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
                'message' => 'Unable to link referral acquisition.',
            ], 500);
        }
    }
}
