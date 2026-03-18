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
        Schema::create('outbox_messages', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('topic', 100);
            $table->string('aggregate_type', 50);
            $table->string('aggregate_id', 100);
            $table->json('payload');
            $table->enum('status', ['pending', 'published', 'failed'])->default('pending');
            $table->dateTime('published_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_outbox_messages_status');
            $table->index('topic', 'idx_outbox_messages_topic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbox_messages');
    }
};
