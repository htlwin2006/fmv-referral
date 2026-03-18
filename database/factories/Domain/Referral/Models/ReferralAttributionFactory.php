<?php

namespace Database\Factories\Domain\Referral\Models;

use App\Domain\Referral\Models\Campaign;
use App\Domain\Referral\Models\ReferralAttribution;
use App\Domain\Referral\Models\ReferralCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ReferralAttribution>
 */
class ReferralAttributionFactory extends Factory
{
    protected $model = ReferralAttribution::class;

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

        return [
            'uuid' => Str::uuid()->toString(),
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'rule_set_id' => null,
            'referrer_user_id' => $referralCode->referrer_user_id,
            'prospect_external_ref' => 'signup-' . $this->faker->uuid(),
            'prospect_phone' => $this->faker->optional()->numerify('959########'),
            'prospect_email' => $this->faker->optional()->email(),
            'prospect_telegram_id' => $this->faker->optional()->numerify('########'),
            'click_id' => 'click_' . $this->faker->uuid(),
            'session_id' => 'sess_' . $this->faker->uuid(),
            'device_fingerprint' => 'fp_' . $this->faker->uuid(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'attribution_source' => $this->faker->randomElement(['link', 'manual_code', 'api', 'import']),
            'attribution_status' => 'captured',
            'attributed_at' => now(),
            'expires_at' => null,
            'metadata' => [
                'channel' => $this->faker->randomElement(['web', 'mobile-app', 'telegram-miniapp']),
                'utm_source' => 'referral',
            ],
        ];
    }

    /**
     * Create attribution with captured status.
     */
    public function captured(): static
    {
        return $this->state(fn (array $attributes) => [
            'attribution_status' => 'captured',
        ]);
    }

    /**
     * Create attribution with linked status.
     */
    public function linked(): static
    {
        return $this->state(fn (array $attributes) => [
            'attribution_status' => 'linked',
        ]);
    }

    /**
     * Create attribution with invalid status.
     */
    public function invalid(): static
    {
        return $this->state(fn (array $attributes) => [
            'attribution_status' => 'invalid',
        ]);
    }

    /**
     * Create attribution with expired status.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'attribution_status' => 'expired',
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Create attribution with rejected status.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'attribution_status' => 'rejected',
        ]);
    }

    /**
     * Create attribution via link source.
     */
    public function viaLink(): static
    {
        return $this->state(fn (array $attributes) => [
            'attribution_source' => 'link',
        ]);
    }

    /**
     * Create attribution via manual code entry.
     */
    public function viaManualCode(): static
    {
        return $this->state(fn (array $attributes) => [
            'attribution_source' => 'manual_code',
        ]);
    }

    /**
     * Create attribution via API.
     */
    public function viaApi(): static
    {
        return $this->state(fn (array $attributes) => [
            'attribution_source' => 'api',
        ]);
    }

    /**
     * Create attribution via import.
     */
    public function viaImport(): static
    {
        return $this->state(fn (array $attributes) => [
            'attribution_source' => 'import',
        ]);
    }

    /**
     * Create attribution with only email identity.
     */
    public function withEmailOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'prospect_external_ref' => null,
            'prospect_phone' => null,
            'prospect_email' => $this->faker->email(),
            'prospect_telegram_id' => null,
        ]);
    }

    /**
     * Create attribution with only phone identity.
     */
    public function withPhoneOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'prospect_external_ref' => null,
            'prospect_phone' => $this->faker->numerify('959########'),
            'prospect_email' => null,
            'prospect_telegram_id' => null,
        ]);
    }

    /**
     * Create attribution with only telegram ID identity.
     */
    public function withTelegramIdOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'prospect_external_ref' => null,
            'prospect_phone' => null,
            'prospect_email' => null,
            'prospect_telegram_id' => $this->faker->numerify('########'),
        ]);
    }

    /**
     * Create attribution with full prospect information.
     */
    public function withFullProspectInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'prospect_external_ref' => 'signup-' . $this->faker->uuid(),
            'prospect_phone' => $this->faker->numerify('959########'),
            'prospect_email' => $this->faker->email(),
            'prospect_telegram_id' => $this->faker->numerify('########'),
        ]);
    }
}
