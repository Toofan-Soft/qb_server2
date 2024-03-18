<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FavoriteQuestion extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false; // This indicates that the model does not auto-increment its primary key.
    protected $primaryKey  = ['user_id','question_id'];
    protected $fillable = [
        'user_id',
        'question_id',
        'combination_id',
    ];


    public function question() : BelongsTo {
        return $this->BelongsTo(Question::class);
    }

    public function user() : BelongsTo {
        return $this->BelongsTo(User::class);
    }

}
