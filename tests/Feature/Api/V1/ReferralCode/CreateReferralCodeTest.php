<?php

namespace Tests\Feature\Api\V1\ReferralCode;

use App\Domain\Referral\Models\Campaign;
use App\Domain\Referral\Models\ReferralCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateReferralCodeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful system code creation
     */
    public function test_can_create_system_referral_code_successfully(): void
    {
        $campaign = Campaign::factory()->active()->create([
            'code' => 'WELCOME2026',
            'start_at' => now()->subDay(),
            'end_at' => now()->addMonth(),
        ]);

        $payload = [
            'referrer_user_id' => 'USR10001',
            'referrer_account_id' => 'ACC10001',
            'campaign_code' => 'WELCOME2026',
            'code_type' => 'system',
            'metadata' => [
                'channel' => 'telegram-miniapp',
                'requested_by' => 'miniapp',
            ],
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'uuid',
                    'referrer_user_id',
                    'referrer_account_id',
                    'referral_code',
                    'code_type',
                    'status',
                    'used_count',
                    'max_usage_count',
                    'campaign',
                    'metadata',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ])
            ->assertJson([
                'data' => [
                    'referrer_user_id' => 'USR10001',
                    'referrer_account_id' => 'ACC10001',
                    'code_type' => 'system',
                    'status' => 'active',
                    'used_count' => 0,
                ],
                'message' => 'Referral code created successfully.',
            ]);

        $this->assertDatabaseHas('referral_codes', [
            'referrer_user_id' => 'USR10001',
            'campaign_id' => $campaign->id,
            'code_type' => 'system',
            'status' => 'active',
        ]);
    }

    /**
     * Test reusing existing active referral code
     */
    public function test_returns_existing_active_referral_code(): void
    {
        $campaign = Campaign::factory()->active()->create([
            'code' => 'WELCOME2026',
        ]);

        $existingCode = ReferralCode::factory()->create([
            'campaign_id' => $campaign->id,
            'referrer_user_id' => 'USR10001',
            'referrer_account_id' => 'ACC10001',
            'referral_code' => 'HAN8K2P1',
            'status' => 'active',
            'used_count' => 3,
        ]);

        $payload = [
            'referrer_user_id' => 'USR10001',
            'referrer_account_id' => 'ACC10001',
            'campaign_code' => 'WELCOME2026',
            'code_type' => 'system',
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uuid' => $existingCode->uuid,
                    'referral_code' => 'HAN8K2P1',
                    'used_count' => 3,
                ],
                'message' => 'Existing active referral code returned.',
            ]);

        // Verify no duplicate was created
        $this->assertEquals(1, ReferralCode::where('referrer_user_id', 'USR10001')
            ->where('campaign_id', $campaign->id)
            ->count());
    }

    /**
     * Test successful custom code creation
     */
    public function test_can_create_custom_referral_code(): void
    {
        $campaign = Campaign::factory()->active()->create([
            'code' => 'WELCOME2026',
        ]);

        $payload = [
            'referrer_user_id' => 'USR10001',
            'referrer_account_id' => 'ACC10001',
            'campaign_code' => 'WELCOME2026',
            'code_type' => 'custom',
            'custom_code' => 'HAN_BONUS',
            'metadata' => [
                'channel' => 'telegram-miniapp',
            ],
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'referral_code' => 'HAN_BONUS',
                    'code_type' => 'custom',
                ],
                'message' => 'Referral code created successfully.',
            ]);

        $this->assertDatabaseHas('referral_codes', [
            'referral_code' => 'HAN_BONUS',
            'code_type' => 'custom',
        ]);
    }

    /**
     * Test campaign not found
     */
    public function test_returns_404_when_campaign_not_found(): void
    {
        $payload = [
            'referrer_user_id' => 'USR10001',
            'campaign_code' => 'NONEXISTENT',
            'code_type' => 'system',
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Campaign not found.',
            ]);
    }

    /**
     * Test campaign not yet active
     */
    public function test_returns_409_when_campaign_not_yet_active(): void
    {
        Campaign::factory()->active()->create([
            'code' => 'FUTURE2026',
            'start_at' => now()->addWeek(),
        ]);

        $payload = [
            'referrer_user_id' => 'USR10001',
            'campaign_code' => 'FUTURE2026',
            'code_type' => 'system',
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Campaign is not yet active.',
            ]);
    }

    /**
     * Test campaign already ended
     */
    public function test_returns_409_when_campaign_ended(): void
    {
        Campaign::factory()->active()->create([
            'code' => 'PAST2026',
            'end_at' => now()->subDay(),
        ]);

        $payload = [
            'referrer_user_id' => 'USR10001',
            'campaign_code' => 'PAST2026',
            'code_type' => 'system',
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Campaign has already ended.',
            ]);
    }

    /**
     * Test campaign status not allowed
     */
    public function test_returns_409_when_campaign_status_not_allowed(): void
    {
        Campaign::factory()->create([
            'code' => 'ENDED2026',
            'status' => 'ended',
        ]);

        $payload = [
            'referrer_user_id' => 'USR10001',
            'campaign_code' => 'ENDED2026',
            'code_type' => 'system',
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(409)
            ->assertJsonFragment([
                'message' => 'Campaign status does not allow referral code generation.',
            ]);
    }

    /**
     * Test invalid custom code format
     */
    public function test_returns_422_when_custom_code_format_invalid(): void
    {
        Campaign::factory()->active()->create(['code' => 'WELCOME2026']);

        $payload = [
            'referrer_user_id' => 'USR10001',
            'campaign_code' => 'WELCOME2026',
            'code_type' => 'custom',
            'custom_code' => 'invalid-code',
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['custom_code']);
    }

    /**
     * Test duplicate custom code
     */
    public function test_returns_409_when_custom_code_already_exists(): void
    {
        $campaign = Campaign::factory()->active()->create(['code' => 'WELCOME2026']);

        ReferralCode::factory()->create([
            'referral_code' => 'TAKEN_CODE',
            'campaign_id' => $campaign->id,
        ]);

        $payload = [
            'referrer_user_id' => 'USR10002',
            'campaign_code' => 'WELCOME2026',
            'code_type' => 'custom',
            'custom_code' => 'TAKEN_CODE',
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Referral code already exists.',
            ]);
    }

    /**
     * Test missing required fields
     */
    public function test_returns_422_when_required_fields_missing(): void
    {
        $response = $this->postJson('/api/v1/referral-codes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['referrer_user_id', 'campaign_code', 'code_type']);
    }

    /**
     * Test custom code required when type is custom
     */
    public function test_returns_422_when_custom_code_missing_for_custom_type(): void
    {
        Campaign::factory()->active()->create(['code' => 'WELCOME2026']);

        $payload = [
            'referrer_user_id' => 'USR10001',
            'campaign_code' => 'WELCOME2026',
            'code_type' => 'custom',
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['custom_code']);
    }

    /**
     * Test custom code prohibited when type is system
     */
    public function test_returns_422_when_custom_code_provided_for_system_type(): void
    {
        Campaign::factory()->active()->create(['code' => 'WELCOME2026']);

        $payload = [
            'referrer_user_id' => 'USR10001',
            'campaign_code' => 'WELCOME2026',
            'code_type' => 'system',
            'custom_code' => 'SHOULD_NOT_BE_HERE',
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['custom_code']);
    }

    /**
     * Test campaign code normalization
     */
    public function test_campaign_code_is_normalized_to_uppercase(): void
    {
        Campaign::factory()->active()->create(['code' => 'WELCOME2026']);

        $payload = [
            'referrer_user_id' => 'USR10001',
            'campaign_code' => 'welcome2026',
            'code_type' => 'system',
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.campaign.code', 'WELCOME2026');
    }

    /**
     * Test custom code normalization
     */
    public function test_custom_code_is_normalized_to_uppercase(): void
    {
        Campaign::factory()->active()->create(['code' => 'WELCOME2026']);

        $payload = [
            'referrer_user_id' => 'USR10001',
            'campaign_code' => 'WELCOME2026',
            'code_type' => 'custom',
            'custom_code' => 'my_code',
        ];

        $response = $this->postJson('/api/v1/referral-codes', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'referral_code' => 'MY_CODE',
                ],
            ]);
    }
}
