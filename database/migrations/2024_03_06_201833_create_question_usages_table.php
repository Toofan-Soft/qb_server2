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
        Schema::create('question_usages', function (Blueprint $table) {
            $table->timestamp('online_exam_last_selection_datetime')->nullable();
            $table->timestamp('practice_exam_last_selection_datetime')->nullable();
            $table->timestamp('paper_exam_last_selection_datetime')->nullable();
            $table->integer('online_exam_selection_times_count')->nullable()->default(0);
            $table->integer('practice_exam_selection_times_count')->nullable()->default(0);
            $table->integer('paper_exam_selection_times_count')->nullable()->default(0);

            $table->integer('online_exam_correct_answers_count')->nullable()->default(0);
            $table->integer('online_exam_incorrect_answers_count')->nullable()->default(0);
            $table->integer('practice_exam_incorrect_answers_count')->nullable()->default(0);
            $table->integer('practice_exam_correct_answers_count')->nullable()->default(0);


            $table->unsignedBigInteger('question_id')->primary();
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
        Schema::dropIfExists('question_usages');
    }
};
