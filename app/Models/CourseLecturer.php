<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseLecturer extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'academic_year',
        'department_course_part_id',
        'lecturer_id',
    ];


    public function employee() : BelongsTo {
        return $this->BelongsTo(Employee::class);
    }

    public function real_exams() : HasMany {
        return $this->HasMany(RealExam::class);
    }

    public function department_course_part() : BelongsTo {
        return $this->belongsTo(DepartmentCoursePart::class);
    }
}
