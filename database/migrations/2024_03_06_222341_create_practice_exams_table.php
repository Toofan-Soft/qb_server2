<?php

use App\Enums\LanguageEnum;
use App\Enums\ExamStateEnum;
use App\Enums\ExamStatusEnum;
use App\Enums\ExamConductMethodEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\ExamProcedureMethodEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Enums\ExamFormConfigurationMethodEnum;
use App\Enums\PracticeExamStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('practice_exams', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->enum('language', LanguageEnum::values());
            $table->timestamp('datetime');
            $table->integer('duration');
            $table->enum('difficulty_level', ExamDifficultyLevelEnum::values());
            $table->enum('conduct_method', ExamConductMethodEnum::values());
            $table->enum('status', PracticeExamStatusEnum::values())->default(PracticeExamStatusEnum::NEW->value);

            $table->unsignedBigInteger('department_course_part_id');
            $table->foreign('department_course_part_id')
            ->references('id')
            ->on('department_course_parts')
            ->onDelete('restrict');
            
            $table->uuid('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_exams');
    }
};
