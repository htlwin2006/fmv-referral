<?php

namespace App\Domain\Referral\Models;

use Database\Factories\Domain\Referral\Models\CampaignFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'code',
        'name',
        'description',
        'status',
        'start_at',
        'end_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /**
     * Get the referral codes for the campaign.
     */
    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class);
    }

    /**
     * Get the referral attributions for the campaign.
     */
    public function referralAttributions(): HasMany
    {
        return $this->hasMany(ReferralAttribution::class);
    }

    /**
     * Get the referral acquisitions for the campaign.
     */
    public function referralAcquisitions(): HasMany
    {
        return $this->hasMany(ReferralAcquisition::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CampaignFactory
    {
        return CampaignFactory::new();
    }
}
