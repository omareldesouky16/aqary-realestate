<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_listing_features', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
        });

        Schema::create('chatbot_listing_feature', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('listing_id')->constrained('chatbot_listings')->cascadeOnDelete();
            $table->foreignId('feature_id')->constrained('chatbot_listing_features')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['listing_id', 'feature_id']);
            $table->index(['listing_id', 'feature_id'], 'chatbot_listing_feature_listing_feature_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_listing_feature');
        Schema::dropIfExists('chatbot_listing_features');
    }
};
