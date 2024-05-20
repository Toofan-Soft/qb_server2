<?php

namespace App\Models;

use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guest extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'name',
        'phone',
        'image_url',
        'gender',
        'user_id',
    ];

    //عشان اقله نوع البيانات في هذا الاتريبيوت ستكون من نوع هذا الإنم
    protected $casts = [
        // 'gender' =>GenderEnum::class ,
    ];

    public function user() : BelongsTo {
        return $this->BelongsTo(User::class);
    }
}
