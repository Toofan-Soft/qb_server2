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
    protected $primaryKey = ['student_id','online_exam_id'];
    protected $fillable = [
        'student_id',
        'online_exam_id',
        'start_datetime',
        'end_datetime',
        'status',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        // 'status' => StudentOnlineExamStatusEnum::class,
    ];
    public function student() : BelongsTo {
        return $this->BelongsTo(Student::class);
    }
    public function online_exam() : BelongsTo {
        return $this->BelongsTo(OnlineExam::class);
    }

}
