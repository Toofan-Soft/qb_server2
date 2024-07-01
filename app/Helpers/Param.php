<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;

class Param
{
  public string $from;
  public string $to;

  public function __construct(string $from, string $to = null)
  {
    $this->from = $from;
    $this->to = $to ?? $from;
  }

  // private function getParams($parent, $properties)
  // {
  //   $params = [];

  //   foreach ($properties as $property) {
  //       if ($parent->has($property)) {
  //           // $params[] = ['key' => $property, 'value' => $parent->{$property}];
  //           // $params[] = [$property => $parent->{$property}];
  //           $params[$property] = $parent->{$property};
  //       }
  //   }

  //   return $params;
  // }
}
