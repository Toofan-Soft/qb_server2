<?php

namespace App\Helpers;

use App\Enums\GenderEnum;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProcessDataHelper
{

    public static function enumsConvertIdToName( $data, $enumReplacements )
    {

        foreach ($data as $item) {
            foreach ($enumReplacements as $enumReplacement) {
                $item[$enumReplacement->columnName] = $enumReplacement->enumClass::getNameByNumber($item[$enumReplacement->columnName]);
            }
        }
        return $data;
    }


    public static function columnConvertIdToName( $data, $columnReplacements )
    {

        foreach ($data as $item) {
            foreach ($columnReplacements as $columnReplacement) {
                $item[$columnReplacement->columnName] = $columnReplacement->model::find($item[$columnReplacement->columnName])->get([$columnReplacement->modelColumnName]);
        }
        return $data;
    }

}
}
