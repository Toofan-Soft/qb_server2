<?php

namespace App\Helpers;


class EnumReplacement
{
  public string $dbColumnName;
  public string $newColumnName;
  public string $enumClass;

  public function __construct(string $dbColumnName, string $newColumnName, string $enumClass)
  {
    $this->dbColumnName = $dbColumnName;
    $this->newColumnName = $newColumnName;
    $this->enumClass = $enumClass;
  }
}
