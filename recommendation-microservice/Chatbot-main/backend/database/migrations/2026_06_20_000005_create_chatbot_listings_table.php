<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_listings', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('url');
            $table->unsignedBigInteger('price')->index();
            $table->unsignedInteger('area')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->string('furnished_status')->nullable();
            $table->unsignedBigInteger('location_id')->index();
            $table->string('location_name');
            $table->unsignedBigInteger('property_type_id')->index();
            $table->string('cover_image_url')->nullable();
            $table->boolean('is_promoted')->default(false);
            $table->string('status', 20)->default('active')->index();
            $table->string('payment_type', 20)->default('cash')->index();
            $table->string('seller_phone')->nullable();
            $table->timestamps();

            $table->index(['status', 'payment_type', 'location_id', 'property_type_id', 'price'], 'chatbot_listings_scope_price_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_listings');
    }
};
