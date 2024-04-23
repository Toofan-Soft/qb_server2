<?php

namespace App\Models;

use App\Models\RealExam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaperExam extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false; // This indicates that the model does not auto-increment its primary key.
    protected $fillable = [
        'Course_lecturer_name',
    ];

    public function real_exam() : HasOne {
        return $this->HasOne(RealExam::class);
    }

}
