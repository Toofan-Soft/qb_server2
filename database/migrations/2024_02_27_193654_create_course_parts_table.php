<?php

use App\Enums\CoursePartsEnum;
use App\Enums\CourseStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\LevelsCountEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_parts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('course_id'); // Foreign key
            $table->enum('part_id', CoursePartsEnum::values()); // Part of the primary key
            $table->enum('status', CourseStatusEnum::values());
            $table->string('description')->nullable();

            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_parts');
    }
};
