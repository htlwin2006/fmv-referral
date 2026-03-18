<?php

namespace Database\Factories\Domain\Referral\Models;

use App\Domain\Referral\Models\Campaign;
use App\Domain\Referral\Models\ReferralAcquisition;
use App\Domain\Referral\Models\ReferralAttribution;
use App\Domain\Referral\Models\ReferralCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ReferralAcquisition>
 */
class ReferralAcquisitionFactory extends Factory
{
    protected $model = ReferralAcquisition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $campaign = Campaign::factory()->create();
        $referralCode = ReferralCode::factory()->create([
            'campaign_id' => $campaign->id,
        ]);
        $attribution = ReferralAttribution::factory()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'referrer_user_id' => $referralCode->referrer_user_id,
        ]);

        return [
            'uuid' => Str::uuid()->toString(),
            'attribution_id' => $attribution->id,
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'referrer_user_id' => $referralCode->referrer_user_id,
            'acquired_user_id' => 'USR' . $this->faker->numberBetween(20000, 99999),
            'acquired_account_id' => 'WALLET' . $this->faker->numberBetween(1000, 9999),
            'acquired_customer_id' => $this->faker->optional()->numerify('CIF######'),
            'prospect_phone' => $attribution->prospect_phone,
            'prospect_email' => $attribution->prospect_email,
            'prospect_telegram_id' => $attribution->prospect_telegram_id,
            'account_opened_at' => now()->subMinutes($this->faker->numberBetween(1, 1440)),
            'linked_at' => now(),
            'acquisition_status' => 'account_created',
            'metadata' => [
                'registration_channel' => $this->faker->randomElement(['web', 'mobile-app', 'telegram-miniapp']),
            ],
        ];
    }

    /**
     * Create acquisition with pending status.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'acquisition_status' => 'pending',
        ]);
    }

    /**
     * Create acquisition with account_created status.
     */
    public function accountCreated(): static
    {
        return $this->state(fn (array $attributes) => [
            'acquisition_status' => 'account_created',
        ]);
    }

    /**
     * Create acquisition with kyc_in_progress status.
     */
    public function kycInProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'acquisition_status' => 'kyc_in_progress',
        ]);
    }

    /**
     * Create acquisition with qualified status.
     */
    public function qualified(): static
    {
        return $this->state(fn (array $attributes) => [
            'acquisition_status' => 'qualified',
        ]);
    }

    /**
     * Create acquisition with reward_pending status.
     */
    public function rewardPending(): static
    {
        return $this->state(fn (array $attributes) => [
            'acquisition_status' => 'reward_pending',
        ]);
    }

    /**
     * Create acquisition with rewarded status.
     */
    public function rewarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'acquisition_status' => 'rewarded',
        ]);
    }

    /**
     * Create acquisition with rejected status.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'acquisition_status' => 'rejected',
        ]);
    }

    /**
     * Create acquisition with expired status.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'acquisition_status' => 'expired',
        ]);
    }

    /**
     * Create acquisition with full account information.
     */
    public function withFullAccountInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'acquired_account_id' => 'WALLET' . $this->faker->numberBetween(1000, 9999),
            'acquired_customer_id' => 'CIF' . $this->faker->numerify('######'),
        ]);
    }

    /**
     * Create acquisition with minimal information.
     */
    public function withMinimalInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'acquired_account_id' => null,
            'acquired_customer_id' => null,
        ]);
    }
}
