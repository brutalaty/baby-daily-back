<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use \DateTime;

class UpdateChildAvatarRequest extends FormRequest
{

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, mixed>
   */
  public function rules()
  {
    return [
      'avatar' => 'image|max:500|required',
    ];
  }
}
