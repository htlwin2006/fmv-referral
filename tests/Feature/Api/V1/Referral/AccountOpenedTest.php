<?php

namespace Tests\Feature\Api\V1\Referral;

use App\Domain\Referral\Models\Campaign;
use App\Domain\Referral\Models\ReferralAcquisition;
use App\Domain\Referral\Models\ReferralAttribution;
use App\Domain\Referral\Models\ReferralCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountOpenedTest extends TestCase
{
    use RefreshDatabase;

    protected string $endpoint = '/api/v1/referrals/account-opened';

    /** @test */
    public function it_creates_acquisition_successfully_with_attribution_uuid()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
            'referrer_user_id' => 'USR10001',
        ]);

        $attribution = ReferralAttribution::factory()->captured()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'referrer_user_id' => 'USR10001',
            'prospect_email' => 'user@example.com',
            'prospect_phone' => '959123456789',
        ]);

        $payload = [
            'attribution_uuid' => $attribution->uuid,
            'acquired_user_id' => 'USR20009',
            'acquired_account_id' => 'WALLET9988',
            'acquired_customer_id' => 'CIF887766',
            'account_opened_at' => '2026-03-16T10:30:00Z',
            'metadata' => [
                'registration_channel' => 'telegram-miniapp',
            ],
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'uuid',
                    'referrer_user_id',
                    'acquired_user_id',
                    'acquired_account_id',
                    'acquired_customer_id',
                    'acquisition_status',
                    'account_opened_at',
                    'linked_at',
                    'metadata',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ])
            ->assertJson([
                'data' => [
                    'referrer_user_id' => 'USR10001',
                    'acquired_user_id' => 'USR20009',
                    'acquired_account_id' => 'WALLET9988',
                    'acquired_customer_id' => 'CIF887766',
                    'acquisition_status' => 'account_created',
                ],
                'message' => 'Referral acquisition linked successfully.',
            ]);

        $this->assertDatabaseHas('referral_acquisitions', [
            'attribution_id' => $attribution->id,
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'referrer_user_id' => 'USR10001',
            'acquired_user_id' => 'USR20009',
            'acquired_account_id' => 'WALLET9988',
            'acquired_customer_id' => 'CIF887766',
            'acquisition_status' => 'account_created',
        ]);

        // Check attribution status was updated
        $this->assertEquals('linked', $attribution->fresh()->attribution_status);
    }

    /** @test */
    public function it_creates_acquisition_with_referral_code_and_prospect_phone()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
            'referrer_user_id' => 'USR10001',
            'referral_code' => 'HAN8K2P1',
        ]);

        $attribution = ReferralAttribution::factory()->captured()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'referrer_user_id' => 'USR10001',
            'prospect_phone' => '959123456789',
        ]);

        $payload = [
            'referral_code' => 'HAN8K2P1',
            'prospect_phone' => '959123456789',
            'acquired_user_id' => 'USR20009',
            'acquired_account_id' => 'WALLET9988',
            'account_opened_at' => '2026-03-16T10:30:00Z',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'acquired_user_id' => 'USR20009',
                    'acquisition_status' => 'account_created',
                ],
            ]);

        $this->assertDatabaseHas('referral_acquisitions', [
            'attribution_id' => $attribution->id,
            'acquired_user_id' => 'USR20009',
        ]);
    }

    /** @test */
    public function it_reuses_existing_acquisition()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $attribution = ReferralAttribution::factory()->linked()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
        ]);

        $existingAcquisition = ReferralAcquisition::factory()->create([
            'attribution_id' => $attribution->id,
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'acquired_user_id' => 'USR20009',
        ]);

        $payload = [
            'attribution_uuid' => $attribution->uuid,
            'acquired_user_id' => 'USR20009',
            'account_opened_at' => '2026-03-16T10:30:00Z',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uuid' => $existingAcquisition->uuid,
                    'acquired_user_id' => 'USR20009',
                ],
                'message' => 'Existing referral acquisition returned.',
            ]);

        // Should still have only one acquisition
        $this->assertCount(1, ReferralAcquisition::all());
    }

    /** @test */
    public function it_fails_when_attribution_not_found_by_uuid()
    {
        $payload = [
            'attribution_uuid' => 'b3f0df0c-dc0f-4e88-a5c8-999999999999',
            'acquired_user_id' => 'USR20009',
            'account_opened_at' => '2026-03-16T10:30:00Z',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Referral attribution not found.',
            ]);
    }

    /** @test */
    public function it_fails_when_attribution_not_found_by_referral_code()
    {
        $payload = [
            'referral_code' => 'NOTEXIST',
            'prospect_phone' => '959123456789',
            'acquired_user_id' => 'USR20009',
            'account_opened_at' => '2026-03-16T10:30:00Z',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Referral attribution not found.',
            ]);
    }

    /** @test */
    public function it_fails_when_attribution_status_is_not_captured()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $attribution = ReferralAttribution::factory()->invalid()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
        ]);

        $payload = [
            'attribution_uuid' => $attribution->uuid,
            'acquired_user_id' => 'USR20009',
            'account_opened_at' => '2026-03-16T10:30:00Z',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Referral attribution not found.',
            ]);
    }

    /** @test */
    public function it_fails_when_acquired_user_id_is_missing()
    {
        $payload = [
            'attribution_uuid' => 'b3f0df0c-dc0f-4e88-a5c8-222222222222',
            'account_opened_at' => '2026-03-16T10:30:00Z',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['acquired_user_id']);
    }

    /** @test */
    public function it_fails_when_account_opened_at_is_missing()
    {
        $payload = [
            'attribution_uuid' => 'b3f0df0c-dc0f-4e88-a5c8-222222222222',
            'acquired_user_id' => 'USR20009',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_opened_at']);
    }

    /** @test */
    public function it_fails_when_no_attribution_resolution_method_provided()
    {
        $payload = [
            'acquired_user_id' => 'USR20009',
            'account_opened_at' => '2026-03-16T10:30:00Z',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['attribution_resolution']);
    }

    /** @test */
    public function it_fails_when_referral_code_provided_without_prospect_identity()
    {
        $payload = [
            'referral_code' => 'HAN8K2P1',
            'acquired_user_id' => 'USR20009',
            'account_opened_at' => '2026-03-16T10:30:00Z',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['attribution_resolution']);
    }

    /** @test */
    public function it_normalizes_referral_code_to_uppercase()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
            'referral_code' => 'HAN8K2P1',
        ]);

        $attribution = ReferralAttribution::factory()->captured()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'prospect_phone' => '959123456789',
        ]);

        $payload = [
            'referral_code' => 'han8k2p1',  // lowercase
            'prospect_phone' => '959123456789',
            'acquired_user_id' => 'USR20009',
            'account_opened_at' => '2026-03-16T10:30:00Z',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201);
    }

    /** @test */
    public function it_stores_metadata_as_json()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $attribution = ReferralAttribution::factory()->captured()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
        ]);

        $metadata = [
            'registration_channel' => 'telegram-miniapp',
            'utm_source' => 'referral',
            'custom_field' => 'custom_value',
        ];

        $payload = [
            'attribution_uuid' => $attribution->uuid,
            'acquired_user_id' => 'USR20009',
            'account_opened_at' => '2026-03-16T10:30:00Z',
            'metadata' => $metadata,
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'metadata' => $metadata,
                ],
            ]);

        $acquisition = ReferralAcquisition::where('acquired_user_id', 'USR20009')->first();
        $this->assertEquals($metadata, $acquisition->metadata);
    }

    /** @test */
    public function it_copies_prospect_data_from_attribution()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $attribution = ReferralAttribution::factory()->captured()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'prospect_phone' => '959123456789',
            'prospect_email' => 'user@example.com',
            'prospect_telegram_id' => '77889900',
        ]);

        $payload = [
            'attribution_uuid' => $attribution->uuid,
            'acquired_user_id' => 'USR20009',
            'account_opened_at' => '2026-03-16T10:30:00Z',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('referral_acquisitions', [
            'acquired_user_id' => 'USR20009',
            'prospect_phone' => '959123456789',
            'prospect_email' => 'user@example.com',
            'prospect_telegram_id' => '77889900',
        ]);
    }

    /** @test */
    public function it_resolves_attribution_by_referral_code_and_email()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
            'referral_code' => 'TEST123',
        ]);

        $attribution = ReferralAttribution::factory()->captured()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'prospect_email' => 'user@example.com',
        ]);

        $payload = [
            'referral_code' => 'TEST123',
            'prospect_email' => 'user@example.com',
            'acquired_user_id' => 'USR20009',
            'account_opened_at' => '2026-03-16T10:30:00Z',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'acquired_user_id' => 'USR20009',
                ],
            ]);
    }

    /** @test */
    public function it_resolves_attribution_by_referral_code_and_telegram_id()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
            'referral_code' => 'TEST456',
        ]);

        $attribution = ReferralAttribution::factory()->captured()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
            'prospect_telegram_id' => '77889900',
        ]);

        $payload = [
            'referral_code' => 'TEST456',
            'prospect_telegram_id' => '77889900',
            'acquired_user_id' => 'USR20009',
            'account_opened_at' => '2026-03-16T10:30:00Z',
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'acquired_user_id' => 'USR20009',
                ],
            ]);
    }

    /** @test */
    public function it_accepts_nullable_account_fields()
    {
        $campaign = Campaign::factory()->active()->create();
        $referralCode = ReferralCode::factory()->active()->create([
            'campaign_id' => $campaign->id,
        ]);

        $attribution = ReferralAttribution::factory()->captured()->create([
            'referral_code_id' => $referralCode->id,
            'campaign_id' => $campaign->id,
        ]);

        $payload = [
            'attribution_uuid' => $attribution->uuid,
            'acquired_user_id' => 'USR20009',
            'account_opened_at' => '2026-03-16T10:30:00Z',
            // No acquired_account_id or acquired_customer_id
        ];

        $response = $this->postJson($this->endpoint, $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('referral_acquisitions', [
            'acquired_user_id' => 'USR20009',
            'acquired_account_id' => null,
            'acquired_customer_id' => null,
        ]);
    }
}
