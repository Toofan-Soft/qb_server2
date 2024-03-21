<?php

namespace App\Http\Controllers;

use App\Helpers\FilterHelper;
use App\Models\Course;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function retrieveCourses(Request $request)
    {
        $attributes = ['id', 'arabic_name as name'];
        $conditionAttribute = [];
        return FilterHelper::getfilterData(Course::class, $attributes, $conditionAttribute);
    }
}
