<?php

namespace App\Models;

use App\Enums\LanguageEnum;
use App\Enums\ExamStateEnum;
use App\Enums\ExamStatusEnum;
use App\Helpers\DatetimeHelper;
use App\Enums\ExamConductMethodEnum;
use App\Enums\ExamDifficultyLevelEnum;
use App\Enums\ExamProcedureMethodEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PracticeExam extends Model
{
    use HasFactory;
    public $timestamps = false;
    // protected $dateFormat = 'U';
    protected $fillable = [
        'title',
        'language',
        'datetime',
        'duration',
        'difficulty_level',
        'conduct_method',
        'status',
        'department_course_part_id',
        'user_id',
    ];

    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
        // 'language' =>LanguageEnum::class,
        // 'difficulty_level' =>ExamDifficultyLevelEnum::class,
        // 'conduct_method' =>ExamConductMethodEnum::class,
        // 'status' =>ExamStatusEnum::class,
    ];

    public function getDateTimeAttribute($value)
    {
        return DatetimeHelper::convertDateTimeToLong($value);
    }

    // public function setDateTimeAttribute($value)
    // {
    //     $this->attributes['datetime'] = DatetimeHelper::convertLongToDateTime($value);
    // }

    public function user() : BelongsTo {
        return $this->BelongsTo(User::class);
    }

    public function practice_exam_question() : HasMany {
        return $this->HasMany(PracticeExamQuestion::class);
    }

    public function practice_exam_usage() : HasOne {
        return $this->HasOne(PracticeExamUsage::class);
    }

    public function department_course_part() : BelongsTo {
        return $this->belongsTo(DepartmentCoursePart::class);
    }
}
