<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('referral_acquisitions', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('attribution_id');
            $table->unsignedBigInteger('referral_code_id');
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('rule_set_id')->nullable();
            $table->string('referrer_user_id', 100);
            $table->string('acquired_user_id', 100)->nullable();
            $table->string('acquired_account_id', 100)->nullable();
            $table->string('acquired_customer_id', 100)->nullable();
            $table->string('prospect_phone', 30)->nullable();
            $table->string('prospect_email', 150)->nullable();
            $table->string('prospect_telegram_id', 100)->nullable();
            $table->dateTime('account_opened_at')->nullable();
            $table->dateTime('linked_at')->nullable();
            $table->enum('acquisition_status', [
                'pending',
                'account_created',
                'kyc_in_progress',
                'qualified',
                'reward_pending',
                'rewarded',
                'rejected',
                'expired'
            ])->default('pending');
            $table->string('rejection_reason_code', 100)->nullable();
            $table->string('rejection_reason_text', 255)->nullable();
            $table->dateTime('qualification_deadline_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('attribution_id', 'fk_referral_acquisitions_attribution_id')
                  ->references('id')->on('referral_attributions');
            $table->foreign('referral_code_id', 'fk_referral_acquisitions_referral_code_id')
                  ->references('id')->on('referral_codes');
            $table->foreign('campaign_id', 'fk_referral_acquisitions_campaign_id')
                  ->references('id')->on('campaigns');
            $table->foreign('rule_set_id', 'fk_referral_acquisitions_rule_set_id')
                  ->references('id')->on('referral_rule_sets');
            
            $table->unique('attribution_id', 'uk_acquisition_attribution');
            $table->unique('acquired_user_id', 'uk_acquired_user_id');
            $table->index('referrer_user_id', 'idx_referral_acquisitions_referrer');
            $table->index('acquisition_status', 'idx_referral_acquisitions_status');
            $table->index('acquired_account_id', 'idx_referral_acquisitions_account');
            $table->index('acquired_customer_id', 'idx_referral_acquisitions_customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_acquisitions');
    }
};
