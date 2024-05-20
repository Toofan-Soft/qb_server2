<?php

namespace App\Models;

use App\Enums\ChoiceStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Choice extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'question_id',
        'content',
        'attachment',
        'status',
    ];

    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
        // 'status' =>ChoiceStatusEnum::class  ,

    ];
    public function question() : BelongsTo {
        return $this->BelongsTo(Question::class);
    }
}
