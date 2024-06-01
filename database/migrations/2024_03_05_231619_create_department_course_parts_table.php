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
        Schema::create('department_course_parts', function (Blueprint $table) {
            $table->id();
            $table->text('note')->nullable();
            $table->integer('score')->nullable();
            $table->integer('lectures_count')->nullable();
            $table->bigInteger('lecture_duration')->nullable();
            
            // Define composite foreign key constraint
            $table->unsignedBigInteger('course_part_id');
            $table->foreign('course_part_id')
            ->references('id') // Reference both primary key columns
            ->on('course_parts')
            ->onDelete('cascade');
            
            $table->unsignedBigInteger('department_course_id');
            $table->foreign('department_course_id')
                ->references('id')
                ->on('department_courses')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_course_parts');
    }
};
