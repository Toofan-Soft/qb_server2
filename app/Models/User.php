<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Support\Str;
use App\Enums\OwnerTypeEnum;
use App\Enums\UserStatusEnum;
use App\Traits\Uuids;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable , Uuids;

    public $incrementing = false; // Disable auto-incrementing for UUID
    protected $fillable = [
        'email',
        'email_verified_at',
        'password',
        'status',
        'owner_type',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'status' =>UserStatusEnum::class,
        'owner_type' => OwnerTypeEnum::class,
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
    ];


    public function user_roles() : HasMany {
        return $this->HasMany(UserRole::class);
    }

    public function students() : HasOne {
        return $this->HasOne(Student::class);
    }

    public function employees() : HasOne {
        return $this->HasOne(Employee::class);
    }

    public function practise_exams() : HasMany {
        return $this->HasMany(PracticeExam::class);
    }
    public function guests() : HasOne {
        return $this->HasOne(Guest::class);
    }

    public function favorite_questions() : HasMany {
        return $this->HasMany(FavoriteQuestion::class);
    }

}
