<?php

namespace App\Models;

use App\Enums\ExamStatusEnum;
use App\Enums\OnlineExamStateEnum;
use App\Enums\ExamConductMethodEnum;
use App\Enums\ExamProcedureMethodEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OnlineExam extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;
    protected $fillable = [
        'id',
        'proctor_id',
        'status',
        'conduct_method',
        'exam_datetime_notification_datetime',
        'result_notification_datetime',
    ];

    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
        'conduct_method' => ExamConductMethodEnum::class,
        'exam_datetime_notification_datetime' => 'datetime',
        'result_notification_datetime' => 'datetime',
        // 'status' => ExamStatusEnum::class,
    ];

    public function student_online_exams() : HasMany {
        return $this->HasMany(StudentOnlineExam::class);
    }

    public function employee() : BelongsTo {
        return $this->BelongsTo(Employee::class);
    }

    public function real_exam() : BelongsTo {
        return $this->BelongsTo(RealExam::class);
    }

}
