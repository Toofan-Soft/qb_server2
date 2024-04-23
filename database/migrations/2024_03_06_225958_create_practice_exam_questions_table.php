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
        Schema::create('practice_exam_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('practice_exam_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('combination_id')->nullable();
                // In (select combination_id from question_choices_combination b where b.question_id = question_id)
            $table->integer('answer')->nullable();
            $table->bigInteger('answer_duration');
            $table->primary(['practice_exam_id','question_id']);

            $table->foreign('practice_exam_id')
            ->references('id')
            ->on('practice_exams')
            ->onDelete('cascade');

            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->onDelete('cascade');
        });

          //add a check constraint
        //DB::statement('ALTER TABLE question_forms ADD CONSTRAINT check_combination_id CHECK (combination_id IN (SELECT combination_id FROM question_choices_combination WHERE question_id = question_forms.question_id))');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_exam_questions');
    }
};
