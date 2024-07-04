<?php

use App\Enums\QuestionTypeEnum;
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
        Schema::create('real_exam_question_types', function (Blueprint $table) {
            $table->enum('question_type', QuestionTypeEnum::values());
            $table->integer('questions_count');
            $table->float('question_score');
            
            $table->unsignedBigInteger('real_exam_id');
            $table->foreign('real_exam_id')
            ->references('id')
            ->on('real_exams')
            ->onDelete('cascade');

            $table->primary(['real_exam_id', 'question_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_exam_question_types');
    }
};
