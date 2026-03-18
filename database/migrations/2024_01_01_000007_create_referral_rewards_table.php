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
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('acquisition_id');
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('rule_set_id')->nullable();
            $table->string('referrer_user_id', 100);
            $table->string('acquired_user_id', 100);
            $table->enum('reward_type', ['fixed_points', 'fixed_cash', 'custom']);
            $table->decimal('reward_amount', 18, 2);
            $table->string('reward_currency', 20)->nullable();
            $table->enum('reward_status', ['pending', 'processing', 'issued', 'failed', 'cancelled', 'reversed'])->default('pending');
            $table->string('reward_reference_no', 100)->nullable();
            $table->string('wallet_transaction_id', 100)->nullable();
            $table->string('issuer_service', 100)->nullable();
            $table->dateTime('issued_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->string('failure_code', 100)->nullable();
            $table->text('failure_reason')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->string('idempotency_key', 150);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('acquisition_id', 'fk_referral_rewards_acquisition_id')
                  ->references('id')->on('referral_acquisitions');
            $table->foreign('campaign_id', 'fk_referral_rewards_campaign_id')
                  ->references('id')->on('campaigns');
            $table->foreign('rule_set_id', 'fk_referral_rewards_rule_set_id')
                  ->references('id')->on('referral_rule_sets');
            
            $table->unique('acquisition_id', 'uk_referral_rewards_acquisition');
            $table->unique('idempotency_key', 'uk_referral_rewards_idempotency');
            $table->index('referrer_user_id', 'idx_referral_rewards_referrer');
            $table->index('reward_status', 'idx_referral_rewards_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
    }
};
