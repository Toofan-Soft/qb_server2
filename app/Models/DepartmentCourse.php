<?php

namespace App\Models;

use App\Enums\LevelsEnum;
use App\Models\CoursePart;
use App\Enums\SemesterEnum;
use App\Enums\CoursePartsEnum;
use App\Enums\CourseStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DepartmentCourse extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'course_id',
        'department_id',
        'level',
        'semester',
    ];

    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
        'level' => LevelsEnum::class,
        'semester' => SemesterEnum::class,
    ];
    public function department() : BelongsTo {
        return $this->BelongsTo(Department::class);
    }

    public function course_students() : HasMany {
        return $this->HasMany(CourseStudent::class);
    }

    public function course_lecturers() : HasMany {
        return $this->HasMany(CourseLecturer::class);
    }

    public function department_course_parts() : HasMany {
        return $this->HasMany(DepartmentCoursePart::class);
    }

    public function course() : BelongsTo {
        return $this->BelongsTo(Course::class);
    }
}
