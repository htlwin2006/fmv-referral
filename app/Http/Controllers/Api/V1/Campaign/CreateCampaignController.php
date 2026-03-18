<?php

namespace App\Http\Controllers\Api\V1\Campaign;

use App\Domain\Referral\Exceptions\CampaignCodeAlreadyExistsException;
use App\Domain\Referral\Services\CampaignService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Campaign\CreateCampaignRequest;
use App\Http\Resources\Api\V1\Campaign\CampaignResource;
use Illuminate\Http\JsonResponse;

class CreateCampaignController extends Controller
{
    public function __construct(
        private readonly CampaignService $campaignService
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(CreateCampaignRequest $request): CampaignResource|JsonResponse
    {
        try {
            $campaign = $this->campaignService->createCampaign($request->validated());

            return (new CampaignResource($campaign))
                ->response()
                ->setStatusCode(201);
        } catch (CampaignCodeAlreadyExistsException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
