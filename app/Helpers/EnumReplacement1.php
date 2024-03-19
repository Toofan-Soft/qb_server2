<?php

namespace App\Helpers;

use Illuminate\Validation\Rules\Enum;

class EnumReplacement1
{
  public string $columnName;
  public string $enumClass;

  public function __construct(string $columnName,  $enumClass)
  {
    $this->columnName = $columnName;
    $this->enumClass = $enumClass;
  }

}

