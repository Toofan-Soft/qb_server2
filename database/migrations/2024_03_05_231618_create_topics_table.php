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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('arabic_title') ;
            $table->string('english_title');
            $table->text('description')->nullable();
            // $table->timestamps();
            
            $table->unsignedBigInteger('chapter_id');
            $table->foreign('chapter_id')
            ->references('id')
            ->on('chapters')
            ->onDelete('restrict');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
