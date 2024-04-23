<?php

namespace App\Models;

use App\Enums\TrueFalseAnswerEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrueFalseQuestion extends Model
{


    use HasFactory;
    public $timestamps = false;

    public $incrementing = false; // This indicates that the model does not auto-increment its primary key.

    protected $primaryKey = ['question_id']; 

    protected $fillable = [
        'question_id',
        'answer',
    ];

    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
         'answer' =>  TrueFalseAnswerEnum::class,

    ];

    public function question() : BelongsTo {
        return $this->BelongsTo(Question::class);
    }
}
