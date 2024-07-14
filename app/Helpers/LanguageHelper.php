<?php

namespace App\Helpers;

use App\Enums\LanguageEnum;

class LanguageHelper
{

    /**
     * 
     * 
     */
    public static function getNameColumnName(string $tableName = null, string $aliasName = null): string
    {
        // استدعاء دالة تقوم بتخلص من الفراغات في اسم الجدول والاسم المستعار المرسل

        try {
            $statement = '';
            if (intval(auth()->user()->language_id) === LanguageEnum::ARABIC->value) {
                $statement = 'arabic_name';
            } else {
                $statement = 'english_name';
            }
            if(!is_null($tableName)){
                $statement = $tableName . '.' . $statement;
            }
            if(!is_null($aliasName)){
                $statement = $statement . ' as ' . $aliasName;
            }
            return $statement;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getTitleColumnName(string $tableName = null, string $aliasName = null): string
    {
         // استدعاء دالة تقوم بتخلص من الفراغات في اسم الجدول والاسم المستعار المرسل
         try {
            $statement = '';
            if (intval(auth()->user()->language_id) === LanguageEnum::ARABIC->value) {
                $statement = 'arabic_title';
            } else {
                $statement = 'english_title';
            }
            if(!is_null($tableName)){
                $statement = $tableName . '.' . $statement;
            }
            if(!is_null($aliasName)){
                $statement = $statement . ' as ' . $aliasName;
            }
            return $statement;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getEnumLanguageName(): string
    {
        try {
            if (intval(auth()->user()->language_id) === LanguageEnum::ARABIC->value) {
                return 'ar';
            } else {
                return 'en';
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
