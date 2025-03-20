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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id(); // Laravel’s auto-incremented ID
            $table->unsignedBigInteger('device_id'); // Store the ID from the biometric device
            $table->unsignedBigInteger('user_id'); // Employee/User ID
            $table->timestamp('timestamp'); // Attendance time
            $table->string('status')->default('Present'); // Default to "Present"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
