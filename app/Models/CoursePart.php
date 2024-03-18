<?php

namespace App\Models;

use App\Enums\CoursePartsEnum;
use App\Enums\CourseStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoursePart extends Model
{
    use HasFactory;
    public $timestamps = false;
    
    protected $fillable = [
        'course_id',
        'description',
        'part_id',
        'status',
    ];

    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
        'part_id' => CoursePartsEnum::class,
        'status' => CourseStatusEnum::class,
    ];

    public function course() : BelongsTo {
        return $this->BelongsTo(Course::class);
    }

    public function chapters() : HasMany {
        return $this->HasMany(Chapter::class);
    }

    public function department_course_parts() : HasMany {
        return $this->HasMany(DepartmentCoursePart::class);
    }
}
