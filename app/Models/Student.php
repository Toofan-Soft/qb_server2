<?php

namespace App\Models;

use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'arabic_name',
        'english_name',
        'phone',
        'image_url',
        'birthdate',
        'user_id',
        'gender',
        'academic_id',
    ];
    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
        // 'gender' => GenderEnum::class,
        // 'birthdate' => 'datetime',
    ];

    public function student_courses() : HasMany {
        return $this->HasMany(CourseStudent::class);
    }

    public function student_answers() : HasMany {
        return $this->HasMany(StudentAnswer::class);
    }

    public function student_online_exams() : HasMany {
        return $this->HasMany(StudentOnlineExam::class);
    }

    public function user() : BelongsTo {
        return $this->BelongsTo(User::class);
    }


}
