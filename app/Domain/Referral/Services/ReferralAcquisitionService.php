<?php

namespace App\Domain\Referral\Services;

use App\Domain\Referral\Exceptions\ReferralAttributionNotFoundException;
use App\Domain\Referral\Exceptions\UserAlreadyLinkedToAcquisitionException;
use App\Domain\Referral\Models\ReferralAcquisition;
use App\Domain\Referral\Models\ReferralAttribution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralAcquisitionService
{
    /**
     * Link a created user account to a referral attribution.
     *
     * @param array $data Validated acquisition data
     * @return array ['acquisition' => ReferralAcquisition, 'was_created' => bool]
     * @throws ReferralAttributionNotFoundException
     * @throws UserAlreadyLinkedToAcquisitionException
     */
    public function linkAccount(array $data): array
    {
        // Resolve attribution
        $attribution = $this->resolveAttribution($data);

        if (!$attribution) {
            throw new ReferralAttributionNotFoundException();
        }

        // Validate attribution status
        if ($attribution->attribution_status !== 'captured') {
            throw new ReferralAttributionNotFoundException();
        }

        // Check if this user is already linked to another acquisition
        $existingUserAcquisition = ReferralAcquisition::where('acquired_user_id', $data['acquired_user_id'])
            ->whereNull('deleted_at')
            ->first();

        if ($existingUserAcquisition && $existingUserAcquisition->attribution_id !== $attribution->id) {
            throw new UserAlreadyLinkedToAcquisitionException();
        }

        // Check if acquisition already exists for this attribution
        $existingAcquisition = ReferralAcquisition::where('attribution_id', $attribution->id)
            ->whereNull('deleted_at')
            ->first();

        if ($existingAcquisition) {
            return [
                'acquisition' => $existingAcquisition,
                'was_created' => false,
            ];
        }

        // Create new acquisition
        $acquisition = DB::transaction(function () use ($attribution, $data) {
            $acquisition = ReferralAcquisition::create([
                'uuid' => Str::uuid()->toString(),
                'attribution_id' => $attribution->id,
                'referral_code_id' => $attribution->referral_code_id,
                'campaign_id' => $attribution->campaign_id,
                'referrer_user_id' => $attribution->referrer_user_id,
                'acquired_user_id' => $data['acquired_user_id'],
                'acquired_account_id' => $data['acquired_account_id'] ?? null,
                'acquired_customer_id' => $data['acquired_customer_id'] ?? null,
                'prospect_phone' => $attribution->prospect_phone,
                'prospect_email' => $attribution->prospect_email,
                'prospect_telegram_id' => $attribution->prospect_telegram_id,
                'account_opened_at' => $data['account_opened_at'],
                'linked_at' => now(),
                'acquisition_status' => 'account_created',
                'metadata' => $data['metadata'] ?? null,
            ]);

            // Update attribution status to linked
            $attribution->update(['attribution_status' => 'linked']);

            return $acquisition;
        });

        return [
            'acquisition' => $acquisition,
            'was_created' => true,
        ];
    }

    /**
     * Resolve attribution from request data.
     *
     * @param array $data
     * @return ReferralAttribution|null
     */
    protected function resolveAttribution(array $data): ?ReferralAttribution
    {
        // Try by attribution_uuid first
        if (!empty($data['attribution_uuid'])) {
            return ReferralAttribution::where('uuid', $data['attribution_uuid'])
                ->whereNull('deleted_at')
                ->first();
        }

        // Try by referral_code + prospect identity
        if (!empty($data['referral_code'])) {
            $query = ReferralAttribution::query()
                ->whereHas('referralCode', function ($q) use ($data) {
                    $q->where('referral_code', $data['referral_code']);
                })
                ->whereNull('deleted_at');

            // Add identity matching
            if (!empty($data['prospect_phone'])) {
                $query->where('prospect_phone', $data['prospect_phone']);
            } elseif (!empty($data['prospect_email'])) {
                $query->where('prospect_email', $data['prospect_email']);
            } elseif (!empty($data['prospect_telegram_id'])) {
                $query->where('prospect_telegram_id', $data['prospect_telegram_id']);
            }

            return $query->first();
        }

        return null;
    }
}
