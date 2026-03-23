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
        Schema::create('site_media_assets', function (Blueprint $table) {
            $table->id();
            $table->string('section');
            $table->string('item_key')->unique();
            $table->string('label');
            $table->string('usage_hint')->nullable();
            $table->string('media_type')->default('image');
            $table->string('recommended_size')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_media_assets');
    }
};

