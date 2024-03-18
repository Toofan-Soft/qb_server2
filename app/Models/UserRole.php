<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserRole extends Model
{
    use HasFactory;
    public $incrementing = false; // This indicates that the model does not auto-increment its primary key.

    protected $primaryKey = ['user_id', 'role_id'];
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'role_id',
    ];

    protected $casts = [
        'role_id' => RoleEnum::class,
    ];

    public function user() : BelongsTo {
        return $this->BelongsTo(User::class);
    }
}
