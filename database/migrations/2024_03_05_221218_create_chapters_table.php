<?php


use App\Enums\ChapterStatusEnum;
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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->string('arabic_title') ;
            $table->string('english_title');
            $table->enum('status', ChapterStatusEnum::values())->default(ChapterStatusEnum::AVAILABLE->value);
            $table->text('description')->nullable();
            // $table->timestamps();
            
            $table->unsignedBigInteger('course_part_id');
            $table->foreign('course_part_id')
            ->references('id')
            ->on('course_parts')
            ->onDelete('cascade');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
