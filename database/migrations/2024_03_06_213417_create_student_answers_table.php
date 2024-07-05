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
        Schema::create('student_answers', function (Blueprint $table) {
            $table->integer('answer')->nullable();
            $table->integer('answer_duration')->nullable();
            
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')
            ->references('id')
            ->on('students')
            ->onDelete('restrict');
            
            // $table->foreign('question_id')
            // ->references('question_id')
            // ->on('form_questions')
            // ->onDelete('cascade');
            
            // $table->foreign('form_id')
            // ->references('form_id')
            // ->on('form_questions')
            // ->onDelete('cascade');
            
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('form_id');
            $table->foreign(['question_id', 'form_id'])
            ->references(['question_id', 'form_id'])
            ->on('form_questions')
            ->onDelete('restrict');
            
            $table->primary(['student_id','question_id', 'form_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
