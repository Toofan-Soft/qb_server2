<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuestionChoicesCombination extends Model
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
        // 'combination_choices'  => 'array',
    ];

    public function question() : BelongsTo {
        return $this->BelongsTo(Question::class);
    }
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // $model->combination_id = self::where('question_id', $model->question_id)->max('combination_id') + 1;
             // Get the maximum combination_id for the given question_id
             $maxCombinationId = self::where('question_id', $model->question_id)->max('combination_id');
             // If there is no record, maxCombinationId will be null, so we set it to 1
             $model->combination_id = $maxCombinationId ? $maxCombinationId + 1 : 1;
        });
    }

}
