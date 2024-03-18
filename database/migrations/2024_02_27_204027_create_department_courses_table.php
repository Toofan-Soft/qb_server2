<?php

use App\Enums\LevelsEnum;
use App\Enums\SemesterEnum;
use App\Enums\CoursePartsEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('department_courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->enum('level', LevelsEnum::values() );
            $table->enum('semester',SemesterEnum::values());
            $table->unsignedBigInteger('department_id');
            // $table->timestamps();
         // Define composite foreign key constraint
            $table->foreign('course_id')
            ->references('id') // Reference both primary key columns
            ->on('courses')
            ->onDelete('cascade');

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_courses');
    }
};
