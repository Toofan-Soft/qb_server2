<?php

namespace App\AlgorithmAPI;

use App\Enums\AccessibilityStatusEnum;
use App\Enums\QuestionStatusEnum;
use Illuminate\Support\Facades\DB;

class GenerateOnlineExam
{
    /**
     * اذا كان لا يوجد اختلاف في اسئلة النماذج يتم ارجاع  البيانات كتالي....
     * 
     */
    public function execute($data)
    {
        // call algorithm model and recive and return output 
        return $data;

    }
}

