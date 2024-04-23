<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Enums\JobTypeEnum;
use App\Enums\QualificationEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'arabic_name',
        'english_name',
        'phone',
        'image_url',
        'job_type',
        'qualification',
        'specialization',
        'gender',
        'user_id',
    ];

    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
        'job_type' => JobTypeEnum::class,
        'qualification' => QualificationEnum::class,
        'gender' => GenderEnum::class,
    ];

    public function user() : BelongsTo {
        return $this->BelongsTo(User::class);
    }

    public function course_lecturers() : HasMany {
        return $this->HasMany(CourseLecturer::class);
    }
    public function onlin_exams() : HasMany {
        return $this->HasMany(OnlineExam::class);
    }





}
