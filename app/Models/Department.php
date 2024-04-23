<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'arabic_name',
        'english_name',
        'logo_url',
        'levels_count',
        'description',
        'college_id',
    ];
    public function college() : BelongsTo {
        return $this->BelongsTo(College::class);
    }

    public function department_courses() : HasMany {
        return $this->hasMany(DepartmentCourse::class);
    }

}
