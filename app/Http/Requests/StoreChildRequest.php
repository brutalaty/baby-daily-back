<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use \DateTime;

class StoreChildRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'string|required',
            'born' => 'date_format:Y-m-d|required'
        ];
    }
}
