<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormQuestion extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false; // This indicates that the model does not auto-increment its primary key.
    protected $primaryKey  = ['form_id','question_id'];
    protected $fillable = [
        'question_id',
        'form_id',
        'combination_id',
    ];


    public function question() : BelongsTo {
        return $this->BelongsTo(Question::class);
    }

    public function form() : BelongsTo {
        return $this->BelongsTo(Form::class);
    }
    public function student_answers() : HasMany {
        return $this->HasMany(StudentAnswer::class);
    }


}
