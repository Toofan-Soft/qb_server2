<?php

use App\Enums\GenderEnum;
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
        Schema::create('students', function (Blueprint $table) {

            $table->id();
            $table->integer('academic_id');
            $table->string('arabic_name') ;
            $table->string('english_name');
            $table->integer('phone')->nullable();
            $table->string('image_url')->nullable();
            $table->enum('gender', GenderEnum::values());
            $table->timestamp('birthdate')->nullable();
            
            $table->uuid('user_id')->unique()->nullable();
            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
