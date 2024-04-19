<?php

use App\Enums\AccessEnum;
use App\Enums\LanguageEnum;
use App\Enums\QuestionState;
use App\Enums\AccessStateEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\QuestionStateEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\AcceptancestatusEnum;
use App\Enums\AccessibilityStatusEnum;
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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('attachment')->nullable();
            $table->string('title')->nullable();
            $table->enum('type',QuestionTypeEnum::values());
            $table->float('difficulty_level') ;
            $table->enum('status',QuestionStatusEnum::values())->default(QuestionStatusEnum::NEW->value);
            $table->enum('accessability_status',AccessibilityStatusEnum::values());
            $table->bigInteger('estimated_answer_time');
            $table->enum('language',LanguageEnum::values()); // default ??
            $table->unsignedBigInteger('topic_id');

            $table->foreign('topic_id')
            ->references('id')
            ->on('topics')
            ->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
