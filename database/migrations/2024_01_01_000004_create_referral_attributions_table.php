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
        Schema::create('referral_attributions', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('referral_code_id');
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('rule_set_id')->nullable();
            $table->string('referrer_user_id', 100);
            $table->string('prospect_external_ref', 150)->nullable();
            $table->string('prospect_phone', 30)->nullable();
            $table->string('prospect_email', 150)->nullable();
            $table->string('prospect_telegram_id', 100)->nullable();
            $table->string('click_id', 150)->nullable();
            $table->string('session_id', 150)->nullable();
            $table->string('device_fingerprint', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->enum('attribution_source', ['link', 'manual_code', 'api', 'import'])->default('api');
            $table->enum('attribution_status', ['captured', 'linked', 'invalid', 'expired', 'rejected'])->default('captured');
            $table->dateTime('attributed_at');
            $table->dateTime('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('referral_code_id', 'fk_referral_attributions_referral_code_id')
                  ->references('id')->on('referral_codes');
            $table->foreign('campaign_id', 'fk_referral_attributions_campaign_id')
                  ->references('id')->on('campaigns');
            $table->foreign('rule_set_id', 'fk_referral_attributions_rule_set_id')
                  ->references('id')->on('referral_rule_sets');
            
            $table->index('referrer_user_id', 'idx_referral_attributions_referrer');
            $table->index('attribution_status', 'idx_referral_attributions_status');
            $table->index('prospect_phone', 'idx_referral_attributions_phone');
            $table->index('prospect_email', 'idx_referral_attributions_email');
            $table->index('prospect_telegram_id', 'idx_referral_attributions_telegram');
            $table->index('prospect_external_ref', 'idx_referral_attributions_external_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_attributions');
    }
};
