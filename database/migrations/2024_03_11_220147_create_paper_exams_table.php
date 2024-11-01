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
        Schema::create('paper_exams', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('course_lecturer_name')->nullable();

            $table->foreign('id')
            ->references('id')
            ->on('real_exams')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paper_exams');
    }
};
