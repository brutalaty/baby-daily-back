<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'family_name' => $this->family->name,
            'relation' => $this->relation,
            'status' => $this->status,
            'expiration' => $this->expiration,
            'hasExpired' => $this->hasExpired()
        ];
    }
}
