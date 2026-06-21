<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resolution_review_items', function (Blueprint $table): void {
            $table->id();
            $table->uuid('session_id')->index();
            $table->string('preference_type', 40);
            $table->string('status', 20);
            $table->text('raw_text')->nullable();
            $table->json('candidates')->nullable();
            $table->unsignedBigInteger('canonical_id')->nullable();
            $table->string('canonical_name')->nullable();
            $table->string('buyer_choice')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resolution_review_items');
    }
};
