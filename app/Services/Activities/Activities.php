<?php

namespace App\Services\Activities;

class Activities
{

  private static $instance;

  public static function getInstance()
  {
    if (self::$instance == null) {
      self::$instance = new Activities();
    }
    return self::$instance;
  }

  private function __construct()
  {
  }

  public function activities()
  {
    return array_values(config('enums.activities'));
  }

  public function getRandomActivityWithoutConsumptions()
  {
    return array_rand(
      array_diff(
        array_values(config('enums.activities')),
        array_values(config('enums.complex_activities')),
      )
    );
  }

  public function activitiesAsString()
  {
    return implode(', ', $this->activities()) . '.';
  }
}
