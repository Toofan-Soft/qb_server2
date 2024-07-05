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
            if ($parent->{$property->from} === "null") {
              $params[$property->to] = null;
            } else {
              $params[$property->to] = $parent->{$property->from};
            }
        }
    }

    return $params;
  }
}
