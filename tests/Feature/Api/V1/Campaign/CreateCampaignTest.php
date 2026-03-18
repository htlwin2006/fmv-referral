<?php

namespace Tests\Feature\Api\V1\Campaign;

use App\Domain\Referral\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCampaignTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful campaign creation
     */
    public function test_can_create_campaign_successfully(): void
    {
        $payload = [
            'code' => 'WELCOME2026',
            'name' => 'Welcome Referral Campaign 2026',
            'description' => 'Referral campaign for new users who pass KYC.',
            'status' => 'active',
            'start_at' => '2026-03-01 00:00:00',
            'end_at' => '2026-06-30 23:59:59',
            'metadata' => [
                'channel' => 'telegram',
                'country' => 'MM',
            ],
        ];

        $response = $this->postJson('/api/v1/campaigns', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'uuid',
                    'code',
                    'name',
                    'description',
                    'status',
                    'start_at',
                    'end_at',
                    'metadata',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ])
            ->assertJson([
                'data' => [
                    'code' => 'WELCOME2026',
                    'name' => 'Welcome Referral Campaign 2026',
                    'status' => 'active',
                ],
                'message' => 'Campaign created successfully.',
            ]);

        $this->assertDatabaseHas('campaigns', [
            'code' => 'WELCOME2026',
            'name' => 'Welcome Referral Campaign 2026',
            'status' => 'active',
        ]);
    }

    /**
     * Test code is normalized to uppercase
     */
    public function test_campaign_code_is_normalized_to_uppercase(): void
    {
        $payload = [
            'code' => 'welcome2026',
            'name' => 'Welcome Campaign',
            'status' => 'draft',
        ];

        $response = $this->postJson('/api/v1/campaigns', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'code' => 'WELCOME2026',
                ],
            ]);
    }

    /**
     * Test validation fails when required fields are missing
     */
    public function test_validation_fails_when_required_fields_missing(): void
    {
        $response = $this->postJson('/api/v1/campaigns', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'status']);
    }

    /**
     * Test validation fails when code format is invalid
     */
    public function test_validation_fails_when_code_format_invalid(): void
    {
        $payload = [
            'code' => 'welcome-2026',
            'name' => 'Welcome Campaign',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/campaigns', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    /**
     * Test validation fails when code exceeds max length
     */
    public function test_validation_fails_when_code_exceeds_max_length(): void
    {
        $payload = [
            'code' => str_repeat('A', 51),
            'name' => 'Welcome Campaign',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/campaigns', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    /**
     * Test validation fails when name exceeds max length
     */
    public function test_validation_fails_when_name_exceeds_max_length(): void
    {
        $payload = [
            'code' => 'WELCOME2026',
            'name' => str_repeat('A', 151),
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/campaigns', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test cannot create campaign with duplicate code
     */
    public function test_cannot_create_campaign_with_duplicate_code(): void
    {
        Campaign::factory()->create(['code' => 'WELCOME2026']);

        $payload = [
            'code' => 'WELCOME2026',
            'name' => 'Another Campaign',
            'status' => 'active',
        ];

        $response = $this->postJson('/api/v1/campaigns', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    /**
     * Test validation fails with invalid status
     */
    public function test_validation_fails_with_invalid_status(): void
    {
        $payload = [
            'code' => 'WELCOME2026',
            'name' => 'Welcome Campaign',
            'status' => 'ended',
        ];

        $response = $this->postJson('/api/v1/campaigns', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * Test validation fails when end_at is before start_at
     */
    public function test_validation_fails_when_end_at_before_start_at(): void
    {
        $payload = [
            'code' => 'WELCOME2026',
            'name' => 'Welcome Campaign',
            'status' => 'active',
            'start_at' => '2026-06-01 00:00:00',
            'end_at' => '2026-03-01 00:00:00',
        ];

        $response = $this->postJson('/api/v1/campaigns', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_at']);
    }

    /**
     * Test can create campaign with minimal required fields
     */
    public function test_can_create_campaign_with_minimal_fields(): void
    {
        $payload = [
            'code' => 'MINIMAL2026',
            'name' => 'Minimal Campaign',
            'status' => 'draft',
        ];

        $response = $this->postJson('/api/v1/campaigns', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('campaigns', [
            'code' => 'MINIMAL2026',
            'name' => 'Minimal Campaign',
            'status' => 'draft',
        ]);
    }

    /**
     * Test campaign is created with UUID
     */
    public function test_campaign_created_with_uuid(): void
    {
        $payload = [
            'code' => 'UUID_TEST',
            'name' => 'UUID Test Campaign',
            'status' => 'draft',
        ];

        $response = $this->postJson('/api/v1/campaigns', $payload);

        $response->assertStatus(201);

        $campaign = Campaign::where('code', 'UUID_TEST')->first();
        $this->assertNotNull($campaign->uuid);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $campaign->uuid
        );
    }
}
