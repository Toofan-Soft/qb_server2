<?php

namespace App\Helpers;


class ColumnReplacement
{
  public string $columnName;
  public string $modelColumnName;
  public   $model;

  /**
   * $columName: name of primary field in current data
   * $modelColumnName: alternate field name in model
   * $model: class of model from database
   */

  public function __construct(string $columnName, string $modelColumnName, $model)
  {
    $this->columnName = $columnName;
    $this->modelColumnName = $modelColumnName;
    $this->model = $model;
  }
}
