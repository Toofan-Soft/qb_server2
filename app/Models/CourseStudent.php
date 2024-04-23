<?php

namespace App\Models;

use App\Enums\CourseStudentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseStudent extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing =false;
    protected $primaryKey = ['department_course_id', 'student_id']; // Specifies the composite primary key.
    protected $fillable = [
        'department_course_id',
        'student_id',
        'academic_year',
        'status',
    ];
    protected $casts = [
        'status' => CourseStudentStatusEnum::class,

    ];

    public function department_course() : BelongsTo {
        return $this->BelongsTo(DepartmentCourse::class);
    }

    public function student() : BelongsTo {
        return $this->BelongsTo(Student::class);

    }

}
