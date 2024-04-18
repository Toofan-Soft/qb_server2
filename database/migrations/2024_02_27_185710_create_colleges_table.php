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
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->string('arabic_name');
            $table->string('english_name');
            $table->string('logo_url')->nullable() ;
            $table->text('description')->nullable() ;
            $table->string('phone')->unique()->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('facebook')->nullable() ;
            $table->string('x_platform')->nullable() ;
            $table->string('youtube')->nullable() ;
            $table->string('telegram')->nullable();

            $table->unique(['arabic_name', 'english_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colleges');
    }
};
