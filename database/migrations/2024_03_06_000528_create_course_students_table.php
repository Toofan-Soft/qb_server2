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
            $table->enum('status', CourseStudentStatusEnum::values());
            $table->integer('academic_year');
            
            $table->unsignedBigInteger('department_course_id');
            $table->foreign('department_course_id')
            ->references('id')
            ->on('department_courses')
            ->onDelete('cascade');
            
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')
            ->references('id')
            ->on('students')
            ->onDelete('cascade');
            
            $table->primary(['department_course_id', 'student_id']);
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
