<?php

use App\Enums\OwnerTypeEnum;
use App\Enums\UserStatusEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email')->Unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('status', UserStatusEnum::values());
            $table->enum('owner_type', OwnerTypeEnum::values());
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
