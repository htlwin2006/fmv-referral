<?php

namespace App\Domain\Referral\Models;

use Database\Factories\Domain\Referral\Models\ReferralCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferralCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'campaign_id',
        'referrer_user_id',
        'referrer_account_id',
        'referral_code',
        'code_type',
        'status',
        'max_usage_count',
        'used_count',
        'last_used_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_used_at' => 'datetime',
        'used_count' => 'integer',
        'max_usage_count' => 'integer',
    ];

    /**
     * Get the campaign that owns the referral code.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the referral attributions for the referral code.
     */
    public function referralAttributions(): HasMany
    {
        return $this->hasMany(ReferralAttribution::class);
    }

    /**
     * Get the referral acquisitions for the referral code.
     */
    public function referralAcquisitions(): HasMany
    {
        return $this->hasMany(ReferralAcquisition::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ReferralCodeFactory
    {
        return ReferralCodeFactory::new();
    }
}
