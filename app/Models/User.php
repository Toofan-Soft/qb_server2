<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

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
        // 'status' =>UserStatusEnum::class,
        // 'owner_type' => OwnerTypeEnum::class,
        'password' => 'hashed',
        // 'email_verified_at' => 'datetime',
    ];

    public function getEmailVerifiedAtAttribute($value)
    {
        return strtotime($value);
    }

    // public function setEmailVerifiedAtAttribute($value)
    // {
    //     $this->attributes['email_verified_at'] = date('Y-m-d H:i:s', $value);
    // }
    
    public function user_roles() : HasMany {
        return $this->HasMany(UserRole::class);
    }

    public function student() : HasOne {
        return $this->HasOne(Student::class);
    }

    public function employee() : HasOne {
        return $this->HasOne(Employee::class);
    }

    public function practice_exams() : HasMany {
        return $this->HasMany(PracticeExam::class);
    }
    public function guest() : HasOne {
        return $this->HasOne(Guest::class);
    }

    public function favorite_questions() : HasMany {
        return $this->HasMany(FavoriteQuestion::class);
    }

}
