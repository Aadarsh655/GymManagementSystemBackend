<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('image')->nullable();
            $table->text('comment');  
            $table->integer('rating'); 
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
