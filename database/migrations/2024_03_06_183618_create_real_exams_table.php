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

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('real_exams', function (Blueprint $table) {
            $table->id();
            $table->enum('language', LanguageEnum::values())->default(LanguageEnum::ARABIC->value);
            $table->enum('difficulty_level', ExamDifficultyLevelEnum::values());
            $table->enum('form_configuration_method', FormConfigurationMethodEnum::values());
            $table->integer('forms_count')->default(1);
            $table->enum('form_name_method', FormNameEnum::values());
            $table->timestamp('datetime');
            $table->integer('duration');
            $table->enum('type',RealExamTypeEnum::values());
            $table->enum('exam_type', ExamTypeEnum::values()); /////need to add  // mid , month, final
            $table->text('note')->nullable();
            $table->unsignedBigInteger('department_course_part_id');

            $table->foreign('department_course_part_id')
            ->references('id')
            ->on('department_course_parts')
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
