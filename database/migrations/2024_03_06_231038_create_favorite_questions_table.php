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
        Schema::create('favorite_questions', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('combination_id')->nullable();
                // In (select combination_id from question_choices_combination b where b.question_id = question_id)

            $table->primary(['user_id','question_id']);

            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
 
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
        Schema::dropIfExists('favorite_questions');
    }
};
