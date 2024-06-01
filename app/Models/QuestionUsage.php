<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionUsage extends Model
{
    use HasFactory;
    public $timestamps = false;

    public $incrementing = false; // This indicates that the model does not auto-increment its primary key.

    protected $primaryKey = ['question_id'];

    protected $fillable = [
        'question_id',
        'online_exam_last_selection_datetime',
        'practice_exam_last_selection_datetime',
        'paper_exam_last_selection_datetime',
        
        'online_exam_selection_times_count',
        'practice_exam_selection_times_count',
        'paper_exam_selection_times_count',

        'online_exam_correct_answers_count',
        'online_exam_incorrect_answers_count',
        'practice_exam_incorrect_answers_count',
        'practice_exam_correct_answers_count',

    ];

    protected $casts = [
        // 'online_exam_selection_times_count' =>'datetime',
        // 'practice_exam_selection_times_count'=>'datetime',
        // 'paper_exam_selection_times_count'=>'datetime',
    ];

    public function getOnlineExamLastSelectionDatetimeAttribute($value)
    {
        return $value ? strtotime($value) : null;
    }

    public function setOnlineExamLastSelectionDatetimeAttribute($value)
    {
        $this->attributes['online_exam_last_selection_datetime'] = $value ? date('Y-m-d H:i:s', $value) : null;
    }

    public function getPracticeExamLastSelectionDatetimeAttribute($value)
    {
        return $value ? strtotime($value) : null;
    }

    public function setPracticeExamLastSelectionDatetimeAttribute($value)
    {
        $this->attributes['practice_exam_last_selection_datetime'] = $value ? date('Y-m-d H:i:s', $value) : null;
    }

    public function getPaperExamLastSelectionDatetimeAttribute($value)
    {
        return $value ? strtotime($value) : null;
    }

    public function setPaperExamLastSelectionDatetimeAttribute($value)
    {
        $this->attributes['paper_exam_last_selection_datetime'] = $value ? date('Y-m-d H:i:s', $value) : null;
    }
    public function question() : BelongsTo {
        return $this->BelongsTo(Question::class);
    }

}
