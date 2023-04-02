<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
      'email' => $this->email,
      'email_verified_at' => $this->email_verified_at,
      'updated_at' => $this->updated_at,
      'created_at' => $this->created_at,
      'avatar' => $this->avatarUrl()
    ];
  }
}
