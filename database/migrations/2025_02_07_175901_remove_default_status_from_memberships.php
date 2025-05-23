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
        Schema::table('memberships', function (Blueprint $table) {
            $table->enum('status', ['Active', 'Inactive'])->change();
        });
    }
    
    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->change();
        });
    }
    
};
