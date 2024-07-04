<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('form_questions', function (Blueprint $table) {
            // In (select combination_id from question_choices_combination b where b.question_id = question_id)
            $table->unsignedBigInteger('combination_id')->nullable();
            
            $table->unsignedBigInteger('question_id');
            $table->foreign('question_id')
            ->references('id')
            ->on('questions')
            ->onDelete('restrict');
            
            $table->unsignedBigInteger('form_id');
            $table->foreign('form_id')
            ->references('id')
            ->on('forms')
            ->onDelete('cascade');
            
            $table->primary(['form_id', 'question_id']);
            // $table->unique(['form_id', 'question_id']);

            // we can make that constraint as :
               // Add foreign key constraint with a subquery
            // $table->foreign('combination_id')
            // ->references('combination_id')
            // ->on('question_choices_combination')
            // ->where('question_id', '=', DB::raw('question_id'));
        });


        //OR this as a check constraint
        //DB::statement('ALTER TABLE question_forms ADD CONSTRAINT check_combination_id CHECK (combination_id IN (SELECT combination_id FROM question_choices_combination WHERE question_id = question_forms.question_id))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_questions');
    }
};
