<?php

namespace App\Models;

use App\Enums\LanguageEnum;
use App\Enums\AccessStateEnum;
use App\Enums\QuestionTypeEnum;
use App\Enums\QuestionStateEnum;
use App\Enums\QuestionStatusEnum;
use App\Enums\AcceptancestatusEnum;
use App\Enums\AccessibilityStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'content',
        'attachment',
        'title',
        'type',
        'difficulty_level',
        'status',
        'accessability_status',
        'estimated_answer_time',
        'language',
        'topic_id',



    ];

    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
        // 'type' => QuestionTypeEnum::class,
        // 'status' => QuestionStatusEnum::class,
        // 'accessability_status' => AccessibilityStatusEnum::class,
        // 'language' => LanguageEnum::class,
    ];

    public function topic() : BelongsTo {
        return $this->BelongsTo(Topic::class);
    }
    public function question_choices_combinations() : HasMany {
        return $this->HasMany(QuestionChoiceCombination::class);
    }

    public function true_false_question() : HasOne {
        return $this->HasOne(TrueFalseQuestion::class);
    }
// update the question_usages to question_usage
    public function question_usage() : HasOne {
        return $this->HasOne(QuestionUsage::class);
    }

    public function choices() : HasMany {
        return $this->HasMany(Choice::class);
    }

    public function form_questions() : HasMany {
        return $this->HasMany(FormQuestion::class);
    }

    public function practice_exam_questions() : HasMany {
        return $this->HasMany(PracticeExamQuestion::class);
    }
    public function favorite_questions() : HasMany {
        return $this->HasMany(FavoriteQuestion::class);
    }

}
