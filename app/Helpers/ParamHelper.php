<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;

class ParamHelper
{
  public static function getParams($parent, $properties)
  {
    $params = [];

    foreach ($properties as $property) {
        if ($parent->has($property->from)) {
            $params[$property->to] = $parent->{$property->from};
        }
    }

    return $params;
  }
}
