<?php

namespace App\Models;

use App\Enums\QuestionTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RealExamQuestionType extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false; // This indicates that the model does not auto-increment its primary key.
    protected $primaryKey  = ['real_exam_id', 'question_type'];
    protected $fillable = [
        'real_exam_id',
        'question_type',
        'questions_count',
        'question_score',
    ];

    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
        // 'question_type' => QuestionTypeEnum::class,
    ];

    public function real_exam() : BelongsTo {
        return $this->BelongsTo(RealExam::class);
    }

}
