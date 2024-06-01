<?php

use App\Enums\ChoiceStatusEnum;
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
        Schema::create('choices', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('attachment')->nullable();
            $table->enum('status', ChoiceStatusEnum::values());
            
            $table->unsignedBigInteger('question_id');
            $table->foreign('question_id')
            ->references('id')
            ->on('questions')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('choices');
    }
};
