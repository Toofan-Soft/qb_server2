<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DepartmentCoursePartTopic extends Model
{
    use HasFactory;
    public $timestamps = false;

    public $incrementing = false; // This indicates that the model does not auto-increment its primary key.

    protected $primaryKey = ['department_course_part_id', 'topic_id']; // Specifies the composite primary key.

    protected $fillable = [
        'department_course_part_id',
        'topic_id',

    ];

    public function topic() : BelongsTo {
        return $this->BelongsTo(Topic::class);
    }

    public function department_course_parts() : BelongsTo {
        return $this->BelongsTo(DepartmentCoursePart::class);
    }
}
