<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionChoiceCombination extends Model
{
    use HasFactory;
    public $timestamps = false;

    public $incrementing = false; // This indicates that the model does not auto-increment its primary key.

    protected $primaryKey = ['combination_id','question_id'];

    protected $fillable = [
        'combination_id',
        'question_id',
        'combination_choices'
    ];

    protected $casts = [
        //'combination_choices'  => 'array',
    ];

    public function question() : BelongsTo {
        return $this->BelongsTo(Question::class);
    }

}
