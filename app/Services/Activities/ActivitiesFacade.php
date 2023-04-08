<?php

namespace App\Services\Activities;

use Illuminate\Support\Facades\Facade;

class ActivitiesFacade extends Facade
{

  protected static function getFacadeAccessor()
  {
    return Activities::class;
  }
}
