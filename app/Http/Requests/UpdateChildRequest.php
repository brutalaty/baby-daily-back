<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use \DateTime;

class UpdateChildRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'string',
            'born' => 'date_format:Y-m-d'
        ];
    }
}
