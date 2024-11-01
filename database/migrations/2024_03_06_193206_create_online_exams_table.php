<?php

use App\Enums\ExamStateEnum;
use App\Enums\OnlineExamStatusEnum;
use App\Enums\OnlineExamStateEnum;
use Illuminate\Support\Facades\DB;
use App\Enums\ExamConductMethodEnum;
use App\Enums\ExamProcedureMethodEnum;
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
        Schema::create('online_exams', function (Blueprint $table) {
            $table->enum('status', OnlineExamStatusEnum::values());              //***  change OnlineExamStateEnum into ExamStateEnum
            $table->enum('conduct_method', ExamConductMethodEnum::values());
            $table->timestamp('exam_datetime_notification_datetime');
            $table->timestamp('result_notification_datetime');  // suggest after 1 hour by $table->timestamp('result_notification_date')->default(DB::raw('CURRENT_TIMESTAMP + INTERVAL 1 HOUR'));

            $table->unsignedBigInteger('id')->primary(); 
            $table->foreign('id')
                ->references('id')
                ->on('real_exams')
                ->onDelete('cascade');

            $table->unsignedBigInteger('proctor_id')->nullable();
            $table->foreign('proctor_id')
                ->references('id')
                ->on('employees')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_exams');
    }
};
