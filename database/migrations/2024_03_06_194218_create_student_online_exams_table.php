<?php

use App\Enums\StudentOnlineExamStatusEnum;
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
        Schema::create('student_online_exams', function (Blueprint $table) {
            $table->timestamp('start_datetime')->nullable();
            $table->timestamp('end_datetime')->nullable();//
            $table->enum('status', StudentOnlineExamStatusEnum::values());
            
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')
            ->references('id')
            ->on('students')
            ->onDelete('cascade');
            
            $table->unsignedBigInteger('online_exam_id');
            $table->foreign('online_exam_id')
                ->references('id')
                ->on('online_exams')
                ->onDelete('cascade');

            $table->primary(['student_id', 'online_exam_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_online_exams');
    }
};
