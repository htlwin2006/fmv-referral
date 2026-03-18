<?php

namespace App\Http\Controllers\Api\V1\ReferralCode;

use App\Domain\Referral\Exceptions\CampaignNotAvailableException;
use App\Domain\Referral\Exceptions\CampaignNotFoundException;
use App\Domain\Referral\Exceptions\ReferralCodeAlreadyExistsException;
use App\Domain\Referral\Services\ReferralCodeService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReferralCode\CreateReferralCodeRequest;
use App\Http\Resources\Api\V1\ReferralCode\ReferralCodeResource;
use Illuminate\Http\JsonResponse;

class CreateReferralCodeController extends Controller
{
    public function __construct(
        private readonly ReferralCodeService $referralCodeService
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(CreateReferralCodeRequest $request): ReferralCodeResource|JsonResponse
    {
        try {
            $result = $this->referralCodeService->createOrReuseCode($request->validated());

            $resource = new ReferralCodeResource($result['referral_code']);

            if ($result['was_created']) {
                return $resource
                    ->withMessage('Referral code created successfully.')
                    ->response()
                    ->setStatusCode(201);
            } else {
                return $resource
                    ->withMessage('Existing active referral code returned.')
                    ->response()
                    ->setStatusCode(200);
            }
        } catch (CampaignNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        } catch (CampaignNotAvailableException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        } catch (ReferralCodeAlreadyExistsException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unable to create referral code.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
