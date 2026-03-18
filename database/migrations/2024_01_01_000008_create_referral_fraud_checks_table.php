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
        Schema::create('referral_fraud_checks', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->unsignedBigInteger('acquisition_id');
            $table->enum('check_type', [
                'self_referral',
                'duplicate_phone',
                'duplicate_email',
                'duplicate_device',
                'duplicate_national_id',
                'velocity_limit',
                'manual_review'
            ]);
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->enum('check_result', ['pass', 'fail', 'review']);
            $table->string('check_value', 255)->nullable();
            $table->string('reviewed_by', 100)->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('acquisition_id', 'fk_referral_fraud_checks_acquisition_id')
                  ->references('id')->on('referral_acquisitions');
            
            $table->index('acquisition_id', 'idx_referral_fraud_checks_acquisition');
            $table->index('check_result', 'idx_referral_fraud_checks_result');
            $table->index('check_type', 'idx_referral_fraud_checks_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_fraud_checks');
    }
};
