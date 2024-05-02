<?php

namespace App\Helpers;


class EnumReplacement
{
    public string $columnName;
    public string $enumClass;

    
  /**
   * $columName: name of enum field in current data
   * $enumClass: class of Enums
   */
    public function __construct(string $columnName,  $enumClass)
    {
      $this->columnName = $columnName;
      $this->enumClass = $enumClass;
    }


}
