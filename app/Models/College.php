<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class College extends Model
{
    use HasFactory;
    public $timestamps = false;  // مؤقت

    protected $fillable = [
        'arabic_name',
        'english_name',
        'logo_url',
        'description',
        'phone',
        'email',
        'facebook',
        'x_platform',
        'youtube',
        'telegram',
    ];

    public function departments() : HasMany {
        return $this->hasMany(Department::class);
    }
}
