<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdultResource extends JsonResource
{
    /**
     * Treating the users as an Adult family member, this is consumed by FamilyResource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'relation' => $this->member->relation,
            'manager' => $this->member->manager == 1 ? true : false,
            'avatar' => $this->avatarUrl()
        ];
    }
}
