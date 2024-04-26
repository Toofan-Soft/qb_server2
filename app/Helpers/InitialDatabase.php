<?php

namespace App\Helpers;

use App\Models\College;
use App\Models\Course;

class InitialDatabase
{

    public static function college()
    {
        $colleges = [
            [
                'arabic_name' => 'الهندسة وتقنية المعلومات',
                'english_name' => 'Engineering and Infromation Technology',
                'phone' => 777111222,
                'email' => 'engineering@gmail.com'
            ]
        ];
        foreach ($colleges as $college) {
            College::create([
                'arabic_name' => $college['arabic_name'],
                'english_name' => $college['english_name'],
                'phone' => $college['phone'] ?? null,
                'email' => $college['email'] ?? null,
                'description' => $college['description'] ?? null,
                'facebook' => $college['facebook'] ?? null,
                'youtube' => $college['youtube'] ?? null,
                'x_platform' => $college['x_platform'] ?? null,
                'telegram' => $college['telegram'] ?? null,
                'logo_url' => ImageHelper::uploadImage($college['logo'])
            ]);
        }
    }

    public static function department()
    {
        $departments = [
            [
                'college_id' => 1,
                'arabic_name' => 'وتقنية المعلومات',
                'english_name' => 'Infromation Technology',
                'levels_count' => 5,
            ],
            [
                'college_id' => 1,
                'arabic_name' => 'هندسة البرمجيات',
                'english_name' => 'Software Engineering',
                'levels_count' => 5,
            ]
        ];
        foreach ($departments as $department) {
            $college = College::findOrFail($department['college_id']);
            $college->departments()->create([
                'arabic_name' => $department['arabic_name'],
                'english_name' => $department['english_name'],
                'levels_count' => $department['levels_count'],
                'description' => $department['description'] ?? null,
                'logo_url' => ImageHelper::uploadImage($department['logo'])
            ]);
        }
    }

    public static function course()
    {
        $courses = [
            [
                'arabic_name' => 'ذكاء صناعي',
                'english_name' => 'Artificial Intelligent',
            ],
            [
                'arabic_name' => 'شبكات عصبية',
                'english_name' => 'Neural Network',
            ],
        ];
        foreach ($courses as $course) {
            Course::create([
                'arabic_name' => $course['arabic_name'],
                'english_name' => $course['english_name'],
            ]);
        }
    }
    
}
