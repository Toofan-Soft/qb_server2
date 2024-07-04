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
        Schema::create('department_course_part_topics', function (Blueprint $table) {

            $table->unsignedBigInteger('department_course_part_id');
            $table->foreign('department_course_part_id')
            ->references('id')
            ->on('department_course_parts')
            ->onDelete('cascade');
            
            $table->unsignedBigInteger('topic_id');
            $table->foreign('topic_id')
            ->references('id')
            ->on('topics')
            ->onDelete('restrict');
            
            $table->primary(['department_course_part_id', 'topic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_course_part_topics');
    }
};
