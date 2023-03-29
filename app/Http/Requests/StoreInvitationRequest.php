<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Models\Family;
use App\Models\User;

use Closure;

class StoreInvitationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $family = $this->route('family');
        $user = auth()->user();

        return [
            'name' => ['string', 'max:255', 'required'],
            'relation' => ['string', 'max:255', 'required'],
            'email' => [
                'string',
                'email',
                'max:255',
                //a user with the given email should not already be in the family
                function (string $attribute, mixed $value, Closure $fail) use ($family) {
                    $user = User::firstWhere('email', $value);
                    if ($user == null) return;
                    if ($user->families->contains($family))
                        $fail("An adult with the {$attribute}: {$value} is already a member of the {$family->name} family");
                },
                'required'
            ]
        ];
    }
}
