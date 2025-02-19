<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blog', function (Blueprint $table) {
            DB::statement("ALTER TABLE blog CHANGE COLUMN status status ENUM('Active', 'Inactive') NOT NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog', function (Blueprint $table) {
            DB::statement("ALTER TABLE blog CHANGE COLUMN status status TINYINT(1) NOT NULL");
        });
    }
};
