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
            $table->unsignedBigInteger('combination_id');
            // $table->unsignedBigInteger('combination_id')->autoIncrement();  // Add ->autoIncrement() for auto-incrementing ID
            $table->unsignedBigInteger('question_id');

            $table->string('combination_choices'); ///////// OR  $table->integer('related_ids')->nullable();
            // Then in two ways we casts this column in model to array

            $table->primary(['combination_id', 'question_id']);
            $table->foreign('question_id')
            ->references('id')
            ->on('questions')
            ->onDelete('cascade');
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
