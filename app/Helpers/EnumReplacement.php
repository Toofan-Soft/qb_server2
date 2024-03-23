<?php

namespace App\Helpers;


class EnumReplacement
{
    public string $columnName;
    public string $enumClass;

    public function __construct(string $columnName,  $enumClass)
    {
      $this->columnName = $columnName;
      $this->enumClass = $enumClass;
    }


}
