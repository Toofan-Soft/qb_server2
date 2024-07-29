<?php

namespace App\Helpers;

use Traversable;
use App\Traits\EnumTraits;
use Illuminate\Support\Collection;

class ProcessDataHelper
{

    public static function enumsConvertIdToName($data, $enumReplacements)
    {
        try {
            // Check if $data is an array or a single object
            $isArray = is_array($data) || $data instanceof Traversable;

            if (empty($enumReplacements)) {
                return $data;
            }

            $dataToProcess = $isArray ? $data : [$data];

            $newData = [];

            foreach ($dataToProcess as $item) {
                foreach ($enumReplacements as $enumReplacement) {
                    if (is_array($item)) {
                        $item[$enumReplacement->columnName] = EnumTraits::getNameByNumber(intval($item[$enumReplacement->columnName]), $enumReplacement->enumClass, LanguageHelper::getEnumLanguageName());
                    } else {
                        $item->{$enumReplacement->columnName} = EnumTraits::getNameByNumber(intval($item->{$enumReplacement->columnName}), $enumReplacement->enumClass, LanguageHelper::getEnumLanguageName());
                    }
                }
                $newData[] = $item; // Add the processed item to the new array
            }
            unset($item); // Unset the reference to avoid potential bugs

            // If $data was a single object, return the modified object
            return $isArray ? $newData : $newData[0];
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public static function columnConvertIdToName($data, $columnReplacements)
    {
        try {
            // Check if $data is a collection, array, or a single object
            $isCollection = $data instanceof Collection;
            $isArray = is_array($data) || $data instanceof Traversable;
            $isSingleObject = is_object($data) && !$isCollection;

            // Convert $data to an array if it's a single object or collection
            $dataToProcess = $isSingleObject ? [$data] : ($isCollection ? $data->toArray() : $data);
            $processedData = [];

            foreach ($dataToProcess as $item) {
                if (is_object($item) || is_array($item)) {
                    foreach ($columnReplacements as $columnReplacement) {
                        $identifier = is_array($item) ? $item[$columnReplacement->columnName] : $item->{$columnReplacement->columnName};
                        if ($identifier != null) {
                            $model = $columnReplacement->model;
                            $row = $model::findOrFail($identifier, [$columnReplacement->modelColumnName]);

                            if ($row && isset($row->{$columnReplacement->modelColumnName})) {
                                if (is_array($item)) {
                                    $item[$columnReplacement->columnName] = $row->{$columnReplacement->modelColumnName};
                                } else {
                                    $item->{$columnReplacement->columnName} = $row->{$columnReplacement->modelColumnName};
                                }
                            }
                        }
                    }
                    $processedData[] = $item;
                }
            }

            // Convert the processed data back to its original format
            return $isSingleObject ? $processedData[0] : ($isCollection ? new Collection($processedData) : $processedData);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
