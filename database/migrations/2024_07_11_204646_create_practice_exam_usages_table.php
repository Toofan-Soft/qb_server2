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
        Schema::create('practice_exam_usages', function (Blueprint $table) {
            $table->timestamp('start_datetime')->nullable();
            $table->timestamp('last_suspended_datetime')->nullable();
            $table->integer('remaining_duration');

            $table->unsignedBigInteger('practice_exam_id')->primary();
            $table->foreign('practice_exam_id')
            ->references('id')
            ->on('practice_exams')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_exam_usages');
    }
};
