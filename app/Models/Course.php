<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'arabic_name',
        'english_name',

    ];

    public function course_parts() : HasMany {
        return $this->hasMany(CoursePart::class);
    }

    public function department_courses() : HasMany {
        return $this->HasMany(DepartmentCourse::class);
    }
}
