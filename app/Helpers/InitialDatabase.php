<?php

namespace App\Helpers;

use App\Models\College;


class InitialDatabase
{

    public static function college($model)
    {
        $colleges = [
            [
                'arabic_name'=>'الهندسة وتقنية المعلومات',
                'english_name' => 'Engineering and Infromation Technology',
            ]
        ];
        foreach ($colleges as $college) {
            College::create([
                'arabic_name' => $college['arabic_name'],
                'english_name' => $college['english_name'],
                'phone' => $college['phone'] ?? null,
                'email' => $college['email'] ?? null,
                'description' => $college['description']?? null,
                'facebook' => $college['facebook'] ?? null,
                'youtube' => $college['youtube']?? null,
                'x_platform' => $college['x_platform'] ?? null,
                'telegram' => $college['telegram'] ?? null,
                'logo_url' => ImageHelper::uploadImage($college['logo'])
            ]);
        }
    }

}
