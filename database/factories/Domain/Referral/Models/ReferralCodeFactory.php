<?php

namespace Database\Factories\Domain\Referral\Models;

use App\Domain\Referral\Models\Campaign;
use App\Domain\Referral\Models\ReferralCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Referral\Models\ReferralCode>
 */
class ReferralCodeFactory extends Factory
{
    protected $model = ReferralCode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'campaign_id' => Campaign::factory(),
            'referrer_user_id' => 'USR' . $this->faker->unique()->numberBetween(10000, 99999),
            'referrer_account_id' => 'ACC' . $this->faker->numberBetween(10000, 99999),
            'referral_code' => strtoupper($this->faker->unique()->bothify('???#####')),
            'code_type' => $this->faker->randomElement(['system', 'custom']),
            'status' => 'active',
            'max_usage_count' => null,
            'used_count' => 0,
            'last_used_at' => null,
            'metadata' => [
                'channel' => $this->faker->randomElement(['telegram', 'whatsapp', 'web']),
            ],
        ];
    }

    /**
     * Indicate that the code is system-generated.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'code_type' => 'system',
        ]);
    }

    /**
     * Indicate that the code is custom.
     */
    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'code_type' => 'custom',
        ]);
    }

    /**
     * Indicate that the code is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the code is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the code has been used.
     */
    public function used(int $count = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'used_count' => $count,
            'last_used_at' => now(),
        ]);
    }
}
