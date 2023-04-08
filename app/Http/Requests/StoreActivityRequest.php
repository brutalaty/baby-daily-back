<?php

namespace App\Http\Requests;

use \App\Services\Activities\ActivitiesFacade;

use Illuminate\Validation\Rule;

class StoreActivityRequest extends ActivityRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => [
                'string',
                'required',
                Rule::in(ActivitiesFacade::activities()),
            ],
            'time' => [
                'date',
                'required',
            ]
        ];
    }
}
