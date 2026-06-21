<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('session_id')->index();
            $table->string('role', 20);
            $table->text('message');
            $table->string('intent_detected', 60)->nullable()->index();
            $table->json('extracted_data')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'created_at'], 'chat_logs_session_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_logs');
    }
};
