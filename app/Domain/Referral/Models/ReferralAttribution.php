<?php

namespace App\Domain\Referral\Models;

use Database\Factories\Domain\Referral\Models\ReferralAttributionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferralAttribution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'referral_code_id',
        'campaign_id',
        'rule_set_id',
        'referrer_user_id',
        'prospect_external_ref',
        'prospect_phone',
        'prospect_email',
        'prospect_telegram_id',
        'click_id',
        'session_id',
        'device_fingerprint',
        'ip_address',
        'user_agent',
        'attribution_source',
        'attribution_status',
        'attributed_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'attributed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the referral code that owns the attribution.
     */
    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }

    /**
     * Get the campaign that owns the attribution.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the referral acquisition for the attribution.
     */
    public function referralAcquisition(): HasOne
    {
        return $this->hasOne(ReferralAcquisition::class, 'attribution_id');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ReferralAttributionFactory
    {
        return ReferralAttributionFactory::new();
    }
}
