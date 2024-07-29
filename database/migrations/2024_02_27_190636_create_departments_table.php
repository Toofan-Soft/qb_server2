<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\LevelsCountEnum;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('arabic_name');
            $table->string('english_name');
            $table->string('logo_url')->nullable();
            $table->enum('levels_count', LevelsCountEnum::values());
            $table->text('description')->nullable() ;
            // foreign key
            $table->unsignedBigInteger('college_id');
            $table->foreign('college_id')->references('id')->on('colleges') ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
