<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DepartmentCoursePartTopic;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DepartmentCoursePart extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'department_course_id',
        'course_part_id',
        'note',
        'score',
        'lectures_count',
        'lecture_duration',

    ];

    public function course_part() : BelongsTo {
        return $this->BelongsTo(CoursePart::class);
    }
    public function department_course() : BelongsTo {
        return $this->BelongsTo(DepartmentCourse::class);
    }

    public function department_course_part_topics() : HasMany {
        return $this->HasMany(DepartmentCoursePartTopic::class);
    }
    public function course_lecturers() : HasMany {
        return $this->HasMany(CourseLecturer::class);
    }
    public function practice_exams() : HasMany {
        return $this->HasMany(PracticeExam::class);
    }

}
