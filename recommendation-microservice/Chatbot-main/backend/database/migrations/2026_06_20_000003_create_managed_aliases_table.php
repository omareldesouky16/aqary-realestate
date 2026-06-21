<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('managed_aliases', function (Blueprint $table): void {
            $table->id();
            $table->string('preference_type', 40);
            $table->unsignedBigInteger('canonical_id');
            $table->string('canonical_name');
            $table->string('alias');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['preference_type', 'active']);
            $table->index(['preference_type', 'alias']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managed_aliases');
    }
};
