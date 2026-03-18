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
        Schema::create('referral_kyc_progress', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('acquisition_id');
            $table->string('kyc_provider', 100);
            $table->string('acquired_user_id', 100);
            $table->unsignedInteger('current_kyc_level')->default(0);
            $table->unsignedInteger('required_kyc_level')->default(0);
            $table->enum('kyc_status', ['not_started', 'pending', 'approved', 'rejected', 'expired'])->default('not_started');
            $table->boolean('qualified_flag')->default(false);
            $table->dateTime('first_qualified_at')->nullable();
            $table->dateTime('last_kyc_event_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->foreign('acquisition_id', 'fk_referral_kyc_progress_acquisition_id')
                  ->references('id')->on('referral_acquisitions');
            
            $table->unique('acquisition_id', 'uk_referral_kyc_progress_acquisition');
            $table->index('acquired_user_id', 'idx_referral_kyc_progress_user');
            $table->index('kyc_status', 'idx_referral_kyc_progress_status');
            $table->index('qualified_flag', 'idx_referral_kyc_progress_qualified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_kyc_progress');
    }
};
