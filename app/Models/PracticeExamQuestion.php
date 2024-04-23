<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PracticeExamQuestion extends Model
{
    use HasFactory;
    public $timestamps = false;

    public $incrementing = false; // This indicates that the model does not auto-increment its primary key.
    protected $primaryKey  = ['practice_exam_id','question_id'];
    protected $fillable = [
        'practice_exam_id',
        'question_id',
        'combination_id',
        'answer',
        'answer_duration',
    ];


    public function question() : BelongsTo {
        return $this->BelongsTo(Question::class);
    }


    public function practise_exam() : BelongsTo {
        return $this->BelongsTo(PracticeExam::class);
    }

}
