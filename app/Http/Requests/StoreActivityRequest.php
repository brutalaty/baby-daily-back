<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;

class StoreActivityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {

        [$keys, $values] = Arr::divide(config('enums.activities'));

        return [
            'type' => [
                'string',
                'required',
                Rule::in($values),
            ],
            'time' => [
                'date',
                'required',
            ]
        ];
    }
}
