<?php

namespace App\Models;

use App\Helpers\DatetimeHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RealExam extends Model
{
    use HasFactory;
    public $timestamps = false;
    // protected $dateFormat = 'U';
    protected $fillable = [
        // 'user_id',
        'difficulty_level',
        'form_configuration_method',
        'forms_count',
        'form_name_method',
        'datetime',
        'duration',
        'type',
        'exam_type',
        'note',
        'course_lecturer_id',
        'language',
    ];

    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
        // 'language' => LanguageEnum::class,
        // 'difficulty_level' => ExamDifficultyLevelEnum::class,
        // 'form_configuration_method' => FormConfigurationMethodEnum::class,
        // 'form_name_method' => FormNameMethodEnum::class,
        // 'type' => RealExamTypeEnum::class,
        // 'exam_type' => ExamTypeEnum::class,
        // 'datetime' => 'datetime',
    ];

    public function getDateTimeAttribute($value)
    {
        // return strtotime($value); // previous (NSR)
        // return date('Y-m-d H:i:s', strtotime($value)); // new (M7D)
        return DatetimeHelper::convertDateTimeToLong($value);
    }

    // previous (NSR)
    // public function setDateTimeAttribute($value)
    // {
    //     $this->attributes['datetime'] = date('Y-m-d H:i:s', $value);
    // }

    public function setDateTimeAttribute($value)
    {
        // $this->attributes['datetime'] = date('Y-m-d H:i:s', $value); // previous (NSR)
        // $this->attributes['datetime'] = DatetimeHelper::convertMillisecondsToTimestamp($value); // new (M7D)
        $this->attributes['datetime'] = DatetimeHelper::convertLongToDateTime($value);
    }

    public function course_lecturer() : BelongsTo {
        return $this->BelongsTo(CourseLecturer::class);
    }

    public function forms() : HasMany {
        return $this->HasMany(Form::class);
    }
    public function real_exam_question_types() : HasMany {
        return $this->HasMany(RealExamQuestionType::class);
    }
    public function online_exam() : HasOne {
        return $this->HasOne(OnlineExam::class);
    }

    public function paper_exam() : HasOne {
        return $this->HasOne(PaperExam::class);
    }
}
