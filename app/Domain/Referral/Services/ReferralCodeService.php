<?php

namespace App\Domain\Referral\Services;

use App\Domain\Referral\Exceptions\CampaignNotAvailableException;
use App\Domain\Referral\Exceptions\CampaignNotFoundException;
use App\Domain\Referral\Exceptions\ReferralCodeAlreadyExistsException;
use App\Domain\Referral\Models\Campaign;
use App\Domain\Referral\Models\ReferralCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralCodeService
{
    /**
     * Create a new referral code or return existing active one
     *
     * @param array $data
     * @return array ['referral_code' => ReferralCode, 'was_created' => bool]
     * @throws CampaignNotFoundException
     * @throws CampaignNotAvailableException
     * @throws ReferralCodeAlreadyExistsException
     */
    public function createOrReuseCode(array $data): array
    {
        // Find campaign by code
        $campaign = Campaign::where('code', $data['campaign_code'])->first();

        if (!$campaign) {
            throw new CampaignNotFoundException();
        }

        // Validate campaign availability
        $this->validateCampaignAvailability($campaign);

        // Check for existing active referral code
        $existingCode = ReferralCode::where('referrer_user_id', $data['referrer_user_id'])
            ->where('campaign_id', $campaign->id)
            ->where('status', 'active')
            ->first();

        if ($existingCode) {
            return [
                'referral_code' => $existingCode,
                'was_created' => false,
            ];
        }

        // Determine the referral code to use
        if ($data['code_type'] === 'custom') {
            $referralCode = $data['custom_code'];
            
            // Validate custom code uniqueness
            if (ReferralCode::where('referral_code', $referralCode)->exists()) {
                throw new ReferralCodeAlreadyExistsException();
            }
        } else {
            // Generate unique system code
            $referralCode = $this->generateUniqueReferralCode($data['referrer_user_id']);
        }

        // Create new referral code in transaction
        $newCode = DB::transaction(function () use ($data, $campaign, $referralCode) {
            return ReferralCode::create([
                'uuid' => (string) Str::uuid(),
                'campaign_id' => $campaign->id,
                'referrer_user_id' => $data['referrer_user_id'],
                'referrer_account_id' => $data['referrer_account_id'] ?? null,
                'referral_code' => $referralCode,
                'code_type' => $data['code_type'],
                'status' => 'active',
                'used_count' => 0,
                'metadata' => $data['metadata'] ?? null,
            ]);
        });

        return [
            'referral_code' => $newCode,
            'was_created' => true,
        ];
    }

    /**
     * Validate if campaign is available for code generation
     *
     * @param Campaign $campaign
     * @return void
     * @throws CampaignNotAvailableException
     */
    private function validateCampaignAvailability(Campaign $campaign): void
    {
        // Check status
        if (!in_array($campaign->status, ['draft', 'active', 'paused'])) {
            throw new CampaignNotAvailableException(
                'Campaign status does not allow referral code generation.'
            );
        }

        $now = now();

        // Check start date
        if ($campaign->start_at && $now->isBefore($campaign->start_at)) {
            throw new CampaignNotAvailableException('Campaign is not yet active.');
        }

        // Check end date
        if ($campaign->end_at && $now->isAfter($campaign->end_at)) {
            throw new CampaignNotAvailableException('Campaign has already ended.');
        }
    }

    /**
     * Generate a unique referral code
     *
     * @param string $referrerUserId
     * @return string
     */
    private function generateUniqueReferralCode(string $referrerUserId): string
    {
        $maxAttempts = 10;
        $attempt = 0;

        do {
            // Extract prefix from user ID (first 3 chars, uppercase, alphanumeric only)
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $referrerUserId), 0, 3));
            
            // Generate random suffix (5 characters)
            $suffix = strtoupper(Str::random(5));
            
            $code = $prefix . $suffix;
            
            // Ensure it's only alphanumeric
            $code = preg_replace('/[^A-Z0-9]/', '', $code);
            
            // Pad if too short
            if (strlen($code) < 8) {
                $code .= strtoupper(Str::random(8 - strlen($code)));
            }

            $attempt++;

            // Check uniqueness
            if (!ReferralCode::where('referral_code', $code)->exists()) {
                return $code;
            }
        } while ($attempt < $maxAttempts);

        // Fallback: pure random 10 character code
        do {
            $code = strtoupper(Str::random(10));
            $code = preg_replace('/[^A-Z0-9]/', '', $code);
        } while (ReferralCode::where('referral_code', $code)->exists());

        return $code;
    }
}
