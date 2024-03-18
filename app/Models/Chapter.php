<?php

namespace App\Models;

use App\Enums\CoursePartsEnum;
use App\Enums\ChapterStateEnum;
use App\Enums\ChapterStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chapter extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'arabic_title',
        'english_title',
        'description',
        'status',
        'course_part_id',
    ];

    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
        'status' => ChapterStatusEnum::class,
        'course_part_id' => CoursePartsEnum::class,

    ];

    public function course_part() : BelongsTo {
        return $this->BelongsTo(CoursePart::class);
    }

    public function topics() : HasMany {
        return $this->HasMany(Topic::class);
    }
}
