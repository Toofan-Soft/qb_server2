<?php

namespace App\Helpers;

use App\Enums\LanguageEnum;

class LanguageHelper
{

    //getTitleColumnName
    //getEnumLanguageName
    /**
     * $model object of model
     * delete one raw
     */
    public static function getNameColumnName($model): string
    {
        try {
            if (intval(auth()->user()->language_id) === LanguageEnum::ARABIC->value) {
                return 'arabic_name as name';
            } else {
                return 'english_name as name';
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public static function getTitleColumnName($model): string
    {
        try {
            if (intval(auth()->user()->language_id) === LanguageEnum::ARABIC->value) {
                return 'arabic_title as title';
            } else {
                return 'english_title as title';
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public static function getEnumLanguageName($model): string
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
