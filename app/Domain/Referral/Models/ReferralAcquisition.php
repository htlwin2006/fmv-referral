<?php

namespace App\Domain\Referral\Models;

use Database\Factories\Domain\Referral\Models\ReferralAcquisitionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferralAcquisition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'attribution_id',
        'referral_code_id',
        'campaign_id',
        'referrer_user_id',
        'acquired_user_id',
        'acquired_account_id',
        'acquired_customer_id',
        'prospect_phone',
        'prospect_email',
        'prospect_telegram_id',
        'account_opened_at',
        'linked_at',
        'acquisition_status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'account_opened_at' => 'datetime',
        'linked_at' => 'datetime',
    ];

    /**
     * Get the attribution that owns the acquisition.
     */
    public function attribution(): BelongsTo
    {
        return $this->belongsTo(ReferralAttribution::class, 'attribution_id');
    }

    /**
     * Get the referral code that owns the acquisition.
     */
    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }

    /**
     * Get the campaign that owns the acquisition.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ReferralAcquisitionFactory
    {
        return ReferralAcquisitionFactory::new();
    }
}
