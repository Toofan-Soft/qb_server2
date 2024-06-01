<?php

use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use App\Enums\QualificationEnum;
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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('arabic_name') ;
            $table->string('english_name');
            $table->string('phone')->unique()->nullable();
            $table->string('image_url')->nullable();
            $table->enum('job_type', JobTypeEnum::values());
            $table->enum('qualification', QualificationEnum::values());
            $table->string('specialization')->nullable();
            $table->enum('gender', GenderEnum::values());
            
            $table->uuid('user_id')->unique()->nullable();
            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
