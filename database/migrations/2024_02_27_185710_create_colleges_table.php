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
            $table->string('arabic_name')->unique();
            $table->string('english_name')->unique();
            $table->string('logo_url')->nullable() ;
            $table->text('description')->nullable() ;
            $table->integer('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('facebook')->nullable() ;
            $table->string('x_platform')->nullable() ;
            $table->string('youtube')->nullable() ;
            $table->string('telegram')->nullable();

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
