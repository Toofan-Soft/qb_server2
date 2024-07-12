<?php

namespace App\Models;

use App\Helpers\DatetimeHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PracticeExamUsage extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;
    protected $dateFormat = 'U';
    protected $fillable = [
        'practice_exam_id',
        'start_datetime',
        'last_suspended_datetime',
        'remaining_duration',
    ];

    public function practice_exam() : BelongsTo {
        return $this->BelongsTo(PracticeExam::class);
    }

}
