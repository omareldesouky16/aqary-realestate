<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chat_logs')) {
            return;
        }

        Schema::table('chat_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('chat_logs', 'session_id')) {
                $table->uuid('session_id')->index();
            }

            if (! Schema::hasColumn('chat_logs', 'role')) {
                $table->string('role', 20);
            }

            if (! Schema::hasColumn('chat_logs', 'message')) {
                $table->text('message');
            }

            if (! Schema::hasColumn('chat_logs', 'intent_detected')) {
                $table->string('intent_detected', 60)->nullable()->index();
            }

            if (! Schema::hasColumn('chat_logs', 'extracted_data')) {
                $table->json('extracted_data')->nullable();
            }

            if (! Schema::hasIndex('chat_logs', 'chat_logs_session_created_at_idx')) {
                $table->index(['session_id', 'created_at'], 'chat_logs_session_created_at_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('chat_logs')) {
            return;
        }

        Schema::table('chat_logs', function (Blueprint $table): void {
            $table->dropIndex('chat_logs_session_created_at_idx');
        });
    }
};
