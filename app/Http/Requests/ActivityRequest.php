<?php

namespace App\Http\Requests;

use \App\Services\Activities\ActivitiesFacade;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

abstract class ActivityRequest extends FormRequest
{


  /**
   * Get the error messages for the defined validation rules.
   *
   * @return array<string, string>
   */
  public function messages(): array
  {
    return [
      'type.in' => 'The selected type is invalid. Type must be one of ' . ActivitiesFacade::activitiesAsString(),
    ];
  }
}
