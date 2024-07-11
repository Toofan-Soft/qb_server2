<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\StudentOnlineExamStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentOnlineExam extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;
    protected $dateFormat = 'U';
    protected $primaryKey = ['student_id','online_exam_id'];
    protected $fillable = [
        'student_id',
        'online_exam_id',
        'start_datetime',
        'end_datetime',
        'form_id',
        'status',
    ];

    protected $casts = [
        // 'start_datetime' => 'datetime',
        // 'end_datetime' => 'datetime',
        // 'status' => StudentOnlineExamStatusEnum::class,
    ];

    // public function getStartDatetimeAttribute($value)
    // {
    //     return $value ? strtotime($value) : null;
    // }

    // public function setStartDatetimeAttribute($value)
    // {
    //     $this->attributes['start_datetime'] = $value ? date('Y-m-d H:i:s', $value) : null;
    // }

    // public function getEndDatetimeAttribute($value)
    // {
    //     return $value ? strtotime($value) : null;
    // }

    // public function setEndDatetimeAttribute($value)
    // {
    //     $this->attributes['end_datetime'] = $value ? date('Y-m-d H:i:s', $value) : null;
    // }
    public function student() : BelongsTo {
        return $this->BelongsTo(Student::class);
    }
    public function online_exam() : BelongsTo {
        return $this->BelongsTo(OnlineExam::class);
    }

}
