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
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->string('referrer_user_id', 100);
            $table->string('referrer_account_id', 100)->nullable();
            $table->string('referral_code', 32)->unique();
            $table->enum('code_type', ['system', 'custom'])->default('system');
            $table->enum('status', ['active', 'inactive', 'blocked', 'expired'])->default('active');
            $table->unsignedInteger('max_usage_count')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->dateTime('last_used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('campaign_id', 'fk_referral_codes_campaign_id')
                  ->references('id')->on('campaigns');
            
            $table->index('referrer_user_id', 'idx_referral_codes_referrer');
            $table->index('campaign_id', 'idx_referral_codes_campaign');
            $table->index('status', 'idx_referral_codes_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_codes');
    }
};
