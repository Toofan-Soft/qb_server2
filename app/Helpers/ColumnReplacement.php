<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;



class ColumnReplacement
{
  public string $columnName;
  public string $modelColumnName;
  public string $model;

  public function __construct(string $columnName, string $modelColumnName, $model)
  {
    $this->columnName = $columnName;
    $this->modelColumnName = $modelColumnName;
    $this->model = $model;
  }

}
