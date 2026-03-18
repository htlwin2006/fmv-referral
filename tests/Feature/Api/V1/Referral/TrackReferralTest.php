<?php

namespace Tests\Feature\Api\V1\Referral;

use App\Domain\Referral\Models\Campaign;
use App\Domain\Referral\Models\ReferralAttribution;
use App\Domain\Referral\Models\ReferralCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackReferralTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = '/api/v1/referrals/track';

    /** @test */
    public function it_creates_new_attribution_successfully()
    {
        $campaign = Campaign::factory()->active()->create([
            'start_at' => now()->subDay(),
            'end_at' => now()->addMonth(),
        ]);

        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
            'referrer_user_id' => 'USR10001',
            'referral_code' => 'HAN8K2P1',
        ]);

        $payload = [
            'referral_code' => 'HAN8K2P1',
            'prospect_external_ref' => 'signup-session-12345',
            'prospect_phone' => '959123456789',
            'prospect_email' => 'user@example.com',
            'prospect_telegram_id' => '77889900',
            'click_id' => 'click_abc_001',
            'session_id' => 'sess_abc_001',
            'device_fingerprint' => 'fp_xyz_123',
            'ip_address' => '203.0.113.22',
            'user_agent' => 'Mozilla/5.0',
            'attribution_source' => 'manual_code',
            'metadata' => [
                'channel' => 'telegram-miniapp',
                'landing_page' => '/signup',
                'utm_source' => 'referral',
            ],
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'uuid',
                    'referral_code',
                    'referrer_user_id',
                    'campaign',
                    'prospect',
                    'tracking',
                    'attribution_status',
                    'attributed_at',
                    'expires_at',
                    'metadata',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ])
            ->assertJson([
                'data' => [
                    'referral_code' => 'HAN8K2P1',
                    'referrer_user_id' => 'USR10001',
                    'attribution_status' => 'captured',
                ],
                'message' => 'Referral attribution captured successfully.',
            ]);

        $this->assertDatabaseHas('referral_attributions', [
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'referrer_user_id' => 'USR10001',
            'prospect_external_ref' => 'signup-session-12345',
            'prospect_phone' => '959123456789',
            'prospect_email' => 'user@example.com',
            'prospect_telegram_id' => '77889900',
            'attribution_source' => 'manual_code',
            'attribution_status' => 'captured',
        ]);

        // Check usage counter was updated
        $this->assertEquals(1, $referralCode->fresh()->used_count);
    }

    /** @test */
    public function it_reuses_existing_attribution_when_matching_identity_found()
    {
        $campaign = Campaign::factory()->active()->create([
            'start_at' => now()->subDay(),
            'end_at' => now()->addMonth(),
        ]);

        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
            'referrer_user_id' => 'USR10001',
            'referral_code' => 'HAN8K2P1',
        ]);

        // Create existing attribution
        $existingAttribution = ReferralAttribution::factory()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'referrer_user_id' => 'USR10001',
            'prospect_email' => 'user@example.com',
            'attribution_status' => 'captured',
        ]);

        $payload = [
            'referral_code' => 'HAN8K2P1',
            'prospect_email' => 'user@example.com',
            'attribution_source' => 'manual_code',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uuid' => $existingAttribution->uuid,
                    'referral_code' => 'HAN8K2P1',
                ],
                'message' => 'Existing referral attribution returned.',
            ]);

        // Should still have only one attribution
        $this->assertCount(1, ReferralAttribution::all());
    }

    /** @test */
    public function it_normalizes_referral_code_to_uppercase()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
            'referral_code' => 'HAN8K2P1',
        ]);

        $payload = [
            'referral_code' => 'han8k2p1',  // lowercase
            'prospect_email' => 'test@example.com',
            'attribution_source' => 'link',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'referral_code' => 'HAN8K2P1',
                ],
            ]);
    }

    /** @test */
    public function it_normalizes_prospect_email_to_lowercase()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'prospect_email' => 'USER@EXAMPLE.COM',  // uppercase
            'attribution_source' => 'link',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('referral_attributions', [
            'prospect_email' => 'user@example.com',
        ]);
    }

    /** @test */
    public function it_fails_when_referral_code_is_missing()
    {
        $payload = [
            'prospect_email' => 'user@example.com',
            'attribution_source' => 'link',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['referral_code']);
    }

    /** @test */
    public function it_fails_when_all_identity_fields_are_missing()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'attribution_source' => 'link',
            // No identity fields
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['identity']);
    }

    /** @test */
    public function it_fails_when_referral_code_not_found()
    {
        $payload = [
            'referral_code' => 'NOTEXIST',
            'prospect_email' => 'user@example.com',
            'attribution_source' => 'link',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Referral code not found.',
            ]);
    }

    /** @test */
    public function it_fails_when_referral_code_is_inactive()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->inactive()->create([
            'campaign_id' => $campaign->id,
        ]);

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'prospect_email' => 'user@example.com',
            'attribution_source' => 'link',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Referral code is not active.',
            ]);
    }

    /** @test */
    public function it_fails_when_campaign_has_not_started_yet()
    {
        $campaign = Campaign::factory()->active()->create([
            'start_at' => now()->addDay(),  // starts tomorrow
            'end_at' => now()->addMonth(),
        ]);

        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'prospect_email' => 'user@example.com',
            'attribution_source' => 'link',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Campaign is not yet active.',
            ]);
    }

    /** @test */
    public function it_fails_when_campaign_has_already_ended()
    {
        $campaign = Campaign::factory()->active()->create([
            'start_at' => now()->subMonth(),
            'end_at' => now()->subDay(),  // ended yesterday
        ]);

        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'prospect_email' => 'user@example.com',
            'attribution_source' => 'link',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(409)
            ->assertJson([
                'message' => 'Campaign has already ended.',
            ]);
    }

    /** @test */
    public function it_accepts_request_with_only_prospect_external_ref()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'prospect_external_ref' => 'signup-session-12345',
            'attribution_source' => 'link',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201);
    }

    /** @test */
    public function it_accepts_request_with_only_session_id()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'session_id' => 'sess_abc_001',
            'attribution_source' => 'link',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201);
    }

    /** @test */
    public function it_validates_attribution_source_enum()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'prospect_email' => 'user@example.com',
            'attribution_source' => 'invalid_source',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['attribution_source']);
    }

    /** @test */
    public function it_validates_ip_address_format()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'prospect_email' => 'user@example.com',
            'attribution_source' => 'link',
            'ip_address' => 'invalid-ip',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ip_address']);
    }

    /** @test */
    public function it_reuses_attribution_when_matching_by_phone()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $existingAttribution = ReferralAttribution::factory()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'prospect_phone' => '959123456789',
            'attribution_status' => 'captured',
        ]);

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'prospect_phone' => '959123456789',
            'attribution_source' => 'link',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uuid' => $existingAttribution->uuid,
                ],
                'message' => 'Existing referral attribution returned.',
            ]);
    }

    /** @test */
    public function it_reuses_attribution_when_matching_by_telegram_id()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $existingAttribution = ReferralAttribution::factory()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'prospect_telegram_id' => '77889900',
            'attribution_status' => 'captured',
        ]);

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'prospect_telegram_id' => '77889900',
            'attribution_source' => 'link',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uuid' => $existingAttribution->uuid,
                ],
                'message' => 'Existing referral attribution returned.',
            ]);
    }

    /** @test */
    public function it_stores_metadata_as_json()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $metadata = [
            'channel' => 'telegram-miniapp',
            'landing_page' => '/signup',
            'utm_source' => 'referral',
            'custom_field' => 'custom_value',
        ];

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'prospect_email' => 'user@example.com',
            'attribution_source' => 'api',
            'metadata' => $metadata,
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'metadata' => $metadata,
                ],
            ]);

        $this->assertDatabaseHas('referral_attributions', [
            'prospect_email' => 'user@example.com',
        ]);

        $attribution = ReferralAttribution::where('prospect_email', 'user@example.com')->first();
        $this->assertEquals($metadata, $attribution->metadata);
    }

    /** @test */
    public function it_does_not_reuse_attribution_with_different_status()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        // Create attribution with status 'invalid'
        ReferralAttribution::factory()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'prospect_email' => 'user@example.com',
            'attribution_status' => 'invalid',
        ]);

        $payload = [
            'referral_code' => $referralCode->referral_code,
            'prospect_email' => 'user@example.com',
            'attribution_source' => 'link',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        // Should create new attribution, not reuse invalid one
        $response->assertStatus(201);

        $this->assertCount(2, ReferralAttribution::all());
    }
}
