<?php

namespace App\Domain\Referral\Services;

use App\Domain\Referral\Exceptions\CampaignCodeAlreadyExistsException;
use App\Domain\Referral\Models\Campaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampaignService
{
    /**
     * Create a new campaign
     *
     * @param array $data
     * @return Campaign
     * @throws CampaignCodeAlreadyExistsException
     */
    public function createCampaign(array $data): Campaign
    {
        // Check if code already exists
        if (Campaign::where('code', $data['code'])->exists()) {
            throw new CampaignCodeAlreadyExistsException();
        }

        return DB::transaction(function () use ($data) {
            // Generate UUID
            $data['uuid'] = (string) Str::uuid();

            // Create campaign
            return Campaign::create($data);
        });
    }
}
