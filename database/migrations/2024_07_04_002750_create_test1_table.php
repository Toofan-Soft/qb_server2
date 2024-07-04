<?php

use App\Enums\LevelsEnum;
use App\Enums\SemesterEnum;
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
        Schema::create('test1', function (Blueprint $table) {
            $table->id();
            // $table->unsignedInteger('level')->check("level IN LevelsEnum::values()");
            // $table->unsignedInteger('semester')->check("semester IN SemesterEnum::values()");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test1');
    }
};
