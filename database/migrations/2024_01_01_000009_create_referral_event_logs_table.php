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
        Schema::create('referral_event_logs', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('aggregate_type', 50);
            $table->string('aggregate_id', 100);
            $table->string('event_type', 100);
            $table->string('event_source', 100);
            $table->string('idempotency_key', 150)->nullable();
            $table->string('correlation_id', 150)->nullable();
            $table->json('payload');
            $table->dateTime('occurred_at');
            $table->timestamps();

            $table->index(['aggregate_type', 'aggregate_id'], 'idx_referral_event_logs_aggregate');
            $table->index('event_type', 'idx_referral_event_logs_event_type');
            $table->index('occurred_at', 'idx_referral_event_logs_occurred_at');
            $table->index('idempotency_key', 'idx_referral_event_logs_idempotency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_event_logs');
    }
};
