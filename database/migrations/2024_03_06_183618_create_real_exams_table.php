<?php

use App\Enums\ExamTypeEnum;
use App\Enums\FormNameEnum;
use App\Enums\LanguageEnum;
use App\Enums\DataFormNameEnum;
use App\Enums\RealExamTypeEnum;
use App\Enums\ExamDifficultyLevelEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Enums\FormConfigurationMethodEnum;
use Illuminate\Database\Migrations\Migration;
use App\Enums\ExamFormConfigurationMethodEnum;
use App\Enums\FormNameMethodEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('real_exams', function (Blueprint $table) {
            $table->id();
            $table->enum('language', LanguageEnum::values());
            $table->enum('difficulty_level', ExamDifficultyLevelEnum::values());
            $table->enum('form_configuration_method', FormConfigurationMethodEnum::values());
            $table->integer('forms_count');
            $table->enum('form_name_method', FormNameMethodEnum::values());
            $table->timestamp('datetime');
            $table->integer('duration');
            $table->enum('type', ExamTypeEnum::values());
            $table->enum('exam_type', RealExamTypeEnum::values()); /////need to add  // mid , month, final
            $table->text('note')->nullable();
            
            $table->unsignedBigInteger('course_lecturer_id');
            $table->foreign('course_lecturer_id')
            ->references('id')
            ->on('course_lecturers')
            ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_exams');
    }
};
