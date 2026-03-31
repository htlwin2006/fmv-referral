<?php

namespace App\Domain\Referral\Services;

use App\Domain\Referral\Exceptions\ReferralCodeNotAvailableException;
use App\Domain\Referral\Exceptions\ReferralCodeNotFoundException;
use App\Domain\Referral\Models\ReferralAttribution;
use App\Domain\Referral\Models\ReferralCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralAttributionService
{
    /**
     * Create or reuse an existing referral attribution.
     *
     * @param array $data Validated attribution data
     * @return array ['attribution' => ReferralAttribution, 'was_created' => bool]
     * @throws ReferralCodeNotFoundException
     * @throws ReferralCodeNotAvailableException
     */
    public function createOrReuseAttribution(array $data): array
    {
        // Find referral code by code string
        $referralCode = ReferralCode::where('referral_code', $data['referral_code'])
            ->whereNull('deleted_at')
            ->first();

        if (!$referralCode) {
            throw new ReferralCodeNotFoundException();
        }

        // Validate referral code status
        if ($referralCode->status !== 'active') {
            throw new ReferralCodeNotAvailableException('Referral code is not active.');
        }

        // Load related campaign
        $campaign = $referralCode->campaign;

        if (!$campaign) {
            throw new ReferralCodeNotAvailableException('Campaign not found for referral code.');
        }

        // Validate campaign timing window
        $now = now();

        if ($campaign->start_at && $now->lt($campaign->start_at)) {
            throw new ReferralCodeNotAvailableException('Campaign is not yet active.');
        }

        if ($campaign->end_at && $now->gt($campaign->end_at)) {
            throw new ReferralCodeNotAvailableException('Campaign has already ended.');
        }

        // Search for existing reusable attribution
        $existingAttribution = $this->findExistingAttribution($referralCode->id, $data);

        if ($existingAttribution) {
            return [
                'attribution' => $existingAttribution,
                'was_created' => false,
            ];
        }

        // Create new attribution
        $attribution = DB::transaction(function () use ($referralCode, $campaign, $data) {
            $attribution = ReferralAttribution::create([
                'uuid' => Str::uuid()->toString(),
                'referral_code_id' => $referralCode->id,
                'campaign_id' => $campaign->id,
                'rule_set_id' => null,
                'referrer_user_id' => $referralCode->referrer_user_id,
                'prospect_external_ref' => $data['prospect_external_ref'] ?? null,
                'prospect_phone' => $data['prospect_phone'] ?? null,
                'prospect_email' => $data['prospect_email'] ?? null,
                'prospect_telegram_id' => $data['prospect_telegram_id'] ?? null,
                'click_id' => $data['click_id'] ?? null,
                'session_id' => $data['session_id'] ?? null,
                'device_fingerprint' => $data['device_fingerprint'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'attribution_source' => $data['attribution_source'],
                'attribution_status' => 'captured',
                'attributed_at' => now(),
                'expires_at' => null,
                'metadata' => $data['metadata'] ?? null,
            ]);

            // Optionally update referral code usage counters
            $referralCode->increment('used_count');
            $referralCode->update(['last_used_at' => now()]);

            return $attribution;
        });

        return [
            'attribution' => $attribution,
            'was_created' => true,
        ];
    }

    /**
     * Find existing reusable attribution.
     *
     * @param int $referralCodeId
     * @param array $data
     * @return ReferralAttribution|null
     */
    protected function findExistingAttribution(int $referralCodeId, array $data): ?ReferralAttribution
    {
        $query = ReferralAttribution::where('referral_code_id', $referralCodeId)
            ->whereIn('attribution_status', ['captured', 'linked'])
            ->whereNull('deleted_at');

        // Build OR conditions for identity matching
        $query->where(function ($q) use ($data) {
            $hasCondition = false;

            if (!empty($data['prospect_external_ref'])) {
                $q->orWhere('prospect_external_ref', $data['prospect_external_ref']);
                $hasCondition = true;
            }

            if (!empty($data['prospect_phone'])) {
                $q->orWhere('prospect_phone', $data['prospect_phone']);
                $hasCondition = true;
            }

            if (!empty($data['prospect_email'])) {
                $q->orWhere('prospect_email', $data['prospect_email']);
                $hasCondition = true;
            }

            if (!empty($data['prospect_telegram_id'])) {
                $q->orWhere('prospect_telegram_id', $data['prospect_telegram_id']);
                $hasCondition = true;
            }

            if (!empty($data['session_id'])) {
                $q->orWhere('session_id', $data['session_id']);
                $hasCondition = true;
            }

            // If no conditions were added, ensure the query doesn't match anything
            if (!$hasCondition) {
                $q->whereRaw('1 = 0');
            }
        });

        return $query->first();
    }

    /**
     * Get leaderboard data with referrer rankings.
     *
     * @param int|null $limit Optional limit for top N referrers
     * @return \Illuminate\Support\Collection
     */
    public function getLeaderboard(?int $limit = null): \Illuminate\Support\Collection
    {
        $query = ReferralAttribution::select(
            'referrer_user_id',
            DB::raw('COUNT(CASE WHEN attribution_status = "captured" THEN 1 END) as captured_count'),
            DB::raw('COUNT(CASE WHEN attribution_status = "linked" THEN 1 END) as linked_count'),
            DB::raw('COUNT(*) as total_count')
        )
            ->whereNotNull('referrer_user_id')
            ->groupBy('referrer_user_id')
            ->orderByDesc('total_count')
            ->orderByDesc('captured_count');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get referral attribution by prospect Telegram ID.
     *
     * @param string $telegramId
     * @return ReferralAttribution|null
     */
    public function getReferralByTelegramId(string $telegramId): ?ReferralAttribution
    {
        return ReferralAttribution::where('prospect_telegram_id', $telegramId)
            ->with(['referralCode', 'campaign'])
            ->whereNull('deleted_at')
            ->latest('created_at')
            ->first();
    }
}
