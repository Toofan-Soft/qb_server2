<?php

use App\Enums\CourseStudentStatusEnum;
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
        Schema::create('course_students', function (Blueprint $table) {
            $table->unsignedBigInteger('department_course_id');
            $table->unsignedBigInteger('student_id');
             $table->enum('status', CourseStudentStatusEnum::values())->default(CourseStudentStatusEnum::ACTIVE->value);

            $table->integer('academic_year');

            $table->primary(['department_course_id', 'student_id']);

            $table->foreign('department_course_id')
            ->references('id')
            ->on('department_courses')
            ->onDelete('cascade');

            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_students');
    }
};
