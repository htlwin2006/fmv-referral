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
        Schema::create('referral_rule_sets', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedInteger('version');
            $table->string('name', 150);
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->unsignedInteger('required_kyc_level');
            $table->boolean('require_account_opened')->default(true);
            $table->unsignedInteger('qualification_window_days')->nullable();
            $table->enum('reward_type', ['fixed_points', 'fixed_cash', 'custom'])->default('fixed_points');
            $table->decimal('reward_amount', 18, 2)->default(0.00);
            $table->string('reward_currency', 20)->nullable();
            $table->unsignedInteger('max_rewards_per_referrer')->nullable();
            $table->boolean('allow_self_referral')->default(false);
            $table->boolean('allow_duplicate_device')->default(false);
            $table->boolean('allow_duplicate_national_id')->default(false);
            $table->json('config_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('campaign_id', 'fk_referral_rule_sets_campaign_id')
                  ->references('id')->on('campaigns');
            
            $table->unique(['campaign_id', 'version'], 'uk_campaign_version');
            $table->index(['campaign_id', 'status'], 'idx_rule_sets_campaign_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_rule_sets');
    }
};
