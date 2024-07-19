<?php

namespace App\Models;

use App\Models\Chapter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Topic extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'arabic_title',
        'english_title',
        'description',
        'chapter_id',
    ];

    public function chapter() : BelongsTo {
        return $this->BelongsTo(Chapter::class);
    }

    public function questions() : HasMany {
        return $this->HasMany(Question::class);
    }
}
