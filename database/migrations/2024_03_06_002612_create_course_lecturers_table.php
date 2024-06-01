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
        Schema::create('course_lecturers', function (Blueprint $table) {
            $table->id();
            $table->integer('academic_year');
            
            $table->unsignedBigInteger('department_course_part_id');
            $table->foreign('department_course_part_id')
            ->references('id')
            ->on('department_course_parts')
            ->onDelete('cascade');
            
            $table->unsignedBigInteger('lecturer_id');
            $table->foreign('lecturer_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_lecturers');
    }
};
