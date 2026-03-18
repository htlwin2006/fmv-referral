<?php

namespace Database\Factories\Domain\Referral\Models;

use App\Domain\Referral\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Referral\Models\Campaign>
 */
class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'code' => strtoupper($this->faker->unique()->bothify('????####')),
            'name' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['draft', 'active', 'paused']),
            'start_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'end_at' => $this->faker->dateTimeBetween('+2 months', '+6 months'),
            'metadata' => [
                'channel' => $this->faker->randomElement(['telegram', 'whatsapp', 'email']),
                'country' => $this->faker->countryCode(),
            ],
        ];
    }

    /**
     * Indicate that the campaign is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the campaign is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the campaign is paused.
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paused',
        ]);
    }
}
