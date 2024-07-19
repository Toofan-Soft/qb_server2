<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentAnswer extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false; // This indicates that the model does not auto-increment its primary key.
    protected $primaryKey  = ['student_id','form_id','question_id'];
    protected $fillable = [
        'student_id',
        'question_id',
        'form_id',
        'answer',
        'answer_duration',
    ];

    public function form_question() : BelongsTo {
        return $this->BelongsTo(FormQuestion::class);

    }

    public function student() : BelongsTo {
        return $this->BelongsTo(Student::class);
    }


}
