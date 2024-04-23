<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Form extends Model
{
    use HasFactory;
    public $timestamps = false;  
    protected $fillable = [
        'real_exam_id',

    ];

    public function form_questions() : HasMany {
        return $this->HasMany(FormQuestion::class);
    }

    public function real_exam() : BelongsTo {
        return $this->BelongsTo(RealExam::class);
    }

}
