<?php

namespace App\Models;

use App\Helpers\DatetimeHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OnlineExam extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;
    // protected $dateFormat = 'U';
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
        // 'conduct_method' => ExamConductMethodEnum::class,
        // 'exam_datetime_notification_datetime' => 'datetime',
        // 'result_notification_datetime' => 'datetime',
        // 'status' => ExamStatusEnum::class,
    ];

    public function getExamDatetimeNotificationDatetimeAttribute($value)
    {
        // return $value ? strtotime($value) : null;
        return DatetimeHelper::convertDateTimeToLong($value);
    }


    public function setExamDatetimeNotificationDatetimeAttribute($value) // NSR
    {
        // $this->attributes['exam_datetime_notification_datetime'] = $value ? date('Y-m-d H:i:s', $value) : null; // NSR
        //  $this->attributes['exam_datetime_notification_datetime'] = DatetimeHelper::convertMillisecondsToTimestamp($value); // M7D
        $this->attributes['exam_datetime_notification_datetime'] = DatetimeHelper::convertLongToDateTime($value);
    }

    public function getResultNotificationDatetimeAttribute($value)
    {
        // return $value ? strtotime($value) : null;
        return DatetimeHelper::convertDateTimeToLong($value);
    }

    
    public function setResultNotificationDatetimeAttribute($value)
    {
        // $this->attributes['result_notification_datetime'] = $value ? date('Y-m-d H:i:s', $value) : null; // NSR
        // $this->attributes['result_notification_datetime'] = DatetimeHelper::convertMillisecondsToTimestamp($value); // M7D
        $this->attributes['result_notification_datetime'] = DatetimeHelper::convertLongToDateTime($value);
    }

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
