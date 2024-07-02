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
}
