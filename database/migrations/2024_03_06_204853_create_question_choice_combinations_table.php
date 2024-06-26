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
        Schema::create('question_choices_combinations', function (Blueprint $table) {
            // $table->unsignedBigInteger('combination_id')->autoIncrement();
            $table->unsignedBigInteger('combination_id');
            // $table->unsignedBigInteger('combination_id')->autoIncrement();  // Add ->autoIncrement() for auto-incrementing ID
            // يحتاج الي جعلة يتزايد بشكل تلقائي لكل سؤال على حده، بحيث يبدا من 1 لكل سؤال 
            
            $table->string('combination_choices'); ///////// OR  $table->integer('related_ids')->nullable();
            // Then in two ways we casts this column in model to array
            
            $table->unsignedBigInteger('question_id');
            $table->foreign('question_id')
            ->references('id')
            ->on('questions')
            ->onDelete('cascade');

            $table->primary(['combination_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_choices_combinations');
    }
};
